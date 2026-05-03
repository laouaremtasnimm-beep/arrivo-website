<?php
/**
 * One-time cleanup: reset offer_only = 0 for packages that were incorrectly
 * flagged by the old offers.php logic (which set offer_only=1 on ALL linked
 * packages, including pre-existing ones).
 *
 * Safe to run multiple times. Packages that are genuinely offer-only
 * (no start_date / end_date / standard data) keep their flag.
 *
 * Visit: /arrivo-website/backend/api/v1/fix_offer_only.php
 */
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

try {
    // Find all packages flagged offer_only=1 that have a start_date AND end_date
    // (i.e., they are real packages, not newly-created offer-only stubs).
    // Reset them so they appear in the public listing again.
    $stmt = $pdo->prepare('
        UPDATE packages
        SET offer_only = 0
        WHERE offer_only = 1
          AND start_date IS NOT NULL
          AND end_date   IS NOT NULL
    ');
    $stmt->execute();
    $affected = $stmt->rowCount();

    echo json_encode([
        'success'  => true,
        'message'  => "Reset offer_only=0 for $affected packages that had start/end dates.",
        'affected' => $affected,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
