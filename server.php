<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$path = __DIR__.'/public'.$uri;

if ($uri !== '/' && is_file($path)) {
    if (str_starts_with($uri, '/build/')) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $types = ['css' => 'text/css; charset=utf-8', 'js' => 'text/javascript; charset=utf-8', 'woff' => 'font/woff', 'woff2' => 'font/woff2', 'svg' => 'image/svg+xml'];
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Content-Type: '.($types[$ext] ?? 'application/octet-stream'));
        if (in_array($ext, ['css', 'js', 'svg'], true) && str_contains($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') && filesize($path) > 1024) {
            header('Content-Encoding: gzip');
            header('Vary: Accept-Encoding');
            echo gzencode(file_get_contents($path), 6);

            return true;
        }
        readfile($path);

        return true;
    }

    return false;
}

require __DIR__.'/public/index.php';
