<?php
// public/chat-api.php
header("Content-Type: application/json");

// 1. Initialize persistent session memory to track the logged-in user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Reject requests immediately if the user is not authenticated
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

require_once dirname(__DIR__) . '/Database.php';
require_once dirname(__DIR__) . '/Chat.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $chat = new Chat($db);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!empty($data['message'])) {
            // FIX: Pass the real logged-in user session ID into the database
            $active_user_id = $_SESSION['user_id'];
            
            if ($chat->create($active_user_id, $data['message'])) {
                echo json_encode(["status" => "success"]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "SQL execution failed."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Empty message."]);
        }
    } elseif ($method === 'GET') {
        $stmt = $chat->readAll();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($messages ? $messages : []);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
