<?php
// Chat.php
class Chat {
    private $conn;
    private $table_name = "messages";

    // Constructor injection: Requires a database connection object
    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to fetch all chat messages
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Method to create a new chat message
    public function create($sender_id, $message) {
        $query = "INSERT INTO " . $this->table_name . " (sender_id, message) VALUES (:sender_id, :message)";
        $stmt = $this->conn->prepare($query);

        // Sanitize input strings against XSS attacks
        $sender_id = htmlspecialchars(strip_tags($sender_id));
        $message = htmlspecialchars(strip_tags($message));

        // Bind your OOP parameters safely to prevent SQL injection
        $stmt->bindParam(":sender_id", $sender_id);
        $stmt->bindParam(":message", $message);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
