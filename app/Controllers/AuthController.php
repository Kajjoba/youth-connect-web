<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Auth, Audit, Session};
use App\Services\Lists;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->view('auth/login', ['title' => 'Log in']);
    }

    public function login(): void
    {
        $email = trim((string)($_POST['email'] ?? ''));
        $pass = (string)($_POST['password'] ?? '');
        if ($email === '' || $pass === '') {
            Session::withErrors(['email' => $email === '' ? 'Please enter your email.' : '', 'password' => $pass === '' ? 'Please enter your password.' : ''], ['email' => $email]);
            $this->redirect('login');
            return;
        }
        $res = Auth::attempt($email, $pass);
        if (is_int($res)) {
            $intended = $_SESSION['intended'] ?? null;
            Auth::login($res);
            Audit::log('login.ok', 'user', $res, null, $res);
            $role = Auth::user()['role'];
            $dest = ($intended && $intended[0] === '/' && strpos($intended, '//') !== 0) ? ltrim($intended, '/') : Lists::homeFor($role);
            flash('success', 'Welcome back, ' . explode(' ', Auth::user()['name'])[0] . '!');
            $this->redirect($dest);
            return;
        }
        $messages = [
            'throttled' => 'Too many attempts. For your security, please wait a few minutes before trying again.',
            'suspended' => 'This account is paused. Please contact a programme administrator.',
            'invalid'   => 'That email and password do not match. Check them and try again.',
        ];
        flash('error', $messages[$res]);
        Session::withErrors([], ['email' => $email]);
        $this->redirect('login');
    }

    public function registerForm(): void
    {
        $this->view('auth/register', ['title' => 'Join YouthConnect', 'countries' => Lists::COUNTRIES]);
    }

    public function register(): void
    {
        $data = $this->validate([
            'name'     => 'required|min:2|max:120',
            'email'    => 'required|email|max:190',
            'password' => 'required|min:' . config('security.password_min') . '|max:72|strongpass|confirmed:password_confirmation',
            'country'  => 'required|in:' . Lists::csv(Lists::COUNTRIES),
            'age_group' => 'required|in:' . Lists::csv(Lists::AGE_GROUPS),
            'terms'    => 'required',
        ], 'register', ['age_group' => 'your age group', 'terms' => 'agreement to the terms']);
        if ($data === null) return;

        $email = strtolower($data['email']);
        if (DB::val('SELECT id FROM users WHERE email = ?', [$email])) {
            Session::withErrors(['email' => 'An account with this email already exists. Try logging in instead.'], ['name' => $data['name'], 'email' => $email, 'country' => $data['country'], 'age_group' => $data['age_group']]);
            $this->redirect('register');
            return;
        }
        // Role is NEVER taken from the form: self-registration always creates a participant.
        $uid = DB::tx(function () use ($data, $email) {
            $id = DB::insert('INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,\'participant\')',
                [$data['name'], $email, password_hash($data['password'], PASSWORD_DEFAULT)]);
            DB::run('INSERT INTO profiles (user_id, country, age_group) VALUES (?,?,?)', [$id, $data['country'], $data['age_group']]);
            return $id;
        });
        Auth::login($uid);
        Audit::log('user.registered', 'user', $uid, null, $uid);
        flash('success', 'Welcome to YouthConnect! Tell us a little about yourself so we can suggest the right activities.');
        $this->redirect('profile?welcome=1');
    }

    public function logout(): void
    {
        Audit::log('logout');
        Auth::logout();
        Session::start();   // no-op if still active; fresh session for flash
        $this->redirect('');
    }
}
