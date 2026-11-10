<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $p, array $h, array $mw = []): void { $this->add('GET', $p, $h, $mw); }
    public function post(string $p, array $h, array $mw = []): void { $this->add('POST', $p, $h, $mw); }

    private function add(string $method, string $pattern, array $handler, array $mw): void
    {
        // {id} -> numeric, {slug}/{pid}/{code} -> url-safe token
        $regex = preg_replace_callback('#\{(\w+)\}#', function ($m) {
            return $m[1] === 'id' || substr($m[1], -2) === 'Id' ? '(?P<' . $m[1] . '>\d+)' : '(?P<' . $m[1] . '>[A-Za-z0-9_-]+)';
        }, $pattern);
        $this->routes[] = [$method, '#^' . $regex . '$#', $handler, $mw];
    }

    public function dispatch(): void
    {
        $method = Request::method();
        $path = Request::path();
        $pathMatched = false;

        foreach ($this->routes as [$m, $regex, $handler, $mw]) {
            if (!preg_match($regex, $path, $matches)) continue;
            $pathMatched = true;
            if ($m !== $method && !($method === 'HEAD' && $m === 'GET')) continue;

            // Global CSRF protection for every state-changing request.
            if ($method === 'POST' && !Csrf::verify()) {
                if (Request::isAjax()) { Controller::jsonOut(['error' => 'Your session expired. Please reload the page.'], 419); return; }
                View::renderError(419, 'Your session expired', 'For your security the form could not be sent. Go back, reload the page and try again.');
                return;
            }
            foreach ($mw as $name) {
                if (!$this->runMiddleware($name)) return;
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            [$class, $action] = $handler;
            $controller = new $class();
            $controller->$action(...array_values($params));
            return;
        }

        if ($pathMatched) {
            View::renderError(405, 'That action is not available here', 'Try going back to the previous page.');
        } else {
            View::renderError(404, 'We could not find that page', 'The link may be old or mistyped. Try the home page or search for a course.');
        }
    }

    private function runMiddleware(string $name): bool
    {
        if ($name === 'auth') {
            if (!Auth::check()) {
                if (Request::isAjax()) { Controller::jsonOut(['error' => 'Please log in.'], 401); return false; }
                $_SESSION['intended'] = Request::path();
                flash('info', 'Please log in to continue.');
                header('Location: ' . url('login'));
                return false;
            }
            return true;
        }
        if ($name === 'guest') {
            if (Auth::check()) { header('Location: ' . url('my-learning')); return false; }
            return true;
        }
        if (strpos($name, 'role:') === 0) {
            $roles = explode(',', substr($name, 5));
            $u = Auth::user();
            if (!$u || !in_array($u['role'], $roles, true)) {
                View::renderError(403, 'You do not have access to this area', 'This page is for a different type of account. If you think this is a mistake, contact a programme administrator.');
                return false;
            }
            return true;
        }
        return true;
    }
}
