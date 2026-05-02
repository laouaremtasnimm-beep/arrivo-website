<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('
                SELECT d.id, d.name, d.price_from AS price, d.img_url AS image,
                       IFNULL(AVG(r.rating), 0) AS rating,
                       COUNT(r.id) AS review_count
                FROM destinations d
                LEFT JOIN reviews r ON r.destination_id = d.id
                WHERE d.id = ?
                GROUP BY d.id
            ');
            $stmt->execute([$_GET['id']]);
            $listing = $stmt->fetch();
            
            if ($listing) {
                echo json_encode(["listing" => $listing]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Listing not found"]);
            }
        } else {
            $stmt = $pdo->query('
                SELECT d.id, d.name, d.price_from AS price, d.img_url AS image,
                       IFNULL(AVG(r.rating), 0) AS rating,
                       COUNT(r.id) AS review_count
                FROM destinations d
                LEFT JOIN reviews r ON r.destination_id = d.id
                GROUP BY d.id
            ');
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
