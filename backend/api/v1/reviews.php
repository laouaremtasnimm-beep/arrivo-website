<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Get reviews for a specific listing
        if (!isset($_GET['listing_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing listing_id parameter"]);
            exit();
        }
        
        $stmt = $pdo->prepare('
            SELECT r.*, u.first_name, u.last_name 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.package_id = ? OR r.service_id = ? OR r.destination_id = ?
            ORDER BY r.created_at DESC
        ');
        $stmt->execute([$_GET['listing_id']]);
        $reviews = $stmt->fetchAll();
        
        echo json_encode(["reviews" => $reviews]);
        
    } elseif ($method === 'POST') {
        // Create a new review
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['user_id']) || !isset($data['listing_id']) || !isset($data['rating'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required review fields."]);
            exit();
        }
        
        $comment = $data['comment'] ?? '';
        
        $stmt = $pdo->prepare('INSERT INTO reviews (user_id, listing_id, rating, comment) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $data['user_id'], 
            $data['listing_id'], 
            $data['rating'], 
            $comment
        ]);
        
        echo json_encode([
            "message" => "Review submitted successfully",
            "review_id" => $pdo->lastInsertId()
        ]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
