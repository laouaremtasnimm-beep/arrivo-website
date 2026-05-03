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
                       COUNT(r.id) AS review_count,
                       MAX(o.id) AS active_offer_id,
                       MAX(o.discount_pct) AS active_offer_discount,
                       MAX(o.start_date) AS active_offer_start,
                       MAX(o.end_date) AS active_offer_end,
                       MAX(o.title) AS active_offer_title
                FROM packages p
                LEFT JOIN users u ON u.id = p.agency_id
                LEFT JOIN reviews r ON r.package_id = p.id
                LEFT JOIN offer_packages op ON op.package_id = p.id
                LEFT JOIN special_offers o ON o.id = op.offer_id AND o.is_active = 1
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
                       IFNULL(AVG(r.rating), 0) AS rating,
                       MAX(o.id) AS active_offer_id,
                       MAX(o.discount_pct) AS active_offer_discount,
                       MAX(o.start_date) AS active_offer_start,
                       MAX(o.end_date) AS active_offer_end,
                       MAX(o.title) AS active_offer_title
                FROM   packages p
                LEFT   JOIN users u ON u.id = p.agency_id
                LEFT   JOIN bookings b ON b.package_id = p.id
                LEFT   JOIN reviews r ON r.package_id = p.id
                LEFT   JOIN offer_packages op ON op.package_id = p.id
                LEFT   JOIN special_offers o ON o.id = op.offer_id AND o.is_active = 1
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
                       COUNT(DISTINCT r.id) AS review_count,
                       MAX(o.id) AS active_offer_id,
                       MAX(o.discount_pct) AS active_offer_discount,
                       MAX(o.start_date) AS active_offer_start,
                       MAX(o.end_date) AS active_offer_end,
                       MAX(o.title) AS active_offer_title
                FROM packages p
                LEFT JOIN users u ON u.id = p.agency_id
                LEFT JOIN reviews r ON r.package_id = p.id
                LEFT JOIN offer_packages op ON op.package_id = p.id
                LEFT JOIN special_offers o ON o.id = op.offer_id AND o.is_active = 1
                WHERE p.is_active = 1
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

        $pkgId = (int) $data['id'];

        $pdo->beginTransaction();
        try {
            /* ── Cascade: handle linked offers ──────────────────────────────
               For each offer that includes this package:
               - If this package is the ONLY one in the offer → delete the offer entirely
               - If the offer has multiple packages → just unlink this one
            ─────────────────────────────────────────────────────────────── */
            $linkedOffersStmt = $pdo->prepare('SELECT offer_id FROM offer_packages WHERE package_id = ?');
            $linkedOffersStmt->execute([$pkgId]);
            $linkedOfferIds = $linkedOffersStmt->fetchAll(PDO::FETCH_COLUMN);
            $deletedOfferIds = [];

            foreach ($linkedOfferIds as $offerId) {
                /* Count how many packages this offer has in total */
                $countStmt = $pdo->prepare('SELECT COUNT(*) FROM offer_packages WHERE offer_id = ?');
                $countStmt->execute([$offerId]);
                $packageCount = (int) $countStmt->fetchColumn();

                if ($packageCount <= 1) {
                    /* This was the only package → delete the whole offer */
                    $pdo->prepare('DELETE FROM offer_packages WHERE offer_id = ?')->execute([$offerId]);
                    $pdo->prepare('DELETE FROM special_offers WHERE id = ?')->execute([$offerId]);
                    $deletedOfferIds[] = $offerId;
                } else {
                    /* Offer still has other packages → just unlink this one */
                    $pdo->prepare('DELETE FROM offer_packages WHERE offer_id = ? AND package_id = ?')
                        ->execute([$offerId, $pkgId]);
                }
            }

            /* ── Delete the package itself ─────────────────────────────── */
            $pdo->prepare('DELETE FROM packages WHERE id = ?')->execute([$pkgId]);

            $pdo->commit();
            echo json_encode([
                "message"          => "Package deleted",
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