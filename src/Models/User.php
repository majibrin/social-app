<?php
// src/Models/User.php

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function register($username, $password, $email) {
        $checkQuery = "SELECT id FROM " . $this->table_name . " WHERE username = :user OR email = :email LIMIT 1";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":user", $username);
        $checkStmt->bindParam(":email", $email);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            return "taken";
        }

        $query = "INSERT INTO " . $this->table_name . " (username, password, email) VALUES (:username, :password, :email)";
        $stmt = $this->conn->prepare($query);

        $username = htmlspecialchars(strip_tags($username));
        $email = htmlspecialchars(strip_tags($email));
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":email", $email);

        return $stmt->execute() ? "success" : "failed";
    }

    public function login($email, $password) {
        $query = "SELECT id, username, password FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                return true;
            }
        }
        return false;
    }

    public function storeResetTokenByEmail($email, $token) {
        $query = "UPDATE " . $this->table_name . "
                  SET reset_token = :token, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)
                  WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":email", $email);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function verifyAndResetPasswordByEmail($email, $token, $new_password) {
        $query = "SELECT id FROM " . $this->table_name . "
                  WHERE email = :email AND reset_token = :token AND reset_expires > NOW() LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":token", $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $updateQuery = "UPDATE " . $this->table_name . "
                            SET password = :password, reset_token = NULL, reset_expires = NULL
                            WHERE email = :email";
            $updateStmt = $this->conn->prepare($updateQuery);
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $updateStmt->bindParam(":password", $hashed_password);
            $updateStmt->bindParam(":email", $email);
            return $updateStmt->execute();
        }
        return false;
    }
}
