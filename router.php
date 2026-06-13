<?php
// router.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$public_dir = __DIR__ . '/public';

// Clear out empty spaces or double slashes
$clean_uri = trim($uri, '/');

// 1. Allow Auth API processing
if ($clean_uri === 'auth-api.php') {
    require $public_dir . '/auth-api.php';
    exit;
}

// 2. Allow Chat API processing
if ($clean_uri === 'chat-api.php') {
    require $public_dir . '/chat-api.php';
    exit;
}

// 3. Allow primary layout rendering
if ($clean_uri === '' || $clean_uri === 'index.php') {
    require $public_dir . '/index.php';
    exit;
}

// 4. Fallback filter for serving raw physical static media assets (css, images)
$target_file = $public_dir . '/' . $clean_uri;
if (file_exists($target_file) && !is_dir($target_file)) {
    return false;
}

// 5. Clean structural block trigger
http_response_code(404);
echo "<h1 style='color:red; font-family:sans-serif; text-align:center; margin-top:50px;'>404 Not Found</h1>";
echo "<p style='text-align:center; font-family:sans-serif;'>The path '<strong>" . htmlspecialchars($uri) . "</strong>' could not be resolved.</p>";
exit;
