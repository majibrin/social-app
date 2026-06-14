<?php
// src/Config/Database.php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        $this->host     = $_ENV['DB_HOST']     ?? '127.0.0.1';
        $this->db_name  = $_ENV['DB_NAME']     ?? 'social_app';
        $this->username = $_ENV['DB_USER']     ?? 'root';
        $this->password = $_ENV['DB_PASS']     ?? '';
        $this->port     = $_ENV['DB_PORT']     ?? '3306';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
