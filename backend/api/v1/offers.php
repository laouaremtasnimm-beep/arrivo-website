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

function offerServiceIds(PDO $pdo, array $offer): array {
    $ids = [];

    if (!empty($offer['collab_id'])) {
        $collabStmt = $pdo->prepare('SELECT service_id, service_ids FROM collaborations WHERE id = ?');
        $collabStmt->execute([(int)$offer['collab_id']]);
        $collab = $collabStmt->fetch(PDO::FETCH_ASSOC);
        if ($collab) {
            if (!empty($collab['service_ids'])) {
                $decoded = json_decode($collab['service_ids'], true);
                if (is_array($decoded)) $ids = array_merge($ids, $decoded);
            }
            if (!empty($collab['service_id'])) $ids[] = (int)$collab['service_id'];
        }
    }

    if (!empty($offer['service_id'])) $ids[] = (int)$offer['service_id'];

    return array_values(array_unique(array_filter(array_map('intval', $ids))));
}

function attachOfferContent(PDO $pdo, array &$offer): void {
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
    $pkgStmt->execute([(int)$offer['id']]);
    $packages = $pkgStmt->fetchAll(PDO::FETCH_ASSOC);
    $offer['packages'] = $packages;
    $offer['packageIds'] = array_map('intval', array_column($packages, 'id'));

    if (!empty($packages)) {
        $pkg = $packages[0];
        $offer['package_id'] = $offer['package_id'] ?? (int)$pkg['id'];
        $offer['package_title'] = $pkg['title'] ?? null;
        $offer['package_type'] = $pkg['type'] ?? null;
        $offer['package_destination'] = $pkg['destination'] ?? null;
        $offer['package_img_url'] = $pkg['img_url'] ?? null;
        $offer['package_price'] = $pkg['price'] ?? null;
        $offer['price'] = $offer['price'] ?? $pkg['price'] ?? 0;
    }

    $offer['services'] = [];
    $serviceIds = offerServiceIds($pdo, $offer);
    if (!empty($serviceIds)) {
        $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
        $order = implode(',', $serviceIds);
        $svcStmt = $pdo->prepare("
            SELECT s.*, u.company_name AS provider_name
            FROM   services s
            LEFT JOIN users u ON u.id = s.provider_id
            WHERE  s.id IN ($placeholders)
            ORDER BY FIELD(s.id, $order)
        ");
        $svcStmt->execute($serviceIds);
        $services = $svcStmt->fetchAll(PDO::FETCH_ASSOC);
        $offer['services'] = $services;
        $offer['service_ids'] = array_map('intval', array_column($services, 'id'));

        if (!empty($services)) {
            $svc = $services[0];
            $offer['service_id'] = $offer['service_id'] ?? (int)$svc['id'];
            $offer['service_title'] = $svc['title'] ?? null;
            $offer['service_type'] = $svc['type'] ?? null;
            $offer['service_img_url'] = $svc['img_url'] ?? null;
            $offer['provider_id'] = !empty($offer['provider_id']) ? (int)$offer['provider_id'] : (int)$svc['provider_id'];
            $offer['provider_name'] = $svc['provider_name'] ?? null;
            $offer['partnerName'] = $svc['provider_name'] ?? null;
        }
    }

    $image = $offer['img_url'] ?? $offer['package_img_url'] ?? $offer['service_img_url'] ?? null;
    $offer['img_url'] = $image;
    $offer['img'] = $image;
}

function attachOffersContent(PDO $pdo, array &$offers): void {
    foreach ($offers as &$offer) {
        attachOfferContent($pdo, $offer);
    }
    unset($offer);
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

            attachOfferContent($pdo, $offer);

            echo json_encode(['offer' => $offer]);

        } elseif (isset($_GET['agency_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM special_offers WHERE agency_id = ? ORDER BY created_at DESC');
            $stmt->execute([$_GET['agency_id']]);
            $offers = $stmt->fetchAll();
            attachOffersContent($pdo, $offers);
            echo json_encode(['offers' => $offers]);

        } else {
            /* Public listing — all active offers */
            $stmt = $pdo->query('SELECT * FROM special_offers WHERE is_active = 1 ORDER BY created_at DESC');
            $offers = $stmt->fetchAll();
            attachOffersContent($pdo, $offers);
            echo json_encode(['offers' => $offers]);
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
                     description, type, offer_type, is_active, source, includes, service_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)
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
                !empty($data['serviceId']) ? (int)$data['serviceId'] : null,
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
                    includes     = ?,
                    service_id   = ?
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
                !empty($data['serviceId']) ? (int)$data['serviceId'] : null,
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
