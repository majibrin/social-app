<?php
header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/Config/Env.php';
require_once dirname(__DIR__) . '/src/Config/Database.php';
require_once dirname(__DIR__) . '/src/Controllers/AuthController.php';

Env::load(dirname(__DIR__));

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$action = $_GET['action'] ?? '';
$response = ["status" => "error", "message" => "Invalid endpoint routing action."];

if ($action === 'debug_env') {
    echo json_encode([
        'DB_HOST' => getenv('DB_HOST'),
        'db_host_env' => $_ENV['DB_HOST'] ?? 'not in _ENV',
        'db_connected' => $db !== null ? 'yes' : 'no',
    ]);
    exit;
}

$authController = new AuthController($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'register':
            $response = $authController->register($data);
            break;
        case 'login':
            $response = $authController->login($data);
            break;
        case 'forgot_password':
            $response = $authController->forgotPassword($data);
            if (isset($_SESSION['smtp_debug'])) {
                $response['debug_trace_log'] = $_SESSION['smtp_debug'];
                unset($_SESSION['smtp_debug']);
            }
            break;
        case 'verify_code':
            $response = $authController->verifyCode($data);
            break;
        case 'reset_password':
            $response = $authController->resetPassword($data);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'check') {
        $response = !empty($_SESSION['user_id'])
            ? ["logged_in" => true, "user_id" => $_SESSION['user_id'], "username" => $_SESSION['username']]
            : ["logged_in" => false];
    } elseif ($action === 'logout') {
        session_unset();
        session_destroy();
        $response = ["status" => "success", "message" => "Logged out."];
    }
}

echo json_encode($response);
exit;
