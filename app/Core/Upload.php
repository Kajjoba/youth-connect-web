<?php
namespace App\Core;

/** Safe file uploads: whitelist by extension AND detected MIME, random names, stored outside the web root. */
class Upload
{
    private const ALLOWED = [
        'pdf'  => ['application/pdf'],
        'png'  => ['image/png'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'txt'  => ['text/plain'],
        'csv'  => ['text/plain', 'text/csv', 'application/csv'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
    ];

    private const AUDIO = [
        'mp3' => ['audio/mpeg', 'audio/mp3'],
        'm4a' => ['audio/x-m4a', 'audio/mp4', 'video/mp4'],
        'ogg' => ['audio/ogg', 'application/ogg'],
        'wav' => ['audio/x-wav', 'audio/wav', 'audio/vnd.wave'],
    ];

    /** @param string $kind 'doc' (evidence and submissions) or 'audio' (lesson audio)
     *  @return array{path:string,name:string,mime:string,size:int}|string  array on success, user-friendly error string on failure */
    public static function store(array $file, string $kind = 'doc')
    {
        $allowed = $kind === 'audio' ? self::AUDIO : self::ALLOWED;
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return 'Choose a file to upload.';
        if ($file['error'] !== UPLOAD_ERR_OK) return 'The upload did not finish. Check your connection and try again.';
        $max = (int)config($kind === 'audio' ? 'security.audio_max_bytes' : 'security.upload_max_bytes');
        if ($file['size'] > $max) return 'That file is larger than ' . round($max / 1048576) . ' MB. Compress it or upload a link instead.';
        if (!is_uploaded_file($file['tmp_name'])) return 'The upload could not be verified. Please try again.';

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset($allowed[$ext])) return $kind === 'audio' ? 'Audio must be MP3, M4A, OGG or WAV.' : 'This file type is not accepted. Use PDF, PNG, JPG, TXT, CSV, DOCX, XLSX or PPTX.';
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, $allowed[$ext], true)) return 'The file content does not match its type. Please save it again and re-upload.';

        $dir = config('storage_path') . '/uploads/' . date('Y/m');
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $rel = date('Y/m') . '/' . bin2hex(random_bytes(16)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], config('storage_path') . '/uploads/' . $rel)) {
            return 'We could not save the file. Please try again.';
        }
        $clean = preg_replace('/[^A-Za-z0-9._ -]/', '_', basename($file['name']));
        return ['path' => $rel, 'name' => mb_substr($clean, 0, 180), 'mime' => $mime, 'size' => (int)$file['size']];
    }

    /** Remove a stored file (ignored if it does not exist or the name is not a valid stored name). */
    public static function delete(?string $rel): void
    {
        $p = $rel ? self::fullPath($rel) : null;
        if ($p) @unlink($p);
    }

    public static function fullPath(string $rel): ?string
    {
        if (!preg_match('#^\d{4}/\d{2}/[a-f0-9]{32}\.[a-z0-9]{3,4}$#', $rel)) return null;   // blocks path traversal
        $p = config('storage_path') . '/uploads/' . $rel;
        return is_file($p) ? $p : null;
    }
}
