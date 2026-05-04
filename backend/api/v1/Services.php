<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    /* ══════════════════════════════════════════════════════════
       GET — list or single fetch
    ══════════════════════════════════════════════════════════ */
    if ($method === 'GET') {

        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('
                SELECT s.*, u.company_name AS provider_name,
                       IFNULL(AVG(r.rating), 0) AS rating,
                       COUNT(r.id) AS review_count,
                       o.id           AS active_offer_id,
                       o.discount_pct AS active_offer_discount,
                       o.start_date   AS active_offer_start,
                       o.end_date     AS active_offer_end,
                       o.title        AS active_offer_title,
                       o.source       AS active_offer_source
                FROM services s
                LEFT JOIN users u ON u.id = s.provider_id
                LEFT JOIN reviews r ON r.service_id = s.id
                LEFT JOIN special_offers o ON o.service_id = s.id AND o.is_active = 1
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

        } elseif (isset($_GET['provider_id']) || isset($_GET['user_id'])) {
            $ownerId = $_GET['provider_id'] ?? $_GET['user_id'];
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
            $stmt->execute([$ownerId]);
            $services = $stmt->fetchAll();
            echo json_encode(["services" => $services]);

        } else {
            $status = $_GET['status'] ?? 'active';
            $where  = ($status === 'active') ? 'WHERE s.is_available = 1' : '';

            $stmt = $pdo->query("
                SELECT s.*, u.company_name AS provider_name,
                       IFNULL(AVG(r.rating), 0) AS rating,
                       COUNT(DISTINCT r.id) AS review_count
                FROM services s
                LEFT JOIN users u ON u.id = s.provider_id
                LEFT JOIN reviews r ON r.service_id = s.id
                $where
                GROUP BY s.id
                ORDER BY s.created_at DESC
            ");
            $services = $stmt->fetchAll();
            echo json_encode(["services" => $services]);
        }

    /* ══════════════════════════════════════════════════════════
       POST — create service
    ══════════════════════════════════════════════════════════ */
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
                (provider_id, title, description, type, price, price_unit, icon, img_url, is_available)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ');
        $stmt->execute([
            $data['provider_id'],
            $data['title'],
            $data['description'] ?? '',
            $data['type']        ?? 'Other',
            $data['price'],
            $data['price_unit']  ?? 'trip',
            $data['icon']        ?? '🛎️',
            $data['img_url']     ?? null,
        ]);

        echo json_encode([
            "message"    => "Service created",
            "service_id" => $pdo->lastInsertId(),
        ]);

    /* ══════════════════════════════════════════════════════════
       PUT — update service
    ══════════════════════════════════════════════════════════ */
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing service id"]);
            exit();
        }

        $stmt = $pdo->prepare('
            UPDATE services
            SET    title = ?, description = ?, type = ?, price = ?,
                   price_unit = ?, icon = ?, img_url = ?
            WHERE  id = ?
        ');
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['type'],
            $data['price'],
            $data['price_unit'],
            $data['icon'],
            $data['img_url'],
            $data['id'],
        ]);

        echo json_encode(["message" => "Service updated"]);

    /* ══════════════════════════════════════════════════════════
       PATCH — toggle availability
    ══════════════════════════════════════════════════════════ */
    } elseif ($method === 'PATCH') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing id"]);
            exit();
        }

        $stmt = $pdo->prepare('UPDATE services SET is_available = 1 - is_available WHERE id = ?');
        $stmt->execute([$data['id']]);
        echo json_encode(["message" => "Availability toggled"]);

    /* ══════════════════════════════════════════════════════════
       DELETE — delete service with cascading offer cleanup
    ══════════════════════════════════════════════════════════ */
    } elseif ($method === 'DELETE') {
        $data  = json_decode(file_get_contents('php://input'), true);
        $svcId = $data['id'] ?? null;

        if (!$svcId) {
            http_response_code(400);
            echo json_encode(["error" => "Missing service id"]);
            exit();
        }

        try {
            $pdo->beginTransaction();

            $linkedOffersStmt = $pdo->prepare('SELECT id FROM special_offers WHERE service_id = ?');
            $linkedOffersStmt->execute([$svcId]);
            $linkedOfferIds  = $linkedOffersStmt->fetchAll(PDO::FETCH_COLUMN);
            $deletedOfferIds = [];

            foreach ($linkedOfferIds as $offerId) {
                $pdo->prepare('DELETE FROM offer_packages WHERE offer_id = ?')->execute([$offerId]);
                $pdo->prepare('DELETE FROM special_offers WHERE id = ?')->execute([$offerId]);
                $deletedOfferIds[] = $offerId;
            }

            $pdo->prepare('DELETE FROM services WHERE id = ?')->execute([$svcId]);

            $pdo->commit();
            echo json_encode([
                "message"           => "Service deleted",
                "deleted_offer_ids" => $deletedOfferIds,
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}