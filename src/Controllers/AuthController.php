<?php
// src/Controllers/AuthController.php
require_once dirname(__DIR__) . '/Models/User.php';

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class AuthController {
    private $user;

    public function __construct($db) {
        $this->user = new User($db);
    }

    public function register($data) {
        if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            http_response_code(400);
            return ["status" => "error", "message" => "Username, password, and email are required."];
        }
        $res = $this->user->register($data['username'], $data['password'], $data['email']);
        if ($res === 'success') return ["status" => "success", "message" => "Registration complete!"];
        if ($res === 'taken') {
            http_response_code(409);
            return ["status" => "error", "message" => "Username or Email is already taken."];
        }
        http_response_code(500);
        return ["status" => "error", "message" => "Registration failed."];
    }

    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            return ["status" => "error", "message" => "Email and password are required fields."];
        }
        
        if ($this->user->login($data['email'], $data['password'])) {
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['username'] = $this->user->username;
            return ["status" => "success", "message" => "Logged in successfully!"];
        }
        http_response_code(401);
        return ["status" => "error", "message" => "Invalid email or password."];
    }

    public function forgotPassword($data) {
        if (empty($data['email'])) {
            http_response_code(400);
            return ["status" => "error", "message" => "Email address is required."];
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        if ($this->user->storeResetTokenByEmail($data['email'], $code)) {
            // Keep your terminal log safe so you never get locked out
            $log_file = dirname(dirname(__DIR__)) . '/reset_codes.log';
            file_put_contents($log_file, "[" . date('H:i:s') . "] Reset Code for " . $data['email'] . " is: " . $code . "\n", FILE_APPEND);

            // Attempt secure SMTP delivery over mobile-friendly SSL Port 465
            if ($this->sendEmail($data['email'], $code)) {
                return ["status" => "success", "message" => "Reset code sent directly to your email inbox!"];
            }

            return ["status" => "success", "message" => "Reset code written to local file 'reset_codes.log' (Network delivery deferred)."];
        }
        
        http_response_code(404);
        return ["status" => "error", "message" => "Email address not found."];
    }

    public function verifyCode($data) {
        if (empty($data['email']) || empty($data['token'])) {
            http_response_code(400);
            return ["status" => "error", "message" => "Email and code token are required."];
        }

        $query = "SELECT id FROM users WHERE email = :email AND reset_token = :token AND reset_expires > NOW() LIMIT 1";
        $stmt = $this->user->getConnection()->prepare($query);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":token", $data['token']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['reset_email'] = $data['email'];
            $_SESSION['reset_token'] = $data['token'];
            return ["status" => "success", "message" => "Code verified successfully! Choose your new password."];
        }

        http_response_code(400);
        return ["status" => "error", "message" => "Invalid or expired verification code."];
    }

    public function resetPassword($data) {
        $email = $_SESSION['reset_email'] ?? $data['email'] ?? '';
        $token = $_SESSION['reset_token'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            return ["status" => "error", "message" => "Missing session token context."];
        }

        if ($this->user->verifyAndResetPasswordByEmail($email, $token, $password)) {
            unset($_SESSION['reset_email'], $_SESSION['reset_token']);
            return ["status" => "success", "message" => "Password saved! Proceed to login."];
        }

        http_response_code(400);
        return ["status" => "error", "message" => "Password override execution failure."];
    }

    private function sendEmail($to, $code)
{
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
        file_put_contents(dirname(__DIR__, 2) . '/smtp_debug.log', $str, FILE_APPEND);
        };
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        $mail->setFrom($_ENV['SMTP_USER'], 'Social App');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = "Password Reset Code";
        $mail->Body = "
            <h2>Password Reset</h2>
            <p>Your reset code is:</p>
            <h1>$code</h1>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("MAIL ERROR: " . $mail->ErrorInfo);
        return false;
    }
  }

}
