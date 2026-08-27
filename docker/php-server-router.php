<?php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = urldecode($path);

if ($path !== '/' && ! str_contains($path, '..')) {
    $file = __DIR__.'/../public'.$path;

    if (is_file($file)) {
        return false;
    }
}

require __DIR__.'/../public/index.php';
