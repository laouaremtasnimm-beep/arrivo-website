<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

if (!isset($_GET['email'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing email parameter"]);
    exit();
}

try {
    $stmt = $pdo->prepare('
        SELECT id,
               CONCAT(first_name, " ", last_name) AS name,
               email, role, company_name
        FROM users
        WHERE email = ?
        LIMIT 1
    ');
    $stmt->execute([trim($_GET['email'])]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(["error" => "User not found"]);
        exit();
    }

    echo json_encode(["user" => $user]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}