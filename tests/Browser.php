<?php
/** Minimal browser for tests: cookie jar, automatic CSRF token, optional redirect following. */
class Browser
{
    public string $jar; public string $base; public string $token = ''; public array $last = [];
    public function __construct(string $base) { $this->base = $base; $this->jar = tempnam(sys_get_temp_dir(), 'ycp'); }
    public function __destruct() { @unlink($this->jar); }

    public function req(string $method, string $path, array $data = [], array $headers = [], bool $follow = false, bool $csrf = true): array
    {
        $ch = curl_init($this->base . $path);
        $hdr = [];
        if ($method === 'POST' && $csrf && !isset($data['_csrf'])) $data['_csrf'] = $this->token;
        curl_setopt_array($ch, [
            CURLOPT_PATH_AS_IS => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->jar, CURLOPT_COOKIEFILE => $this->jar,
            CURLOPT_ENCODING => '', CURLOPT_FOLLOWLOCATION => $follow, CURLOPT_MAXREDIRS => 5, CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADERFUNCTION => function ($c, $line) use (&$hdr) { $p = explode(':', $line, 2); if (count($p) === 2) $hdr[strtolower(trim($p[0]))] = trim($p[1]); return strlen($line); },
        ]);
        if ($method === 'POST') {
            $hasFile = false; foreach ($data as $v) if ($v instanceof CURLFile) $hasFile = true;
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $hasFile ? $data : http_build_query($data));   // http_build_query supports answers[12]=34
        }
        $body = curl_exec($ch);
        $r = ['status' => curl_getinfo($ch, CURLINFO_HTTP_CODE), 'body' => (string)$body, 'headers' => $hdr, 'wire' => (int)curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD), 'url' => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)];
        if (preg_match('/name="ycp-csrf" content="([a-f0-9]+)"/', $r['body'], $m)) $this->token = $m[1];
        return $this->last = $r;
    }
    public function get(string $p, array $h = [], bool $follow = true): array { return $this->req('GET', $p, [], $h, $follow); }
    public function post(string $p, array $d = [], bool $follow = true): array { return $this->req('POST', $p, $d, [], $follow); }
    public function login(string $email, string $pw): array
    {
        $this->get('/login');
        $r = $this->post('/login', ['email' => $email, 'password' => $pw]);
        $this->get('/');   // refresh CSRF token for the new session
        return $r;
    }
}
function has(array $r, string $needle): bool { return stripos($r['body'], $needle) !== false; }
function newBrowser(string $b, ?string $email = null, string $pw = 'Learn@12345'): Browser { $x = new Browser($b); if ($email) $x->login($email, $pw); return $x; }
