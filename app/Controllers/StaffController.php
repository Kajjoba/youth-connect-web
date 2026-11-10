<?php
namespace App\Controllers;

use App\Core\{Controller, DB};

/** Mentor / facilitator / stakeholder home: only the projects they are assigned to (admins see all). */
class StaffController extends Controller
{
    public function projects(): void
    {
        $u = $this->user();
        $sql = 'SELECT p.public_id, p.title, p.status, p.updated_at, t.name AS team, ps.role AS my_role,
                (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.id AND tm.status = \'active\') AS members
                FROM project_staff ps JOIN projects p ON p.id = ps.project_id JOIN teams t ON t.id = p.team_id
                WHERE ps.user_id = ? ORDER BY FIELD(p.status, \'proposed\', \'submitted\', \'assessed\', \'validated\', \'approved\', \'changes_requested\', \'draft\', \'completed\'), p.updated_at DESC';
        $rows = DB::all($sql, [$u['id']]);
        $this->view('staff/projects', ['rows' => $rows, 'title' => 'My assigned projects']);
    }
}
