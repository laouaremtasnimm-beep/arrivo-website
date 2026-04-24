<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

$location = $_GET['location'] ?? null;
$price_max = $_GET['price_max'] ?? null;
$category = $_GET['category'] ?? null;

$query = "SELECT * FROM listings WHERE 1=1";
$params = [];

if ($location) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
}

if ($price_max) {
    $query .= " AND price <= ?";
    $params[] = $price_max;
}

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    echo json_encode(["results" => $results]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
