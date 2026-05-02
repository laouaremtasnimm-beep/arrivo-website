<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('
                SELECT s.*, u.company_name AS provider_name,
                       IFNULL(AVG(r.rating), 0) AS rating,
                       COUNT(r.id) AS review_count
                FROM services s
                LEFT JOIN users u ON u.id = s.provider_id
                LEFT JOIN reviews r ON r.service_id = s.id
                WHERE s.id = ?
                GROUP BY s.id
            ');
            $stmt->execute([$_GET['id']]);
            $service = $stmt->fetch();

            if (!$service) {
                http_response_code(404);
                echo json_encode(["error" => "Service not found"]);
                exit();
            }
            echo json_encode(["service" => $service]);

        } elseif (isset($_GET['provider_id'])) {
            $stmt = $pdo->prepare('
                SELECT s.*, u.company_name AS provider_name,
                       COUNT(DISTINCT b.id) AS booking_count,
                       IFNULL(AVG(r.rating), 0) AS rating
                FROM   services s
                LEFT   JOIN users u ON u.id = s.provider_id
                LEFT   JOIN bookings b ON b.service_id = s.id
                LEFT   JOIN reviews r ON r.service_id = s.id
                WHERE  s.provider_id = ?
                GROUP  BY s.id
                ORDER  BY s.created_at DESC
            ');
            $stmt->execute([$_GET['provider_id']]);
            $services = $stmt->fetchAll();
            echo json_encode(["services" => $services]);

        } else {
            $stmt = $pdo->query('
                SELECT s.*, u.company_name AS provider_name,
                       IFNULL(AVG(r.rating), 0) AS rating,
                       COUNT(r.id) AS review_count
                FROM services s
                LEFT JOIN users u ON u.id = s.provider_id
                LEFT JOIN reviews r ON r.service_id = s.id
                WHERE s.is_available = 1
                GROUP BY s.id
                ORDER BY s.created_at DESC
            ');
            $services = $stmt->fetchAll();
            echo json_encode(["services" => $services]);
        }

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['provider_id', 'title', 'price'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required field: $field"]);
                exit();
            }
        }

        $stmt = $pdo->prepare('
            INSERT INTO services
                (provider_id, title, type, icon, price, price_unit,
                 description, long_desc, img_url, features, is_available)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $data['provider_id'],
            $data['title'],
            $data['type']         ?? 'Transport',
            $data['icon']         ?? null,
            $data['price'],
            $data['price_unit']   ?? 'day',
            $data['description']  ?? null,
            $data['long_desc']    ?? null,
            $data['img_url']      ?? null,
            isset($data['features']) ? json_encode($data['features']) : null,
            1,
        ]);

        echo json_encode([
            "message"    => "Service created successfully",
            "service_id" => $pdo->lastInsertId(),
        ]);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing service id"]);
            exit();
        }

        $stmt = $pdo->prepare('
            UPDATE services SET
                title        = ?,
                type         = ?,
                icon         = ?,
                price        = ?,
                price_unit   = ?,
                description  = ?,
                long_desc    = ?,
                img_url      = ?,
                features     = ?,
                is_available = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $data['title'],
            $data['type']        ?? 'Transport',
            $data['icon']        ?? null,
            $data['price'],
            $data['price_unit']  ?? 'day',
            $data['description'] ?? null,
            $data['long_desc']   ?? null,
            $data['img_url']     ?? null,
            isset($data['features']) ? json_encode($data['features']) : null,
            $data['is_available'] ?? 1,
            $data['id'],
        ]);

        echo json_encode(["message" => "Service updated"]);

    } elseif ($method === 'PATCH') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing service id"]);
            exit();
        }

        $stmt = $pdo->prepare('
            UPDATE services
            SET    is_available = NOT is_available
            WHERE  id = ?
        ');
        $stmt->execute([$data['id']]);

        echo json_encode(["message" => "Availability toggled"]);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing service id"]);
            exit();
        }

        $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
        $stmt->execute([$data['id']]);

        echo json_encode(["message" => "Service deleted"]);

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}