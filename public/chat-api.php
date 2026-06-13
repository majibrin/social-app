<?php
// public/chat-api.php
header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

require_once dirname(__DIR__) . '/src/Config/Database.php';
require_once dirname(__DIR__) . '/src/Controllers/ChatController.php';

$database = new Database();
$db = $database->getConnection();
$chatController = new ChatController($db);

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true) ?? [];
$response = [];

if ($method === 'GET') {
    $response = $chatController->getAllMessages();
} elseif ($method === 'POST') {
    $response = $chatController->postMessage($data);
}

echo json_encode($response);
exit;
