<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if (is_file($file) && str_ends_with($file, '.svg')) {
    header('Content-Type: image/svg+xml');
    header('Cache-Control: public, max-age=31536000, immutable');
    readfile($file);
    return true;
}

return false;
