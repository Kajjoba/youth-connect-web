<?php
namespace App\Services;

use App\Core\DB;
use App\Core\Notify;

/** Final step of the Level Two chain: certificates + badges for every active team member. */
class Recognition
{
    public static function award(array $project): void
    {
        DB::tx(function () use ($project) {
            $pid = (int)$project['id'];
            $members = DB::all('SELECT u.id, p.country FROM team_members tm JOIN users u ON u.id = tm.user_id
                                LEFT JOIN profiles p ON p.user_id = u.id WHERE tm.team_id = ? AND tm.status = \'active\'', [$project['team_id']]);
            $countries = array_unique(array_filter(array_column($members, 'country')));
            $asm = DB::one('SELECT total FROM assessments WHERE project_id = ?', [$pid]);
            $badges = ['collaborator'];
            if (count($countries) >= 3) $badges[] = 'cross_border';
            if ($asm && (int)$asm['total'] >= 80) $badges[] = 'impact_maker';
            if (DB::val('SELECT COUNT(*) FROM validations WHERE project_id = ? AND decision = \'endorsed\'', [$pid])) $badges[] = 'validated_impact';

            foreach ($members as $m) {
                $code = 'YCP-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
                DB::run('INSERT IGNORE INTO certificates (code, user_id, kind, course_id, project_id, title) VALUES (?,?,?,0,?,?)',
                    [$code, $m['id'], 'project', $pid, 'Certificate of Collaboration: ' . $project['title']]);
                foreach ($badges as $b) {
                    DB::run('INSERT IGNORE INTO user_badges (user_id, badge_id, project_id)
                             SELECT ?, id, ? FROM badges WHERE code = ?', [$m['id'], $pid, $b]);
                }
                Notify::to((int)$m['id'], 'Your project "' . $project['title'] . '" is complete. Your certificate and badges are ready.', 'my-learning');
            }
        });
    }
}
