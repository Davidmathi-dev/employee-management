<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';


function authenticateToken($pdo) {
    $headers = apache_request_headers();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        sendJsonResponse(false, 'Unauthorized: Missing or invalid Authorization header.', [], 401);
    }

    $token = $matches[1];
    $tokenHash = hash('sha256', $token);

    try {
        $stmt = $pdo->prepare("SELECT user_id, expires_at FROM auth_tokens WHERE token_hash = :token_hash");
        $stmt->execute(['token_hash' => $tokenHash]);
        $result = $stmt->fetch();

        if (!$result) {
            sendJsonResponse(false, 'Unauthorized: Invalid token.', [], 401);
        }

        if (strtotime($result['expires_at']) < time()) {

            $stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE token_hash = :token_hash");
            $stmt->execute(['token_hash' => $tokenHash]);
            sendJsonResponse(false, 'Unauthorized: Token has expired. Please log in again.', [], 401);
        }

        return $result['user_id'];
    } catch (PDOException $e) {
        sendJsonResponse(false, 'Internal Server Error during authentication.', [], 500);
    }
}
?>
