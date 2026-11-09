<?php
declare(strict_types=1);
/** Optional local PHP development server router. Apache/XAMPP use .htaccess instead. */
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);
if (str_contains($path, '..') || preg_match('~(?:^|/)(?:data|storage|config|includes|tools|vendor|\.git)(?:/|$)|/(?:\.env[^/]*|\.gitignore|\.htaccess|composer\.(?:json|lock)|README\.md|TESTING\.md|router\.php)$~i', $path)) {
    http_response_code(403); exit('Forbidden');
}
$file = realpath(__DIR__ . $path);
if ($file && str_starts_with($file, realpath(__DIR__) . DIRECTORY_SEPARATOR) && is_file($file)) return false;
if ($path === '/' || $path === '') { require __DIR__ . '/index.php'; return true; }
http_response_code(404);
exit('Not found');
