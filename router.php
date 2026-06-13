<?php
// router.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$public_dir = __DIR__ . '/public';
$clean_uri = trim($uri, '/');

if (strpos($clean_uri, '.env') !== false) {
    http_response_code(403);
    echo "<h1 style='color:red; font-family:sans-serif; text-align:center; margin-top:50px;'>403 Forbidden</h1>";
    exit;
}

if ($clean_uri === 'auth-api.php') {
    require $public_dir . '/auth-api.php';
    exit;
}

if ($clean_uri === 'chat-api.php') {
    require $public_dir . '/chat-api.php';
    exit;
}

if ($clean_uri === '' || $clean_uri === 'index.php') {
    require $public_dir . '/index.php';
    exit;
}

// FIX: Force the built-in server to look inside the public folder for static assets
$target_file = $public_dir . '/' . $clean_uri;
if (file_exists($target_file) && !is_dir($target_file)) {
    // Determine the content-type so the browser reads the asset correctly
    $ext = pathinfo($target_file, PATHINFO_EXTENSION);
    if ($ext === 'css') header("Content-Type: text/css");
    if ($ext === 'js') header("Content-Type: application/javascript");
    
    readfile($target_file);
    exit;
}

http_response_code(404);
echo "<h1 style='color:red; font-family:sans-serif; text-align:center; margin-top:50px;'>404 Not Found</h1>";
exit;
