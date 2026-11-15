<?php
/**
 * Router for the PHP built-in server:
 *     php -S localhost:8000 router.php
 */

$root   = __DIR__;
$public = $root . '/public';
$path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path   = rawurldecode($path);

// Block sensitive directories
if (preg_match('#^/(includes|sql)(/|$)#', $path)) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// 1. Root → public/index.php
if ($path === '/') {
    $file = $public . '/index.php';
    if (is_file($file)) {
        require $file;
        exit;
    }
    http_response_code(404);
    exit;
}

// 2. Root-level .php (login.php, change-password.php, etc.)
if (preg_match('#^/([A-Za-z0-9_-]+)\.php$#', $path, $m)) {
    $candidate = $root . '/' . $m[1] . '.php';
    if (is_file($candidate)) {
        require $candidate;
        exit;
    }
    http_response_code(404);
    exit;
}

// 3. /caretrack/* → public/caretrack/*
if (preg_match('#^/caretrack(/.*)?$#', $path, $m)) {
    $rest = $m[1] ?? '';
    if ($rest === '' || $rest === '/') {
        $rest = '/index.php';
    }
    $candidate = $public . '/caretrack' . $rest;
    if (is_file($candidate)) {
        if (pathinfo($candidate, PATHINFO_EXTENSION) === 'php') {
            require $candidate;
        } else {
            serve_static($candidate);
        }
        exit;
    }
    http_response_code(404);
    echo 'Not found';
    exit;
}

// 4. /fastaid/* → public/fastaid/*
if (preg_match('#^/fastaid(/.*)?$#', $path, $m)) {
    $rest = $m[1] ?? '';
    if ($rest === '' || $rest === '/') {
        $rest = '/index.php';
    }
    $candidate = $public . '/fastaid' . $rest;
    if (is_file($candidate)) {
        if (pathinfo($candidate, PATHINFO_EXTENSION) === 'php') {
            require $candidate;
        } else {
            serve_static($candidate);
        }
        exit;
    }
    http_response_code(404);
    echo 'Not found';
    exit;
}

// 5. Anything else → try in public/
$candidate = $public . $path;
if (is_file($candidate)) {
    if (pathinfo($candidate, PATHINFO_EXTENSION) === 'php') {
        require $candidate;
    } else {
        serve_static($candidate);
    }
    exit;
}

http_response_code(404);
echo 'Not found';
exit;

// ---- helper ----
function serve_static(string $file): void
{
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'ico'  => 'image/x-icon',
        'json' => 'application/json',
        'html' => 'text/html; charset=UTF-8',
        'txt'  => 'text/plain',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
    ];
    $mime = $mimes[$ext] ?? 'application/octet-stream';
    header('Content-Type: ' . $mime);
    readfile($file);
    exit;
}