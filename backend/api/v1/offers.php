<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

/* ─────────────────────────────────────────────────────────────
   Helper: mark a list of package IDs as offer_only = $flag
   Called when an offer is created, updated, or deleted.
───────────────────────────────────────────────────────────── */
function setPackagesOfferOnly(PDO $pdo, array $packageIds, int $flag): void {
    if (empty($packageIds)) return;
    $placeholders = implode(',', array_fill(0, count($packageIds), '?'));
    $stmt = $pdo->prepare(
        "UPDATE packages SET offer_only = ? WHERE id IN ($placeholders)"
    );
    $stmt->execute(array_merge([$flag], $packageIds));
}

try {
    /* ══════════════════════════════════════════════════════════
       GET  — list or single fetch
    ══════════════════════════════════════════════════════════ */
    if ($method === 'GET') {

        if (isset($_GET['id'])) {
            /* Single offer with its linked packages */
            $stmt = $pdo->prepare('SELECT * FROM special_offers WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            $offer = $stmt->fetch();

            if (!$offer) {
                http_response_code(404);
                echo json_encode(['error' => 'Offer not found']);
                exit();
            }

            /* Fetch linked packages (offer_only ones) */
            $pkgStmt = $pdo->prepare('
                SELECT p.*, u.company_name AS agency_name,
                       IFNULL(AVG(r.rating), 0) AS rating,
                       COUNT(r.id)              AS review_count
                FROM   offer_packages op
                JOIN   packages p ON p.id = op.package_id
                LEFT JOIN users u ON u.id = p.agency_id
                LEFT JOIN reviews r ON r.package_id = p.id
                WHERE  op.offer_id = ?
                GROUP  BY p.id
            ');
            $pkgStmt->execute([$_GET['id']]);
            $offer['packages'] = $pkgStmt->fetchAll();

            echo json_encode(['offer' => $offer]);

        } elseif (isset($_GET['agency_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM special_offers WHERE agency_id = ? ORDER BY created_at DESC');
            $stmt->execute([$_GET['agency_id']]);
            echo json_encode(['offers' => $stmt->fetchAll()]);

        } else {
            /* Public listing — all active offers */
            $stmt = $pdo->query('SELECT * FROM special_offers WHERE is_active = 1 ORDER BY created_at DESC');
            echo json_encode(['offers' => $stmt->fetchAll()]);
        }

    /* ══════════════════════════════════════════════════════════
       POST — create offer
    ══════════════════════════════════════════════════════════ */
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['agency_id', 'title', 'discount'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: $field"]);
                exit();
            }
        }

        $offerType  = $data['offerType'] ?? 'general';
        $packageIds = (isset($data['packageIds']) && is_array($data['packageIds']))
                      ? array_map('intval', $data['packageIds'])
                      : [];

        /* Validate: package offer must have at least one package */
        if ($offerType === 'package' && empty($packageIds)) {
            http_response_code(400);
            echo json_encode(['error' => 'A package offer must include at least one package.']);
            exit();
        }

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('
                INSERT INTO special_offers
                    (agency_id, title, discount_pct, start_date, end_date,
                     description, type, offer_type, is_active, source, includes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)
            ');
            $stmt->execute([
                $data['agency_id'],
                $data['title'],
                $data['discount'],
                !empty($data['startDate']) ? $data['startDate'] : null,
                !empty($data['endDate']) ? $data['endDate'] : null,
                $data['description'],
                $data['type']   ?? 'General',
                $offerType,
                $data['source'] ?? 'manual',
                isset($data['includes']) ? json_encode($data['includes']) : null,
            ]);
            $offerId = (int) $pdo->lastInsertId();

            /* Link packages to this offer.
               IMPORTANT: Only set offer_only=1 on packages that were ALREADY
               flagged as offer_only (i.e. new packages created exclusively for this offer).
               Pre-existing packages must remain visible on the public listing —
               they should just show the offer badge. */
            if ($offerType === 'package' && !empty($packageIds)) {
                $linkStmt = $pdo->prepare(
                    'INSERT IGNORE INTO offer_packages (offer_id, package_id) VALUES (?, ?)'
                );
                foreach ($packageIds as $pkgId) {
                    $linkStmt->execute([$offerId, $pkgId]);
                }

                /* Only flag packages that are truly offer-only (newly created for this offer) */
                if (!empty($packageIds)) {
                    $placeholders = implode(',', array_fill(0, count($packageIds), '?'));
                    $offerOnlyStmt = $pdo->prepare(
                        "SELECT id FROM packages WHERE id IN ($placeholders) AND (offer_only = 1 OR offer_only IS NOT NULL AND offer_only != 0)"
                    );
                    $offerOnlyStmt->execute($packageIds);
                    $alreadyOfferOnly = array_column($offerOnlyStmt->fetchAll(), 'id');
                    // Only existing offer_only packages keep that flag — pre-existing packages are NOT touched
                    // (no setPackagesOfferOnly call here for pre-existing packages)
                }
            }

            $pdo->commit();
            echo json_encode(['message' => 'Offer created successfully', 'offer_id' => $offerId]);

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    /* ══════════════════════════════════════════════════════════
       PUT — update offer
    ══════════════════════════════════════════════════════════ */
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing offer id']);
            exit();
        }

        $offerId    = (int) $data['id'];
        $offerType  = $data['offerType'] ?? 'general';
        $packageIds = (isset($data['packageIds']) && is_array($data['packageIds']))
                      ? array_map('intval', $data['packageIds'])
                      : [];

        $pdo->beginTransaction();

        try {
            /* Fetch old linked packages so we can un-hide any removed ones */
            $oldPkgStmt = $pdo->prepare('SELECT package_id FROM offer_packages WHERE offer_id = ?');
            $oldPkgStmt->execute([$offerId]);
            $oldPackageIds = $oldPkgStmt->fetchAll(PDO::FETCH_COLUMN);

            /* Update offer row */
            $stmt = $pdo->prepare('
                UPDATE special_offers SET
                    title        = ?,
                    discount_pct = ?,
                    start_date   = ?,
                    end_date     = ?,
                    description  = ?,
                    type         = ?,
                    offer_type   = ?,
                    is_active    = ?,
                    includes     = ?
                WHERE id = ?
            ');
            $stmt->execute([
                $data['title'],
                $data['discount'],
                !empty($data['startDate']) ? $data['startDate'] : null,
                !empty($data['endDate']) ? $data['endDate'] : null,
                $data['description'],
                $data['type']     ?? 'General',
                $offerType,
                $data['is_active'] ?? 1,
                isset($data['includes']) ? json_encode($data['includes']) : null,
                $offerId,
            ]);

            /* Reconcile package links */
            $toAdd    = array_diff($packageIds, $oldPackageIds);
            $toRemove = array_diff($oldPackageIds, $packageIds);

            /* Remove old links — only reset offer_only=0 if the package has no other active offers */
            if (!empty($toRemove)) {
                $pl = implode(',', array_fill(0, count($toRemove), '?'));
                $pdo->prepare("DELETE FROM offer_packages WHERE offer_id = ? AND package_id IN ($pl)")
                    ->execute(array_merge([$offerId], $toRemove));
                
                /* Only reset offer_only for packages that truly have no other active offer */
                foreach ($toRemove as $pkgId) {
                    $checkStmt = $pdo->prepare('
                        SELECT COUNT(*) FROM offer_packages op
                        JOIN special_offers o ON o.id = op.offer_id AND o.is_active = 1
                        WHERE op.package_id = ?
                    ');
                    $checkStmt->execute([$pkgId]);
                    $hasOtherOffer = $checkStmt->fetchColumn() > 0;
                    if (!$hasOtherOffer) {
                        setPackagesOfferOnly($pdo, [$pkgId], 0);
                    }
                }
            }

            /* Add new links — do NOT set offer_only=1 on pre-existing packages */
            if (!empty($toAdd)) {
                $linkStmt = $pdo->prepare(
                    'INSERT IGNORE INTO offer_packages (offer_id, package_id) VALUES (?, ?)'
                );
                foreach ($toAdd as $pkgId) {
                    $linkStmt->execute([$offerId, $pkgId]);
                }
                // Note: we deliberately do NOT call setPackagesOfferOnly here
                // Pre-existing packages should remain visible on the listing with an offer badge
            }

            $pdo->commit();

            /* If an img_url is provided and we have linked packages, update them.
               This reflects changes everywhere (home, search, etc.) just like a package edit. */
            if (!empty($data['img_url']) && !empty($packageIds)) {
                $placeholders = implode(',', array_fill(0, count($packageIds), '?'));
                $imgStmt = $pdo->prepare("
                    UPDATE packages 
                    SET    img_url = ? 
                    WHERE  id IN ($placeholders)
                ");
                $imgStmt->execute(array_merge([$data['img_url']], $packageIds));
            }

            echo json_encode(['message' => 'Offer updated']);

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    /* ══════════════════════════════════════════════════════════
       DELETE — remove offer + unhide its packages
    ══════════════════════════════════════════════════════════ */
    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing offer id']);
            exit();
        }

        $offerId = (int) $data['id'];

        $pdo->beginTransaction();

        try {
            /* Get linked packages before deleting */
            $pkgStmt = $pdo->prepare('SELECT package_id FROM offer_packages WHERE offer_id = ?');
            $pkgStmt->execute([$offerId]);
            $linkedIds = $pkgStmt->fetchAll(PDO::FETCH_COLUMN);

            /* Remove link rows */
            $pdo->prepare('DELETE FROM offer_packages WHERE offer_id = ?')->execute([$offerId]);

            /* Unhide packages */
            setPackagesOfferOnly($pdo, $linkedIds, 0);

            /* Delete offer */
            $pdo->prepare('DELETE FROM special_offers WHERE id = ?')->execute([$offerId]);

            $pdo->commit();
            echo json_encode(['message' => 'Offer deleted']);

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}