<?php

// Show PHP errors in response during development
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$q        = (isset($_GET['q']) && trim($_GET['q']) !== '') ? trim($_GET['q']) : null;
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$results  = [];

try {

    // ── 1. DESTINATIONS ────────────────────────────────────────────────
    if ($category === 'all' || $category === 'dest') {
        if ($q !== null) {
            $sql  = "SELECT id, name, description, price_from, img_url, rating, review_count
                     FROM destinations
                     WHERE name LIKE ? OR country LIKE ? OR region LIKE ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$q%", "%$q%", "%$q%"]);
        } else {
            $stmt = $pdo->query("SELECT id, name, description, price_from, img_url, rating, review_count FROM destinations");
        }
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = [
                'id'          => $row['id'],
                'category'    => 'dest',
                'title'       => $row['name'],
                'desc'        => $row['description'],
                'price'       => $row['price_from'],
                'img'         => $row['img_url'],
                'rating'      => $row['rating'],
                'reviewCount' => $row['review_count'],
            ];
        }
    }

    // ── 2. PACKAGES ────────────────────────────────────────────────────
    if ($category === 'all' || $category === 'package') {
        if ($q !== null) {
            $sql  = "SELECT id, title, description, price, img_url, type, duration_days, rating, review_count
                     FROM packages
                     WHERE is_active = 1 AND (title LIKE ? OR description LIKE ? OR type LIKE ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$q%", "%$q%", "%$q%"]);
        } else {
            $stmt = $pdo->query("SELECT id, title, description, price, img_url, type, duration_days, rating, review_count FROM packages WHERE is_active = 1");
        }
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = [
                'id'          => $row['id'],
                'category'    => 'package',
                'title'       => $row['title'],
                'desc'        => $row['description'],
                'price'       => $row['price'],
                'img'         => $row['img_url'],
                'type'        => $row['type'],
                'duration'    => $row['duration_days'],
                'rating'      => $row['rating'],
                'reviewCount' => $row['review_count'],
            ];
        }
    }

    // ── 3. SERVICES ────────────────────────────────────────────────────
    if ($category === 'all' || $category === 'service') {
        if ($q !== null) {
            $sql  = "SELECT id, title, description, price, img_url, rating, review_count
                     FROM services
                     WHERE is_available = 1 AND (title LIKE ? OR description LIKE ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$q%", "%$q%"]);
        } else {
            $stmt = $pdo->query("SELECT id, title, description, price, img_url, rating, review_count FROM services WHERE is_available = 1");
        }
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = [
                'id'          => $row['id'],
                'category'    => 'service',
                'title'       => $row['title'],
                'desc'        => $row['description'],
                'price'       => $row['price'],
                'img'         => $row['img_url'],
                'rating'      => $row['rating'],
                'reviewCount' => $row['review_count'],
            ];
        }
    }

    // ── 4. OFFERS ──────────────────────────────────────────────────────
    if ($category === 'all' || $category === 'offer') {
        $providerColumnStmt = $pdo->query("SHOW COLUMNS FROM special_offers LIKE 'provider_id'");
        $providerExpr = $providerColumnStmt->fetch() ? 'COALESCE(o.provider_id, s.provider_id)' : 's.provider_id';
        $offerSelect = "
            SELECT o.id, o.title, o.description, o.discount_pct, o.start_date, o.end_date,
                   o.type, o.offer_type, o.source, o.agency_id, o.service_id,
                   $providerExpr AS provider_id,
                   COALESCE(p.price, s.price, 0) AS price,
                   COALESCE(p.img_url, s.img_url) AS img,
                   u.company_name AS provider_name
            FROM special_offers o
            LEFT JOIN offer_packages op ON op.offer_id = o.id
            LEFT JOIN packages p ON p.id = op.package_id
            LEFT JOIN services s ON s.id = o.service_id
            LEFT JOIN users u ON u.id = $providerExpr
            WHERE o.is_active = 1
        ";
        if ($q !== null) {
            $sql  = $offerSelect . " AND (o.title LIKE ? OR o.description LIKE ? OR p.title LIKE ? OR s.title LIKE ?)
                     GROUP BY o.id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$q%", "%$q%", "%$q%", "%$q%"]);
        } else {
            $stmt = $pdo->query($offerSelect . " GROUP BY o.id");
        }
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = [
                'id'          => $row['id'],
                'category'    => 'offer',
                'title'       => $row['title'],
                'desc'        => $row['description'],
                'price'       => $row['price'],
                'discount'    => $row['discount_pct'],
                'img'         => $row['img'],
                'type'        => $row['type'] ?: $row['offer_type'],
                'source'      => $row['source'],
                'startDate'   => $row['start_date'],
                'endDate'     => $row['end_date'],
                'agency_id'   => $row['agency_id'],
                'provider_id' => $row['provider_id'],
                'partnerName' => $row['provider_name'],
            ];
        }
    }

    echo json_encode(['results' => $results]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => "Database Error: " . $e->getMessage(),
        'results' => [],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => $e->getMessage(),
        'results' => [],
    ]);
}
