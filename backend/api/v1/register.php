<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['email']) || !isset($data['password']) || !isset($data['firstName']) || !isset($data['lastName']) || !isset($data['role'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields."]);
    exit();
}

$email = trim($data['email']);
// Hash the password securely
$passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
$firstName = trim($data['firstName']);
$lastName = trim($data['lastName']);
$role = trim($data['role']); // e.g., 'tourist', 'agency', 'provider'

try {
    // Insert user into the database
    $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$firstName, $lastName, $email, $passwordHash, $role]);
    
    $userId = $pdo->lastInsertId();
    
    http_response_code(201); // Created
    echo json_encode([
        "message" => "Registration successful", 
        "user" => [
            "id" => $userId,
            "first_name" => $firstName,
            "last_name" => $lastName,
            "email" => $email,
            "role" => $role
        ]
    ]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate email)
        http_response_code(409); // Conflict
        echo json_encode(["error" => "Email address is already registered."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}
