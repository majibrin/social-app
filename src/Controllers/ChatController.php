<?php
// src/Controllers/ChatController.php
require_once dirname(__DIR__) . '/Models/Chat.php';

class ChatController {
    private $chat;

    public function __construct($db) {
        $this->chat = new Chat($db);
    }

    public function getAllMessages() {
        return $this->chat->readAll();
    }

    public function postMessage($data) {
        if (empty($data['message'])) {
            http_response_code(400);
            return ["status" => "error", "message" => "Message body cannot be empty."];
        }
        
        $userId = $_SESSION['user_id'];
        if ($this->chat->create($userId, $data['message'])) {
            return ["status" => "success"];
        }
        
        http_response_code(500);
        return ["status" => "error", "message" => "Failed to save message."];
    }
}
