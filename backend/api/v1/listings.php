<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            // Fetch a single listing by ID (using destinations table)
            $stmt = $pdo->prepare('SELECT id, name, price_from AS price, img_url AS image FROM destinations WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            $listing = $stmt->fetch();
            
            if ($listing) {
                echo json_encode(["listing" => $listing]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Listing not found"]);
            }
        } else {
            // Fetch all listings (using destinations table)
            $stmt = $pdo->query('SELECT id, name, price_from AS price, img_url AS image FROM destinations');
            $listings = $stmt->fetchAll();
            echo json_encode(["listings" => $listings]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
