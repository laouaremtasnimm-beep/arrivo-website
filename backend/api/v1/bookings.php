<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // -------------------------------------------------------------------
        // ?user_id=X          → tourist: their own bookings
        // ?agency_id=X        → agency:  bookings for their packages
        // ?provider_id=X      → provider: bookings for their services
        // -------------------------------------------------------------------

        if (isset($_GET['user_id'])) {
            $stmt = $pdo->prepare('
                SELECT b.*,
                       COALESCE(d.name, b.item_title)  AS destination_name,
                       COALESCE(p.title, b.item_title) AS package_title,
                       COALESCE(s.title, b.item_title) AS service_title,
                       COALESCE(o.title, b.item_title) AS offer_title
                FROM   bookings b
                LEFT   JOIN destinations d ON b.destination_id = d.id
                LEFT   JOIN packages     p ON b.package_id     = p.id
                LEFT   JOIN services     s ON b.service_id     = s.id
                LEFT   JOIN special_offers o ON b.offer_id     = o.id
                WHERE  b.user_id = ?
                ORDER  BY b.created_at DESC
            ');
            $stmt->execute([$_GET['user_id']]);

        } elseif (isset($_GET['agency_id'])) {
            $stmt = $pdo->prepare('
                SELECT b.*,
                       COALESCE(p.title, b.item_title) AS package_title,
                       COALESCE(o.title, b.item_title) AS offer_title,
                       u.first_name    AS guest_first,
                       u.last_name     AS guest_last
                FROM   bookings b
                LEFT   JOIN packages p ON b.package_id = p.id
                LEFT   JOIN special_offers o ON b.offer_id = o.id
                JOIN   users    u ON b.user_id    = u.id
                WHERE  p.agency_id = ? OR o.agency_id = ?
                ORDER  BY b.created_at DESC
            ');
            $stmt->execute([$_GET['agency_id'], $_GET['agency_id']]);

        } elseif (isset($_GET['provider_id'])) {
            $stmt = $pdo->prepare('
                SELECT b.*,
                       COALESCE(s.title, b.item_title) AS service_title,
                       u.first_name    AS guest_first,
                       u.last_name     AS guest_last
                FROM   bookings b
                JOIN   services s ON b.service_id = s.id
                JOIN   users    u ON b.user_id    = u.id
                WHERE  s.provider_id = ?
                ORDER  BY b.created_at DESC
            ');
            $stmt->execute([$_GET['provider_id']]);

        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id, agency_id, or provider_id parameter"]);
            exit();
        }

        $bookings = $stmt->fetchAll();
        echo json_encode(["bookings" => $bookings]);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        error_log("Incoming booking POST data: " . print_r($data, true));

        if (!isset($data['user_id']) || !isset($data['check_in']) || !isset($data['booking_type'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required booking fields."]);
            exit();
        }

        $stmt = $pdo->prepare('
            INSERT INTO bookings
                (user_id, package_id, service_id, destination_id, offer_id, item_title,
                 booking_type, check_in, check_out, guests, total_price, notes, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $data['user_id'],
            $data['package_id']     ?? null,
            $data['service_id']     ?? null,
            $data['destination_id'] ?? null,
            $data['offer_id']       ?? null,
            $data['title']          ?? null,
            $data['booking_type'],
            $data['check_in'],
            $data['check_out']      ?? null,
            $data['guests']         ?? 1,
            $data['total_price']    ?? 0.00,
            $data['notes']          ?? null,
            'confirmed',
        ]);

        echo json_encode([
            "message"    => "Booking created successfully",
            "booking_id" => $pdo->lastInsertId(),
        ]);

    } elseif ($method === 'PATCH') {
        // Update status: confirm or cancel
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

        $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
        $stmt->execute([$data['id']]);

        echo json_encode(["message" => "Booking deleted successfully"]);

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    $payloadInfo = isset($data) ? " Payload: " . json_encode($data) : "";
    echo json_encode(["error" => "Database error: " . $e->getMessage() . $payloadInfo]);
}