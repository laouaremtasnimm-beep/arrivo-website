<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['user_id'])) {
            $stmt = $pdo->prepare('
                SELECT b.*,
                       COALESCE(p.title, b.item_title) AS package_title,
                       COALESCE(s.title, b.item_title) AS service_title,
                       COALESCE(o.title, b.item_title) AS offer_title,
                       b.item_title AS destination_name,
                       p.img_url AS package_img,
                       s.img_url AS service_img,
                       o.discount_pct AS discount,
                       o.description AS offer_description,
                       o.start_date AS offer_start,
                       o.end_date AS offer_end
                FROM   bookings b
                LEFT   JOIN packages       p ON b.package_id = p.id
                LEFT   JOIN services       s ON b.service_id = s.id
                LEFT   JOIN special_offers o ON b.offer_id   = o.id
                WHERE  b.user_id = ?
                ORDER  BY b.created_at DESC
            ');
            $stmt->execute([$_GET['user_id']]);

        } elseif (isset($_GET['agency_id'])) {
            $stmt = $pdo->prepare('
                SELECT b.*,
                       COALESCE(p.title, b.item_title) AS package_title,
                       COALESCE(o.title, b.item_title) AS offer_title,
                       p.img_url AS package_img,
                       o.discount_pct AS discount,
                       o.description AS offer_description,
                       o.start_date AS offer_start,
                       o.end_date AS offer_end,
                       u.first_name AS guest_first,
                       u.last_name  AS guest_last
                FROM   bookings b
                LEFT   JOIN packages       p ON b.package_id = p.id
                LEFT   JOIN special_offers o ON b.offer_id   = o.id
                JOIN   users u ON b.user_id = u.id
                WHERE  (p.agency_id = ? OR o.agency_id = ?)
                ORDER  BY b.created_at DESC
            ');
            $stmt->execute([$_GET['agency_id'], $_GET['agency_id']]);

        } elseif (isset($_GET['provider_id'])) {
            $stmt = $pdo->prepare('
                SELECT b.*,
                       COALESCE(s.title, b.item_title) AS service_title,
                       COALESCE(o.title, b.item_title) AS offer_title,
                       o.discount_pct AS discount,
                       o.description AS offer_description,
                       o.start_date AS offer_start,
                       o.end_date AS offer_end,
                       u.first_name AS guest_first,
                       u.last_name  AS guest_last
                FROM   bookings b
                LEFT   JOIN services       s ON b.service_id = s.id
                LEFT   JOIN special_offers o ON b.offer_id   = o.id
                JOIN   users u ON b.user_id = u.id
                WHERE  (s.provider_id = ? OR o.agency_id = ?)
                ORDER  BY b.created_at DESC
            ');
            $stmt->execute([$_GET['provider_id'], $_GET['provider_id']]);

        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id, agency_id, or provider_id parameter"]);
            exit();
        }

        $bookings = $stmt->fetchAll();
        echo json_encode(["bookings" => $bookings]);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['user_id']) || !isset($data['check_in']) || !isset($data['booking_type'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required booking fields."]);
            exit();
        }

        $booking_type = $data['booking_type'];
        $offer_id     = !empty($data['offer_id'])     ? (int)$data['offer_id']     : null;
        $package_id   = !empty($data['package_id'])   ? (int)$data['package_id']   : null;
        $service_id   = !empty($data['service_id'])   ? (int)$data['service_id']   : null;
        $total_price  = $data['total_price'] ?? 0.00;

        // ── Bundle-discount guard ─────────────────────────────────────────
        // For collab offer bookings, always recompute price server-side.
        // The frontend price is ignored — the DB is the source of truth.
        if ($booking_type === 'offer' && $offer_id) {
            $offerStmt = $pdo->prepare('
                SELECT o.discount_pct,
                       o.source,
                       COALESCE(p.price, 0) AS package_price,
                       COALESCE(s.price, 0) AS service_price
                FROM   special_offers o
                LEFT   JOIN offer_packages op ON op.offer_id = o.id
                LEFT   JOIN packages       p  ON p.id = op.package_id
                LEFT   JOIN services       s  ON s.id = o.service_id
                WHERE  o.id = ?
                AND    o.is_active = 1
                LIMIT  1
            ');
            $offerStmt->execute([$offer_id]);
            $offerRow = $offerStmt->fetch(PDO::FETCH_ASSOC);

            if (!$offerRow) {
                http_response_code(404);
                echo json_encode(["error" => "Offer not found or inactive."]);
                exit();
            }

            if ($offerRow['source'] === 'collab') {
                $base        = ($offerRow['package_price'] + $offerRow['service_price']) ?: $total_price;
                $total_price = round($base * (1 - $offerRow['discount_pct'] / 100), 2);
            }
        }

        // ── Duplicate booking guard ───────────────────────────────────────
        $checkStmt = $pdo->prepare('
            SELECT id FROM bookings
            WHERE  user_id      = ?
            AND    booking_type = ?
            AND    status      != "cancelled"
            AND (
                (package_id IS NOT NULL AND package_id = ?) OR
                (service_id IS NOT NULL AND service_id = ?) OR
                (offer_id   IS NOT NULL AND offer_id   = ?)
            )
            LIMIT 1
        ');
        $checkStmt->execute([
            $data['user_id'],
            $booking_type,
            $package_id ?? -1,
            $service_id ?? -1,
            $offer_id   ?? -1,
        ]);
        if ($checkStmt->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "You have already booked this item."]);
            exit();
        }

        // ── Insert ────────────────────────────────────────────────────────
        $stmt = $pdo->prepare('
            INSERT INTO bookings
                (user_id, package_id, service_id, destination_id, offer_id, item_title,
                 booking_type, check_in, check_out, guests, total_price, notes, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $data['user_id'],
            $package_id,
            $service_id,
            $data['destination_id'] ?? null,
            $offer_id,
            $data['title']          ?? null,
            $booking_type,
            $data['check_in'],
            $data['check_out']      ?? null,
            $data['guests']         ?? 1,
            $total_price,
            $data['notes']          ?? null,
            'pending',
        ]);

        $bookingId = $pdo->lastInsertId();

        // ── Resolve owner for notification ────────────────────────────────
        $ownerId   = null;
        $ownerRole = null;

        if (!empty($package_id)) {
            $s = $pdo->prepare('SELECT agency_id FROM packages WHERE id = ?');
            $s->execute([$package_id]);
            $ownerId   = $s->fetchColumn();
            $ownerRole = 'agency';
        } elseif (!empty($offer_id)) {
            $s = $pdo->prepare('
                SELECT o.agency_id, u.role
                FROM   special_offers o
                JOIN   users u ON o.agency_id = u.id
                WHERE  o.id = ?
            ');
            $s->execute([$offer_id]);
            $row       = $s->fetch();
            $ownerId   = $row['agency_id'] ?? null;
            $ownerRole = $row['role']      ?? null;
        } elseif (!empty($service_id)) {
            $s = $pdo->prepare('SELECT provider_id FROM services WHERE id = ?');
            $s->execute([$service_id]);
            $ownerId   = $s->fetchColumn();
            $ownerRole = 'provider';
        }

        echo json_encode([
            "message"    => "Booking created successfully",
            "booking_id" => $bookingId,
            "owner_id"   => $ownerId ? (int)$ownerId : null,
            "owner_role" => $ownerRole,
        ]);

    } elseif ($method === 'PATCH') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing id or status"]);
            exit();
        }

        $allowed = ['pending', 'confirmed', 'cancelled', 'completed'];
        if (!in_array($data['status'], $allowed)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid status value"]);
            exit();
        }

        $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $stmt->execute([$data['status'], $data['id']]);

        echo json_encode(["message" => "Booking updated"]);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing booking id"]);
            exit();
        }

        $s = $pdo->prepare('SELECT package_id, service_id, offer_id, booking_type FROM bookings WHERE id = ?');
        $s->execute([$data['id']]);
        $b = $s->fetch();

        $ownerId   = null;
        $ownerRole = null;
        if ($b) {
            if ($b['package_id']) {
                $s2 = $pdo->prepare('SELECT agency_id FROM packages WHERE id = ?');
                $s2->execute([$b['package_id']]);
                $ownerId   = $s2->fetchColumn();
                $ownerRole = 'agency';
            } elseif ($b['service_id']) {
                $s2 = $pdo->prepare('SELECT provider_id FROM services WHERE id = ?');
                $s2->execute([$b['service_id']]);
                $ownerId   = $s2->fetchColumn();
                $ownerRole = 'provider';
            } elseif ($b['offer_id']) {
                $s2 = $pdo->prepare('
                    SELECT o.agency_id, u.role
                    FROM   special_offers o
                    JOIN   users u ON o.agency_id = u.id
                    WHERE  o.id = ?
                ');
                $s2->execute([$b['offer_id']]);
                $row       = $s2->fetch();
                $ownerId   = $row['agency_id'] ?? null;
                $ownerRole = $row['role']      ?? null;
            }
        }

        $stmt = $pdo->prepare('UPDATE bookings SET status = "cancelled" WHERE id = ?');
        $stmt->execute([$data['id']]);

        echo json_encode([
            "message"    => "Booking marked as cancelled",
            "owner_id"   => $ownerId ? (int)$ownerId : null,
            "owner_role" => $ownerRole,
        ]);

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Internal error: " . $e->getMessage()]);
}