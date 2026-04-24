<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing email or password."]);
    exit();
}

$email = trim($data['email']);
$password = $data['password'];

try {
    $stmt = $pdo->prepare('SELECT id, first_name, last_name, email, role, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify password
    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']); // Do not send hash back to the frontend

        // In a real application, you would generate a JWT or establish a session here
        echo json_encode([
            "message" => "Login successful", 
            "user" => $user,
            "token" => "sample-jwt-token-replace-me"
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Invalid email or password."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
