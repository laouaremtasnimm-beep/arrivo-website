<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (!isset($_GET['user_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id"]);
            exit();
        }
        $userId = (int)$_GET['user_id'];
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([$userId]);
        echo json_encode(["notifications" => $stmt->fetchAll()]);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['user_id']) || !isset($data['title'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields"]);
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, body, icon, link) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['type'] ?? 'system',
            $data['title'],
            $data['body'] ?? '',
            $data['icon'] ?? '🔔',
            $data['link'] ?? '/'
        ]);

        echo json_encode(["message" => "Notification created", "id" => $pdo->lastInsertId()]);

    } elseif ($method === 'PUT') {
        // Mark as read
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['id'])) {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            $stmt->execute([$data['id']]);
        } elseif (isset($data['user_id'])) {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
            $stmt->execute([$data['user_id']]);
        }
        echo json_encode(["message" => "Updated successfully"]);

    } elseif ($method === 'DELETE') {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing id"]);
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(["message" => "Deleted successfully"]);

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
