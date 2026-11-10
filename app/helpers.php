<?php
// Global helper functions used by controllers and views.
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;

function config(string $key, $default = null)
{
    static $cfg = null;
    if ($cfg === null) $cfg = require BASE_PATH . '/config/config.php';
    $v = $cfg;
    foreach (explode('.', $key) as $part) {
        if (!is_array($v) || !array_key_exists($part, $v)) return $default;
        $v = $v[$part];
    }
    return $v;
}

/** Escape for HTML output. Every dynamic value in a view goes through this. */
function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_path(): string
{
    static $b = null;
    if ($b === null) {
        $cfg = config('app.base_path');
        if ($cfg !== null && $cfg !== '') {
            $b = rtrim((string)$cfg, '/');
        } else {
            $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
            $dir = preg_replace('#/public$#', '', $dir);
            $b = rtrim($dir, '/');
        }
    }
    return $b;
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function asset(string $file): string
{
    $f = BASE_PATH . '/public/assets/' . $file;
    $v = is_file($f) ? filemtime($f) : 1;
    return url('assets/' . $file) . '?v=' . $v;
}

function csrf_token(): string { return Csrf::token(); }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">'; }

function auth_user(): ?array { return Auth::user(); }

function flash(string $type, string $msg): void { Session::flash($type, $msg); }

function old(string $key, $default = '')
{
    $o = $_SESSION['_old'][$key] ?? null;
    return $o !== null ? $o : $default;
}

function field_error(string $key): string
{
    $err = $_SESSION['_errors'][$key] ?? null;
    return $err ? '<p class="field-error" role="alert">' . e($err) . '</p>' : '';
}

function time_ago(?string $dt): string
{
    if (!$dt) return '';
    $diff = time() - strtotime($dt);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' h ago';
    if ($diff < 86400 * 30) return floor($diff / 86400) . ' d ago';
    return date('j M Y', strtotime($dt));
}

function slugify(string $s): string
{
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s), '-'));
    return $s !== '' ? $s : 'item';
}

/** 16-hex-char opaque identifier used in URLs for teams, projects and evidence. */
function public_id(): string { return bin2hex(random_bytes(8)); }

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $i = strtoupper(substr($parts[0] ?? '?', 0, 1) . (count($parts) > 1 ? substr(end($parts), 0, 1) : ''));
    return $i;
}

function stars(float $r): string
{
    return number_format($r, 1);
}

function lite_mode(): bool
{
    $u = Auth::user();
    if ($u && !empty($u['lite_mode'])) return true;
    if (!empty($_COOKIE['ycp_lite'])) return $_COOKIE['ycp_lite'] === '1';
    return ($_SERVER['HTTP_SAVE_DATA'] ?? '') === 'on';
}

/**
 * Turns plain lesson text into safe HTML: blank line = new paragraph, "## " = heading, "- " lines = bullet list.
 * Everything is escaped, so authors cannot inject markup or scripts.
 */
function render_prose(string $text): string
{
    $out = ''; $para = []; $list = [];
    $flushP = function () use (&$out, &$para) { if ($para) { $out .= '<p>' . nl2br(e(implode("\n", $para))) . '</p>'; $para = []; } };
    $flushL = function () use (&$out, &$list) { if ($list) { $out .= '<ul>' . implode('', array_map(fn($li) => '<li>' . e($li) . '</li>', $list)) . '</ul>'; $list = []; } };
    foreach (preg_split('/\R/', $text) as $line) {
        $t = rtrim($line);
        if (trim($t) === '') { $flushP(); $flushL(); continue; }
        if (preg_match('/^## (.+)$/', $t, $m)) { $flushP(); $flushL(); $out .= '<h2>' . e($m[1]) . '</h2>'; continue; }
        if (preg_match('/^- (.+)$/', $t, $m)) { $flushP(); $list[] = $m[1]; continue; }
        $flushL(); $para[] = $t;
    }
    $flushP(); $flushL();
    return $out;
}

function status_label(string $s): string
{
    return ucfirst(str_replace('_', ' ', $s));
}
