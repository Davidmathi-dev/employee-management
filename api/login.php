<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Method Not Allowed', [], 405);
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!$input) {
    sendJsonResponse(false, 'Invalid JSON input', [], 400);
}

$email = isset($input['email']) ? cleanInput($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';

if (empty($email) || empty($password)) {
    sendJsonResponse(false, 'Email and password are required', [], 400);
}

try {
    // Authenticate user
    $stmt = $pdo->prepare("SELECT id, password_hash FROM admin_users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        
        // Token lifetime: 2 hours
        $expiresAt = date('Y-m-d H:i:s', strtotime('+2 hours'));
        
        // Store token hash in DB
        $insertStmt = $pdo->prepare("INSERT INTO auth_tokens (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)");
        $insertStmt->execute([
            'user_id' => $user['id'],
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt
        ]);
        
        sendJsonResponse(true, null, ['token' => $token]);
    } else {
        usleep(rand(100000, 300000));
        sendJsonResponse(false, 'Invalid credentials', [], 401);
    }
} catch (PDOException $e) {
    sendJsonResponse(false, 'Internal Server Error during login', [], 500);
} catch (Exception $e) {
    sendJsonResponse(false, 'Internal Server Error', [], 500);
}
?>
