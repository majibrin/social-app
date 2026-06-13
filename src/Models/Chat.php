<?php
// src/Models/Chat.php

class Chat {
    private $conn;
    private $table_name = "messages";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        // SQL Table Inner Join link structure to grab active user handles
        $query = "SELECT m.*, u.username 
                  FROM " . $this->table_name . " m 
                  INNER JOIN users u ON m.sender_id = u.id 
                  ORDER BY m.id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($sender_id, $message) {
        $query = "INSERT INTO " . $this->table_name . " (sender_id, message) VALUES (:sender_id, :message)";
        $stmt = $this->conn->prepare($query);

        $sender_id = htmlspecialchars(strip_tags($sender_id));
        $message = htmlspecialchars(strip_tags($message));

        $stmt->bindParam(":sender_id", $sender_id);
        $stmt->bindParam(":message", $message);

        return $stmt->execute();
    }
}
