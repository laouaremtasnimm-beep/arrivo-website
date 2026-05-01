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
                     WHERE title LIKE ? OR description LIKE ? OR type LIKE ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$q%", "%$q%", "%$q%"]);
        } else {
            $stmt = $pdo->query("SELECT id, title, description, price, img_url, type, duration_days, rating, review_count FROM packages");
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
                     WHERE title LIKE ? OR description LIKE ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$q%", "%$q%"]);
        } else {
            $stmt = $pdo->query("SELECT id, title, description, price, img_url, rating, review_count FROM services");
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
        if ($q !== null) {
            // FIX: Changed 'discount' to 'discount_pct' to match your SQL schema
            $sql  = "SELECT id, title, description, discount_pct 
                     FROM special_offers
                     WHERE title LIKE ? OR description LIKE ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$q%", "%$q%"]);
        } else {
            $stmt = $pdo->query("SELECT id, title, description, discount_pct FROM special_offers");
        }
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = [
                'id'       => $row['id'],
                'category' => 'offer',
                'title'    => $row['title'],
                'desc'     => $row['description'],
                'price'    => 0,
                'discount' => $row['discount_pct'], // Mapped to the frontend 'discount' key
                'img'      => null,
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