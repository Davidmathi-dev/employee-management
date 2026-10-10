<?php

require_once __DIR__ . '/config/database.php';

$email = 'admin@example.com';
$password = '123456';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO admin_users (email, password_hash) VALUES (:email, :password_hash)");
    $stmt->execute(['email' => $email, 'password_hash' => $password_hash]);
    echo "Admin user seeded successfully.\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "Admin user already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
