<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Auth, Audit, Throttle, Request};
use App\Services\Lists;

/**
 * "Continue with Google" (OAuth 2.0 authorization-code flow with PKCE, no libraries).
 * - state (CSRF for the redirect) and PKCE verifier live in the server-side session.
 * - Token and userinfo calls go server-to-server over TLS to Google, so no ID-token parsing is needed.
 * - Only accounts whose email Google has verified are accepted.
 * - Role is never taken from Google: new accounts are always participants.
 */
class GoogleAuthController extends Controller
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const INFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

    public static function enabled(): bool
    {
        return (bool)(config('google.client_id') && config('google.client_secret'));
    }

    private function redirectUri(): string
    {
        $fixed = config('google.redirect_uri');
        if ($fixed) return $fixed;
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        return ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('auth/google/callback');
    }

    private function b64url(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }

    public function start(): void
    {
        if (!self::enabled()) {
            flash('error', 'Google sign-in is not available right now. Please use your email and password.');
            $this->redirect('login');
            return;
        }
        $state = bin2hex(random_bytes(16));
        $verifier = $this->b64url(random_bytes(48));
        $_SESSION['g_state'] = $state;
        $_SESSION['g_verifier'] = $verifier;
        $q = http_build_query([
            'client_id' => config('google.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'code_challenge' => $this->b64url(hash('sha256', $verifier, true)),
            'code_challenge_method' => 'S256',
            'prompt' => 'select_account',
        ]);
        $this->redirect(self::AUTH_URL . '?' . $q);
    }

    public function callback(): void
    {
        $state = (string)($_SESSION['g_state'] ?? '');
        $verifier = (string)($_SESSION['g_verifier'] ?? '');
        unset($_SESSION['g_state'], $_SESSION['g_verifier']);

        if (isset($_GET['error'])) {
            flash('info', 'Google sign-in was cancelled. You can try again or use your email and password.');
            $this->redirect('login');
            return;
        }
        $code = (string)($_GET['code'] ?? '');
        if ($code === '' || $state === '' || !hash_equals($state, (string)($_GET['state'] ?? ''))) {
            flash('error', 'That Google sign-in link expired. Please try again.');
            $this->redirect('login');
            return;
        }
        $ipKey = 'google:i:' . Request::ip();
        if (Throttle::tooMany($ipKey, 30, (int)config('security.login_window_minutes'))) {
            flash('error', 'Too many attempts. Please wait a few minutes and try again.');
            $this->redirect('login');
            return;
        }
        Throttle::hit($ipKey);

        $tok = $this->curl(self::TOKEN_URL, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query([
            'code' => $code,
            'client_id' => config('google.client_id'),
            'client_secret' => config('google.client_secret'),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
            'code_verifier' => $verifier,
        ])]);
        $info = isset($tok['access_token'])
            ? $this->curl(self::INFO_URL, [CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $tok['access_token']]])
            : null;
        if (!$info || empty($info['sub']) || empty($info['email']) || empty($info['email_verified'])) {
            flash('error', 'We could not confirm your Google account. Please try again or use your email and password.');
            $this->redirect('login');
            return;
        }

        $gid = (string)$info['sub'];
        $email = strtolower((string)$info['email']);
        $name = trim((string)($info['name'] ?? '')) ?: explode('@', $email)[0];
        $name = mb_substr($name, 0, 120);

        $user = DB::one('SELECT id, status FROM users WHERE google_id = ?', [$gid]);
        $isNew = false;
        if (!$user) {
            $user = DB::one('SELECT id, status FROM users WHERE email = ?', [$email]);   // same verified email: link
            if ($user) {
                DB::run('UPDATE users SET google_id = ? WHERE id = ?', [$gid, $user['id']]);
                Audit::log('user.google_linked', 'user', (int)$user['id'], null, (int)$user['id']);
            }
        }
        if (!$user) {
            $isNew = true;
            $uid = DB::tx(function () use ($name, $email, $gid) {
                // Unusable random password: this person signs in with Google.
                $id = DB::insert('INSERT INTO users (name, email, password_hash, role, google_id) VALUES (?,?,?,\'participant\',?)',
                    [$name, $email, password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT), $gid]);
                DB::run('INSERT INTO profiles (user_id) VALUES (?)', [$id]);
                return $id;
            });
            $user = ['id' => $uid, 'status' => 'active'];
            Audit::log('user.registered', 'user', $uid, 'google', $uid);
        }
        if ($user['status'] !== 'active') {
            flash('error', 'This account is paused. Please contact a programme administrator.');
            $this->redirect('login');
            return;
        }

        $intended = $_SESSION['intended'] ?? null;
        $uid = (int)$user['id'];
        Auth::login($uid);
        Audit::log('login.ok', 'user', $uid, 'google', $uid);
        if ($isNew) {
            flash('success', 'Welcome to YouthConnect, ' . explode(' ', $name)[0] . '! Add your country and age group so we can suggest the right activities.');
            $this->redirect('profile?welcome=1');
            return;
        }
        $dest = ($intended && $intended[0] === '/' && strpos($intended, '//') !== 0) ? ltrim($intended, '/') : Lists::homeFor(Auth::user()['role']);
        flash('success', 'Welcome back, ' . explode(' ', Auth::user()['name'])[0] . '!');
        $this->redirect($dest);
    }

    private function curl(string $url, array $opts): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, $opts + [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2]);
        $body = curl_exec($ch);
        if ($body === false) error_log('Google OAuth curl error: ' . curl_error($ch));
        $data = is_string($body) ? json_decode($body, true) : null;
        return is_array($data) ? $data : null;
    }
}
