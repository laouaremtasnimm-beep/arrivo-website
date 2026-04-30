<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Map DB row → frontend shape
function rowToOffer($row) {
    $fmt = function($d) { return $d ? date('M j', strtotime($d)) : ''; };
    return [
        'offerID'     => (int)$row['id'],
        'owner_id'    => (int)$row['agency_id'],
        'title'       => $row['title'],
        'discount'    => (int)$row['discount_pct'],
        'startDate'   => $fmt($row['start_date']),
        'endDate'     => $fmt($row['end_date']),
        'description' => $row['description'] ?? '',
        'type'        => $row['type']   ?? 'General',
        'source'      => $row['source'] ?? 'manual',
        'active'      => (bool)$row['is_active'],
    ];
}

// Parse free-text date ("Jun 1", "2025-07-01") → MySQL DATE or null
function parseDate($s) {
    if (!$s) return null;
    $ts = strtotime($s);
    return $ts ? date('Y-m-d', $ts) : null;
}

try {
    if ($method === 'GET') {
        if (isset($_GET['owner_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM special_offers WHERE agency_id = ? ORDER BY created_at DESC');
            $stmt->execute([$_GET['owner_id']]);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM special_offers WHERE is_active = 1 ORDER BY created_at DESC');
            $stmt->execute();
        }
        echo json_encode(['offers' => array_map('rowToOffer', $stmt->fetchAll())]);

    } elseif ($method === 'POST') {
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['owner_id']) || empty($d['title']) || !isset($d['discount'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit();
        }
        $stmt = $pdo->prepare(
            'INSERT INTO special_offers (agency_id, title, discount_pct, start_date, end_date, description, type, source, is_active)
             VALUES (?,?,?,?,?,?,?,?,1)'
        );
        $stmt->execute([
            $d['owner_id'],
            $d['title'],
            (int)$d['discount'],
            parseDate($d['startDate'] ?? ''),
            parseDate($d['endDate']   ?? ''),
            $d['description'] ?? '',
            $d['type']        ?? 'General',
            $d['source']      ?? 'manual',
        ]);
        echo json_encode(['message' => 'Offer created', 'offer_id' => (int)$pdo->lastInsertId()]);

    } elseif ($method === 'PUT') {
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['id'])) {
            http_response_code(400); echo json_encode(['error' => 'Missing offer id']); exit();
        }
        $stmt = $pdo->prepare(
            'UPDATE special_offers SET title=?, discount_pct=?, start_date=?, end_date=?, description=?, type=?, is_active=? WHERE id=?'
        );
        $stmt->execute([
            $d['title'],
            (int)$d['discount'],
            parseDate($d['startDate'] ?? ''),
            parseDate($d['endDate']   ?? ''),
            $d['description'] ?? '',
            $d['type']        ?? 'General',
            isset($d['active']) ? (int)$d['active'] : 1,
            $d['id'],
        ]);
        echo json_encode(['message' => 'Offer updated']);

    } elseif ($method === 'DELETE') {
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['id'])) {
            http_response_code(400); echo json_encode(['error' => 'Missing offer id']); exit();
        }
        $stmt = $pdo->prepare('DELETE FROM special_offers WHERE id = ?');
        $stmt->execute([$d['id']]);
        echo json_encode(['message' => 'Offer deleted']);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
