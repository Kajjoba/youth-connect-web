<?php
namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], int $status = 200): void
    {
        $data += ['title' => config('app.name')];
        if ($status !== 200) {
            $html = View::capture('layouts/main', $data + ['content' => View::capture($view, $data)]);
            View::send($html, $status);
            return;
        }
        View::render($view, $data);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)));
    }

    public static function jsonOut(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: private, no-cache');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function json(array $data, int $status = 200): void { self::jsonOut($data, $status); }

    protected function notFound(): void
    {
        View::renderError(404, 'We could not find that page', 'The link may be old, or you may not have access to it.');
    }

    protected function forbidden(string $msg = 'You do not have access to this.'): void
    {
        View::renderError(403, 'Access not available', $msg);
    }

    /** Validate input; on failure flash errors, keep the user's input, redirect back. Returns clean data or null. */
    protected function validate(array $rules, string $backTo, array $labels = []): ?array
    {
        $v = Validator::make($_POST, $rules, $labels);
        if ($v->fails()) {
            $old = $_POST;
            unset($old['password'], $old['password_confirmation'], $old['_csrf']);
            Session::withErrors($v->errors(), $old);
            flash('error', 'Please check the highlighted fields and try again.');
            $this->redirect($backTo);
            return null;
        }
        return $v->data();
    }

    protected function user(): ?array { return Auth::user(); }
    protected function uid(): int { return (int)(Auth::user()['id'] ?? 0); }
}
