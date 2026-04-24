<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch bookings for a specific user (assuming user_id is passed as a query param for simplicity)
        // In a real application, extract user_id from an auth token instead.
        if (!isset($_GET['user_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id parameter"]);
            exit();
        }
        
        $stmt = $pdo->prepare('
            SELECT b.*, l.title, l.location 
            FROM bookings b 
            JOIN listings l ON b.listing_id = l.id 
            WHERE b.user_id = ? 
            ORDER BY b.created_at DESC
        ');
        $stmt->execute([$_GET['user_id']]);
        $bookings = $stmt->fetchAll();
        
        echo json_encode(["bookings" => $bookings]);
        
    } elseif ($method === 'POST') {
        // Create a new booking
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['user_id']) || !isset($data['listing_id']) || !isset($data['start_date']) || !isset($data['end_date'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required booking fields."]);
            exit();
        }
        
        $stmt = $pdo->prepare('INSERT INTO bookings (user_id, listing_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['user_id'], 
            $data['listing_id'], 
            $data['start_date'], 
            $data['end_date'],
            'confirmed' // Default status
        ]);
        
        echo json_encode([
            "message" => "Booking created successfully",
            "booking_id" => $pdo->lastInsertId()
        ]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
