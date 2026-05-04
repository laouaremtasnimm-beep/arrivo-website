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
                       COUNT(r.id) AS review_count,
                       MAX(o.id) AS active_offer_id,
                       MAX(o.discount_pct) AS active_offer_discount,
                       MAX(o.start_date) AS active_offer_start,
                       MAX(o.end_date) AS active_offer_end,
                       MAX(o.title) AS active_offer_title
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

        } elseif (isset($_GET['provider_id'])) {
            $stmt = $pdo->prepare('
                SELECT s.*, u.company_name AS provider_name,
                       COUNT(DISTINCT b.id) AS booking_count,
                       IFNULL(AVG(r.rating), 0) AS rating,
                       MAX(o.id) AS active_offer_id,
                       MAX(o.discount_pct) AS active_offer_discount,
                       MAX(o.start_date) AS active_offer_start,
                       MAX(o.end_date) AS active_offer_end,
                       MAX(o.title) AS active_offer_title
                FROM   services s
                LEFT   JOIN users u ON u.id = s.provider_id
                LEFT   JOIN bookings b ON b.service_id = s.id
                LEFT   JOIN reviews r ON r.service_id = s.id
                LEFT   JOIN special_offers o ON o.service_id = s.id AND o.is_active = 1
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
                       COUNT(r.id) AS review_count,
                       MAX(o.id) AS active_offer_id,
                       MAX(o.discount_pct) AS active_offer_discount,
                       MAX(o.start_date) AS active_offer_start,
                       MAX(o.end_date) AS active_offer_end,
                       MAX(o.title) AS active_offer_title
                FROM services s
                LEFT JOIN users u ON u.id = s.provider_id
                LEFT JOIN reviews r ON r.service_id = s.id
                LEFT JOIN special_offers o ON o.service_id = s.id AND o.is_active = 1
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
                 description, long_desc, img_url, features, is_available,
                 start_date, end_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $data['start_date']   ?? null,
            $data['end_date']     ?? null,
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
                is_available = ?,
                start_date   = ?,
                end_date     = ?
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
            $data['start_date']   ?? null,
            $data['end_date']     ?? null,
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

        $svcId = (int) $data['id'];

        $pdo->beginTransaction();
        try {
            /* ── Cascade: handle linked offers ──────────────────────────────
               Find offers that point to this service (either directly via service_id
               or via the offer_packages join table).
            ─────────────────────────────────────────────────────────────── */
            
            // 1. Direct links
            $offersStmt = $pdo->prepare('SELECT id FROM special_offers WHERE service_id = ?');
            $offersStmt->execute([$svcId]);
            $directOfferIds = $offersStmt->fetchAll(PDO::FETCH_COLUMN);

            // 2. Joined links (if any)
            $joinedStmt = $pdo->prepare('SELECT offer_id FROM offer_packages WHERE package_id = ?');
            $joinedStmt->execute([$svcId]);
            $joinedOfferIds = $joinedStmt->fetchAll(PDO::FETCH_COLUMN);

            $allLinkedOffers = array_unique(array_merge($directOfferIds, $joinedOfferIds));
            $deletedOfferIds = [];

            foreach ($allLinkedOffers as $offerId) {
                /* For each offer, check if removing this service makes it "empty" */
                
                // If it was a joined link, remove it first so counts are accurate
                if (in_array($offerId, $joinedOfferIds)) {
                    $pdo->prepare('DELETE FROM offer_packages WHERE offer_id = ? AND package_id = ?')
                        ->execute([$offerId, $svcId]);
                }

                /* Count remaining packages/items in join table */
                $countStmt = $pdo->prepare('SELECT COUNT(*) FROM offer_packages WHERE offer_id = ?');
                $countStmt->execute([$offerId]);
                $remainingItems = (int) $countStmt->fetchColumn();

                /* Check if it still has a (different) service_id */
                $svcCheck = $pdo->prepare('SELECT service_id FROM special_offers WHERE id = ?');
                $svcCheck->execute([$offerId]);
                $currentSvcId = $svcCheck->fetchColumn();

                // If the current service_id is the one we are deleting, it's effectively gone
                $hasOtherService = (!empty($currentSvcId) && (int)$currentSvcId !== $svcId);

                if ($remainingItems === 0 && !$hasOtherService) {
                    /* Offer is now empty → delete it */
                    $pdo->prepare('DELETE FROM special_offers WHERE id = ?')->execute([$offerId]);
                    $deletedOfferIds[] = $offerId;
                } elseif ((int)$currentSvcId === $svcId) {
                    /* Just remove the direct service link */
                    $pdo->prepare('UPDATE special_offers SET service_id = NULL WHERE id = ?')
                        ->execute([$offerId]);
                }
            }

            /* ── Delete the service itself ─────────────────────────────── */
            $pdo->prepare('DELETE FROM services WHERE id = ?')->execute([$svcId]);

            $pdo->commit();
            echo json_encode([
                "message"           => "Service deleted",
                "deleted_offer_ids" => $deletedOfferIds
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