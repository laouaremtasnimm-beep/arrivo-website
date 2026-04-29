<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (!isset($_GET['user_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id parameter"]);
            exit();
        }
        
        $stmt = $pdo->prepare('SELECT id, first_name, last_name, email, role, company_name, avatar_url, is_verified, created_at FROM users WHERE id = ?');
        $stmt->execute([$_GET['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo json_encode(["profile" => $user]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
        }
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['user_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id parameter"]);
            exit();
        }
        
        // Update basic info dynamically based on provided fields
        $updates = [];
        $params = [];
        
        if (isset($data['first_name'])) {
            $updates[] = "first_name = ?";
            $params[] = $data['first_name'];
        }
        
        if (isset($data['last_name'])) {
            $updates[] = "last_name = ?";
            $params[] = $data['last_name'];
        }

        if (isset($data['company_name'])) {
            $updates[] = "company_name = ?";
            $params[] = $data['company_name'];
        }
        
        if (empty($updates)) {
            echo json_encode(["message" => "No profile updates provided"]);
            exit();
        }
        
        $params[] = $data['user_id']; // For the WHERE clause
        $query = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        echo json_encode(["message" => "Profile updated successfully"]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
