<?php
// public/auth-api.php
header("Content-Type: application/json");

// Start native session management to track who is logged in across reloads
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/Database.php';
require_once dirname(__DIR__) . '/User.php';

$database = new Database();
$db = $database->getConnection();
$userObj = new User($db);

$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($data['username']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing required fields."]);
        exit;
    }

    if ($action === 'register') {
        $result = $userObj->register($data['username'], $data['password']);
        if ($result === 'success') {
            echo json_encode(["status" => "success", "message" => "Registration complete!"]);
        } elseif ($result === 'username_taken') {
            http_response_code(409);
            echo json_encode(["status" => "error", "message" => "Username is already taken."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Registration processing failed."]);
        }
    } elseif ($action === 'login') {
        if ($userObj->login($data['username'], $data['password'])) {
            // Write authenticated profile integers directly to session memory
            $_SESSION['user_id'] = $userObj->id;
            $_SESSION['username'] = $userObj->username;
            echo json_encode(["status" => "success", "message" => "Logged in successfully!"]);
        } else {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Invalid username or password."]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Session check endpoint to see if user profile memory is active
    if ($action === 'check') {
        if (!empty($_SESSION['user_id'])) {
            echo json_encode([
                "logged_in" => true,
                "user_id" => $_SESSION['user_id'],
                "username" => $_SESSION['username']
            ]);
        } else {
            echo json_encode(["logged_in" => false]);
        }
    } elseif ($action === 'logout') {
        session_unset();
        session_destroy();
        echo json_encode(["status" => "success", "message" => "Logged out."]);
    }
}
