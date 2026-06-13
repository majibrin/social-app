<?php
// User.php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to create a new user profile safely
    public function register($username, $password) {
        // Prevent username duplication
        $checkQuery = "SELECT id FROM " . $this->table_name . " WHERE username = :user LIMIT 1";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":user", $username);
        $checkStmt->execute();
        if ($checkStmt->rowCount() > 0) {
            return "username_taken";
        }

        $query = "INSERT INTO " . $this->table_name . " (username, password) VALUES (:username, :password)";
        $stmt = $this->conn->prepare($query);

        // Sanitize string inputs
        $username = htmlspecialchars(strip_tags($username));
        
        // Securely hash passwords so plain text passwords aren't stored
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);

        if ($stmt->execute()) {
            return "success";
        }
        return "failed";
    }

    // Method to verify user credentials and initiate session storage
    public function login($username, $password) {
        $query = "SELECT id, username, password FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify if user input password matches our secure BCRYPT database string
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                return true;
            }
        }
        return false;
    }
}
