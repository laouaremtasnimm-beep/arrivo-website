<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('
                SELECT p.*, u.company_name AS agency_name,
                       IFNULL(AVG(r.rating), 0) AS rating,
                       COUNT(r.id) AS review_count
                FROM packages p
                LEFT JOIN users u ON u.id = p.agency_id
                LEFT JOIN reviews r ON r.package_id = p.id
                WHERE p.id = ?
                GROUP BY p.id
            ');
            $stmt->execute([$_GET['id']]);
            $package = $stmt->fetch();

            if (!$package) {
                http_response_code(404);
                echo json_encode(["error" => "Package not found"]);
                exit();
            }
            echo json_encode(["package" => $package]);

        } elseif (isset($_GET['agency_id'])) {
            $stmt = $pdo->prepare('
                SELECT p.*, u.company_name AS agency_name,
                       COUNT(DISTINCT b.id) AS booking_count,
                       IFNULL(AVG(r.rating), 0) AS rating
                FROM   packages p
                LEFT   JOIN users u ON u.id = p.agency_id
                LEFT   JOIN bookings b ON b.package_id = p.id
                LEFT   JOIN reviews r ON r.package_id = p.id
                WHERE  p.agency_id = ?
                GROUP  BY p.id
                ORDER  BY p.created_at DESC
            ');
            $stmt->execute([$_GET['agency_id']]);
            $packages = $stmt->fetchAll();
            echo json_encode(["packages" => $packages]);

        } else {
            $stmt = $pdo->query('
                SELECT p.*, u.company_name AS agency_name,
                       IFNULL(AVG(r.rating), 0) AS rating,
                       COUNT(r.id) AS review_count
                FROM packages p
                LEFT JOIN users u ON u.id = p.agency_id
                LEFT JOIN reviews r ON r.package_id = p.id
                WHERE p.is_active = 1 AND (p.offer_only = 0 OR p.offer_only IS NULL)
                GROUP BY p.id
                ORDER BY p.created_at DESC
            ');
            $packages = $stmt->fetchAll();
            echo json_encode(["packages" => $packages]);
        }

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['agency_id', 'title', 'destination', 'price'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required field: $field"]);
                exit();
            }
        }

        $stmt = $pdo->prepare('
            INSERT INTO packages
                (agency_id, title, destination, type, duration_days, price,
                 spots_available, group_size_max, description, long_desc,
                 img_url, includes, excludes, itinerary, offer_only, start_date, end_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $data['agency_id'],
            $data['title'],
            $data['destination'],
            $data['type']            ?? 'Adventure',
            $data['duration_days']   ?? 1,
            $data['price'],
            $data['spots_available'] ?? 10,
            $data['group_size_max']  ?? null,
            $data['description']     ?? null,
            $data['long_desc']       ?? null,
            $data['img_url']         ?? null,
            isset($data['includes'])  ? json_encode($data['includes'])  : null,
            isset($data['excludes'])  ? json_encode($data['excludes'])  : null,
            isset($data['itinerary']) ? json_encode($data['itinerary']) : null,
            $data['offer_only']      ?? 0,
            $data['start_date']      ?? null,
            $data['end_date']        ?? null,
        ]);

        echo json_encode([
            "message"    => "Package created successfully",
            "package_id" => $pdo->lastInsertId(),
        ]);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing package id"]);
            exit();
        }

        $stmt = $pdo->prepare('
            UPDATE packages SET
                title           = ?,
                destination     = ?,
                type            = ?,
                duration_days   = ?,
                price           = ?,
                spots_available = ?,
                group_size_max  = ?,
                description     = ?,
                long_desc       = ?,
                img_url         = ?,
                includes        = ?,
                excludes        = ?,
                itinerary       = ?,
                is_active       = ?,
                offer_only      = ?,
                start_date      = ?,
                end_date        = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $data['title'],
            $data['destination'],
            $data['type']            ?? 'Adventure',
            $data['duration_days']   ?? 1,
            $data['price'],
            $data['spots_available'] ?? 10,
            $data['group_size_max']  ?? null,
            $data['description']     ?? null,
            $data['long_desc']       ?? null,
            $data['img_url']         ?? null,
            isset($data['includes'])  ? json_encode($data['includes'])  : null,
            isset($data['excludes'])  ? json_encode($data['excludes'])  : null,
            isset($data['itinerary']) ? json_encode($data['itinerary']) : null,
            $data['is_active']        ?? 1,
            $data['offer_only']       ?? 0,
            $data['start_date']       ?? null,
            $data['end_date']         ?? null,
            $data['id'],
        ]);

        echo json_encode(["message" => "Package updated"]);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing package id"]);
            exit();
        }

        $stmt = $pdo->prepare('DELETE FROM packages WHERE id = ?');
        $stmt->execute([$data['id']]);

        echo json_encode(["message" => "Package deleted"]);

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}