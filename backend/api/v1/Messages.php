<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {

        // ?message_id=X  → single message (used by ComposeModal to resolve sender_id)
        if (isset($_GET['message_id'])) {
            $stmt = $pdo->prepare('
                SELECT m.*,
                       u.first_name AS sender_first,
                       u.last_name  AS sender_last
                FROM   messages m
                JOIN   users    u ON m.sender_id = u.id
                WHERE  m.id = ?
                LIMIT  1
            ');
            $stmt->execute([(int) $_GET['message_id']]);
            $msg = $stmt->fetch();

            if (!$msg) {
                http_response_code(404);
                echo json_encode(["error" => "Message not found"]);
                exit();
            }
            echo json_encode(["message" => $msg]);
            exit();
        }

        // ?user_id=X  → inbox
        if (!isset($_GET['user_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id parameter"]);
            exit();
        }

        $userId = (int) $_GET['user_id'];

        $stmt = $pdo->prepare('
            SELECT m.*,
                   u_s.first_name AS sender_first,
                   u_s.last_name  AS sender_last,
                   u_r.first_name AS receiver_first,
                   u_r.last_name  AS receiver_last
            FROM   messages m
            JOIN   users    u_s ON m.sender_id   = u_s.id
            JOIN   users    u_r ON m.receiver_id = u_r.id
            WHERE  m.receiver_id = ? OR m.sender_id = ?
            ORDER  BY m.created_at ASC
        ');
        $stmt->execute([$userId, $userId]);
        $messages = $stmt->fetchAll();

        echo json_encode(["messages" => $messages]);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['sender_id', 'receiver_id', 'content'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required field: $field"]);
                exit();
            }
        }

        $stmt = $pdo->prepare('
            INSERT INTO messages (sender_id, receiver_id, subject, content)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([
            $data['sender_id'],
            $data['receiver_id'],
            $data['subject'] ?? null,
            $data['content'],
        ]);

        echo json_encode([
            "message"    => "Message sent",
            "message_id" => $pdo->lastInsertId(),
        ]);

    } elseif ($method === 'PATCH') {
        // Mark as read: { id: X }
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing message id"]);
            exit();
        }

        $stmt = $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?');
        $stmt->execute([$data['id']]);

        echo json_encode(["message" => "Marked as read"]);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing message id"]);
            exit();
        }

        $stmt = $pdo->prepare('DELETE FROM messages WHERE id = ?');
        $stmt->execute([$data['id']]);

        echo json_encode(["message" => "Message deleted successfully"]);

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}