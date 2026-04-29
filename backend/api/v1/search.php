<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

$q = $_GET['q'] ?? null;

$query = "SELECT * FROM destinations WHERE 1=1";
$params = [];

if ($q) {
    $query .= " AND (name LIKE ? OR country LIKE ? OR region LIKE ? OR description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    // Add a 'category' field so frontend knows it's a destination
    $formattedResults = array_map(function($row) {
        $row['category'] = 'dest';
        // Map database fields to frontend fields if necessary
        $row['price'] = $row['price_from'];
        $row['img'] = $row['img_url'];
        $row['desc'] = $row['description'];
        return $row;
    }, $results);

    echo json_encode(["results" => $formattedResults]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
