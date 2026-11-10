<?php
namespace App\Controllers;

use App\Core\{Controller, DB};

/** Public, shareable verification page: employers can confirm a certificate is genuine. Shows the name only. */
class CertificateController extends Controller
{
    public function show(string $code): void
    {
        $c = DB::one('SELECT c.*, u.name FROM certificates c JOIN users u ON u.id = c.user_id WHERE c.code = ?', [strtoupper($code)]);
        if (!$c) { $this->notFound(); return; }
        $this->view('certificates/show', ['c' => $c, 'title' => 'Certificate ' . $c['code']]);
    }
}
