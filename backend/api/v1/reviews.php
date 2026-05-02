<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Requires item_type + item_id  e.g. ?item_type=package&item_id=1
        if (!isset($_GET['item_type']) || !isset($_GET['item_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing item_type or item_id parameter"]);
            exit();
        }

        $itemType = $_GET['item_type'];   // 'package' | 'service' | 'destination'
        $itemId   = (int) $_GET['item_id'];

        $columnMap = [
            'package'     => 'package_id',
            'service'     => 'service_id',
            'destination' => 'destination_id',
        ];

        if (!array_key_exists($itemType, $columnMap)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid item_type. Use package, service, or destination."]);
            exit();
        }

        $col  = $columnMap[$itemType];
        $stmt = $pdo->prepare("
            SELECT r.id, r.user_id, r.rating, r.comment, r.created_at,
                   u.first_name, u.last_name
            FROM   reviews r
            JOIN   users   u ON r.user_id = u.id
            WHERE  r.$col = ?
            ORDER  BY r.created_at DESC
        ");
        $stmt->execute([$itemId]);
        $reviews = $stmt->fetchAll();

        echo json_encode(["reviews" => $reviews]);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['user_id', 'item_type', 'item_id', 'rating'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required field: $field"]);
                exit();
            }
        }

        $columnMap = [
            'package'     => 'package_id',
            'service'     => 'service_id',
            'destination' => 'destination_id',
        ];

        $itemType = $data['item_type'];
        if (!array_key_exists($itemType, $columnMap)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid item_type."]);
            exit();
        }

        $col     = $columnMap[$itemType];
        $comment = $data['comment'] ?? '';

        // Bypass foreign key checks to allow reviews for demo items not yet in the DB
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, $col, item_type, rating, comment)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['user_id'],
            $data['item_id'],
            $itemType,
            $data['rating'],
            $comment,
        ]);

        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

        echo json_encode([
            "message"   => "Review submitted successfully",
            "review_id" => $pdo->lastInsertId(),
        ]);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['rating'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing review id or rating"]);
            exit();
        }

        $comment = $data['comment'] ?? '';

        $stmt = $pdo->prepare('UPDATE reviews SET rating = ?, comment = ? WHERE id = ?');
        $stmt->execute([$data['rating'], $comment, $data['id']]);

        echo json_encode(["message" => "Review updated successfully"]);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing review id"]);
            exit();
        }

        $stmt = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
        $stmt->execute([$data['id']]);

        echo json_encode(["message" => "Review deleted"]);

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}