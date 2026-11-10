<?php
namespace App\Core;

class View
{
    /** Render a view inside the layout and send it, with conditional-GET (ETag) support. */
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $content = self::capture($view, $data);
        $html = $layout ? self::capture($layout, $data + ['content' => $content]) : $content;
        self::send($html);
    }

    public static function capture(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include BASE_PATH . '/app/Views/' . $view . '.php';
        return ob_get_clean();
    }

    /**
     * Send HTML with a strong validator. If the browser already has this exact version it gets an empty
     * 304 instead of the whole page: refreshing an unchanged page costs a few hundred bytes, not kilobytes.
     */
    public static function send(string $html, int $status = 200): void
    {
        Session::clearOldInput();
        $etag = '"' . md5($html) . '"';
        header('Cache-Control: private, no-cache');
        header('Vary: Cookie, Accept-Encoding');
        header('ETag: ' . $etag);
        if ($status === 200 && Request::method() === 'GET' && trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            http_response_code(304);
            return;
        }
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    public static function renderError(int $code, string $title, string $message): void
    {
        try {
            $html = self::capture('layouts/main', [
                'title' => $title,
                'content' => self::capture('errors/error', compact('code', 'title', 'message')),
            ]);
        } catch (\Throwable $e) {
            $html = '<!doctype html><meta charset="utf-8"><title>' . e($title) . '</title><h1>' . e($title) . '</h1><p>' . e($message) . '</p>';
        }
        if (!headers_sent()) http_response_code($code);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
}
