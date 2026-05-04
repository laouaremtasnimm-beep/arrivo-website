<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

function ensureCollabServiceIdsColumn(PDO $pdo): void {
    $stmt = $pdo->query("SHOW COLUMNS FROM collaborations LIKE 'service_ids'");
    if (!$stmt->fetch()) {
        $pdo->exec('ALTER TABLE collaborations ADD COLUMN service_ids JSON NULL AFTER service_id');
    }
}

/* ── FIX: MariaDB does not allow ? placeholders in SHOW statements.
         Use a safe string-interpolated query instead (table/column names
         are never user-supplied here, so this is safe). ── */
function tableHasColumn(PDO $pdo, string $table, string $column): bool {
    // Sanitise: strip anything that isn't a letter, digit, or underscore
    $table  = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    $stmt   = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    return (bool) $stmt->fetch();
}

function collabServiceIds(array $collab): array {
    $ids = [];
    if (!empty($collab['service_ids'])) {
        $decoded = is_string($collab['service_ids'])
            ? json_decode($collab['service_ids'], true)
            : $collab['service_ids'];
        if (is_array($decoded)) {
            $ids = array_map('intval', $decoded);
        }
    }
    if (empty($ids) && !empty($collab['service_id'])) {
        $ids = [(int)$collab['service_id']];
    }
    return array_values(array_unique(array_filter($ids)));
}

function attachCollabServices(PDO $pdo, array &$collab): void {
    $ids = collabServiceIds($collab);
    $collab['service_ids'] = $ids;
    $collab['services'] = [];
    if (empty($ids)) return;

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $order = implode(',', $ids);
    $stmt = $pdo->prepare("
        SELECT id, provider_id, title, type, icon, img_url, price, price_unit
        FROM services
        WHERE id IN ($placeholders)
        ORDER BY FIELD(id, $order)
    ");
    $stmt->execute($ids);
    $collab['services'] = array_map(function ($svc) {
        $svc['id'] = (int)$svc['id'];
        $svc['provider_id'] = (int)$svc['provider_id'];
        return $svc;
    }, $stmt->fetchAll());
}

/* ─────────────────────────────────────────────────────────────────────────────
   Helper: create a special_offer row from an accepted collaboration.
   Returns the new offer's ID.
───────────────────────────────────────────────────────────────────────────── */
function createOfferFromCollab(PDO $pdo, array $collab): int {
    $agencyStmt = $pdo->prepare('SELECT agency_id FROM packages WHERE id = ?');
    $agencyStmt->execute([(int)$collab['package_id']]);
    $agencyId = (int)$agencyStmt->fetchColumn();

    $providerStmt = $pdo->prepare('SELECT provider_id FROM services WHERE id = ?');
    $providerStmt->execute([(int)$collab['service_id']]);
    $providerId = (int)$providerStmt->fetchColumn();

    $columns = [
        'agency_id',
        'service_id',
        'collab_id',
        'title',
        'discount_pct',
        'start_date',
        'end_date',
        'type',
        'offer_type',
        'source',
        'description',
        'is_active',
    ];
    $values = [
        $agencyId,
        $collab['service_id'],
        $collab['id'],
        $collab['title'],
        $collab['discount_pct'],
        $collab['start_date'],
        $collab['end_date'],
        $collab['offer_type'] ?? 'Bundle',
        $collab['offer_type'] ?? 'Bundle',
        'collab',
        $collab['message'] ?? null,
        1,
    ];

    if ($providerId && tableHasColumn($pdo, 'special_offers', 'provider_id')) {
        $columns[] = 'provider_id';
        $values[]  = $providerId;
    }

    $placeholders = implode(',', array_fill(0, count($columns), '?'));
    $stmt = $pdo->prepare('
        INSERT INTO special_offers (' . implode(',', $columns) . ')
        VALUES (' . $placeholders . ')
    ');
    $stmt->execute($values);
    $offerId = (int) $pdo->lastInsertId();

    /* Link the package chosen at request-time */
    if (!empty($collab['package_id'])) {
        $pdo->prepare('
            INSERT IGNORE INTO offer_packages (offer_id, package_id)
            VALUES (?, ?)
        ')->execute([$offerId, $collab['package_id']]);
    }

    return $offerId;
}

/* ─────────────────────────────────────────────────────────────────────────────
   Helper: full collab row with all joined data (service, package, users).
───────────────────────────────────────────────────────────────────────────── */
function fetchCollabById(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare('
        SELECT
            c.*,
            ui.first_name   AS initiator_first_name,
            ui.last_name    AS initiator_last_name,
            ui.company_name AS initiator_company,
            ui.avatar_url   AS initiator_avatar,
            ui.role         AS initiator_role,
            up.first_name   AS partner_first_name,
            up.last_name    AS partner_last_name,
            up.company_name AS partner_company,
            up.avatar_url   AS partner_avatar,
            up.role         AS partner_role,
            s.title         AS service_title,
            s.type          AS service_type,
            s.icon          AS service_icon,
            s.img_url       AS service_img_url,
            s.price         AS service_price,
            s.price_unit    AS service_price_unit,
            s.provider_id   AS service_provider_id,
            p.title         AS package_title,
            p.destination   AS package_destination,
            p.type          AS package_type,
            p.img_url       AS package_img_url,
            p.price         AS package_price,
            p.agency_id     AS package_agency_id,
            p.duration_days AS package_duration_days
        FROM   collaborations c
        JOIN   users    ui ON ui.id = c.initiator_id
        JOIN   users    up ON up.id = c.partner_id
        JOIN   services s  ON s.id  = c.service_id
        JOIN   packages p  ON p.id  = c.package_id
        WHERE  c.id = ?
    ');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) return null;

    $row['id']           = (int) $row['id'];
    $row['initiator_id'] = (int) $row['initiator_id'];
    $row['partner_id']   = (int) $row['partner_id'];
    $row['service_id']   = (int) $row['service_id'];
    $row['package_id']   = (int) $row['package_id'];
    $row['discount_pct'] = (int) $row['discount_pct'];
    $row['offer_id']     = $row['offer_id'] ? (int) $row['offer_id'] : null;

    if (!empty($row['counter_data'])) {
        $row['counter_data'] = json_decode($row['counter_data'], true);
    }
    $row['service_provider_id'] = (int) $row['service_provider_id'];
    $row['package_agency_id']   = (int) $row['package_agency_id'];
    attachCollabServices($pdo, $row);
    return $row;
}

/* ═════════════════════════════════════════════════════════════════════════════
   ROUTING
═════════════════════════════════════════════════════════════════════════════ */
try {
    ensureCollabServiceIdsColumn($pdo);

    /* ────────────────────────────────────────────────────────────────────────
       GET
    ──────────────────────────────────────────────────────────────────────── */
    if ($method === 'GET') {

        if (isset($_GET['id'])) {
            $collab = fetchCollabById($pdo, (int) $_GET['id']);
            if (!$collab) {
                http_response_code(404);
                echo json_encode(['error' => 'Collaboration not found']);
                exit();
            }
            echo json_encode(['collaboration' => $collab]);

        } elseif (isset($_GET['user_id'])) {
            $userId = (int) $_GET['user_id'];

            $stmt = $pdo->prepare('
                SELECT
                    c.*,
                    ui.first_name   AS initiator_first_name,
                    ui.last_name    AS initiator_last_name,
                    ui.company_name AS initiator_company,
                    ui.avatar_url   AS initiator_avatar,
                    ui.role         AS initiator_role,
                    up.first_name   AS partner_first_name,
                    up.last_name    AS partner_last_name,
                    up.company_name AS partner_company,
                    up.avatar_url   AS partner_avatar,
                    up.role         AS partner_role,
                    s.title         AS service_title,
                    s.type          AS service_type,
                    s.icon          AS service_icon,
                    s.img_url       AS service_img_url,
                    s.price         AS service_price,
                    s.price_unit    AS service_price_unit,
                    s.provider_id   AS service_provider_id,
                    p.title         AS package_title,
                    p.destination   AS package_destination,
                    p.type          AS package_type,
                    p.img_url       AS package_img_url,
                    p.price         AS package_price,
                    p.agency_id     AS package_agency_id,
                    p.duration_days AS package_duration_days
                FROM   collaborations c
                JOIN   users    ui ON ui.id = c.initiator_id
                JOIN   users    up ON up.id = c.partner_id
                JOIN   services s  ON s.id  = c.service_id
                JOIN   packages p  ON p.id  = c.package_id
                WHERE  c.initiator_id = ? OR c.partner_id = ?
                ORDER  BY c.updated_at DESC
            ');
            $stmt->execute([$userId, $userId]);
            $rows = $stmt->fetchAll();

            foreach ($rows as &$row) {
                $row['id']           = (int) $row['id'];
                $row['initiator_id'] = (int) $row['initiator_id'];
                $row['partner_id']   = (int) $row['partner_id'];
                $row['service_id']   = (int) $row['service_id'];
                $row['package_id']   = (int) $row['package_id'];
                $row['discount_pct'] = (int) $row['discount_pct'];
                $row['offer_id']     = $row['offer_id'] ? (int) $row['offer_id'] : null;
                if (!empty($row['counter_data'])) {
                    $row['counter_data'] = json_decode($row['counter_data'], true);
                }
                $row['service_provider_id'] = (int) $row['service_provider_id'];
                $row['package_agency_id']   = (int) $row['package_agency_id'];
                attachCollabServices($pdo, $row);
            }
            unset($row);

            echo json_encode(['collaborations' => $rows]);

        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Provide user_id or id query parameter']);
        }

    /* ────────────────────────────────────────────────────────────────────────
       POST — send new collab request
    ──────────────────────────────────────────────────────────────────────── */
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['initiator_id', 'partner_id', 'package_id', 'title', 'discount_pct'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: $field"]);
                exit();
            }
        }

        $serviceIds = [];
        if (isset($data['service_ids']) && is_array($data['service_ids'])) {
            $serviceIds = array_map('intval', $data['service_ids']);
        } elseif (isset($data['service_id']) && $data['service_id'] !== '') {
            $serviceIds = [(int)$data['service_id']];
        }
        $serviceIds = array_values(array_unique(array_filter($serviceIds)));
        if (empty($serviceIds)) {
            http_response_code(400);
            echo json_encode(['error' => 'Select at least one service']);
            exit();
        }

        $roleStmt = $pdo->prepare('SELECT id, role FROM users WHERE id IN (?, ?)');
        $roleStmt->execute([(int)$data['initiator_id'], (int)$data['partner_id']]);
        $roleMap = [];
        foreach ($roleStmt->fetchAll() as $u) $roleMap[$u['id']] = $u['role'];

        $initiatorRole = $roleMap[$data['initiator_id']] ?? '';
        $partnerRole   = $roleMap[$data['partner_id']]   ?? '';

        if (!in_array($initiatorRole, ['agency', 'provider'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Collaboration initiator must be an agency or provider']);
            exit();
        }
        if (!in_array($partnerRole, ['agency', 'provider'], true) || $partnerRole === $initiatorRole) {
            http_response_code(403);
            echo json_encode(['error' => 'Collaboration must be between an agency and a provider']);
            exit();
        }

        $providerId      = $initiatorRole === 'provider' ? (int)$data['initiator_id'] : (int)$data['partner_id'];
        $svcPlaceholders = implode(',', array_fill(0, count($serviceIds), '?'));
        $svcCheck        = $pdo->prepare("
            SELECT COUNT(*) FROM services
            WHERE provider_id = ? AND id IN ($svcPlaceholders)
        ");
        $svcCheck->execute(array_merge([$providerId], $serviceIds));
        if ((int)$svcCheck->fetchColumn() !== count($serviceIds)) {
            http_response_code(400);
            echo json_encode(['error' => 'One or more services do not belong to the specified provider']);
            exit();
        }

        $pkgCheck = $pdo->prepare('SELECT agency_id FROM packages WHERE id = ?');
        $pkgCheck->execute([(int)$data['package_id']]);
        $pkg      = $pkgCheck->fetch();
        $agencyId = $initiatorRole === 'agency' ? (int)$data['initiator_id'] : (int)$data['partner_id'];
        if (!$pkg || (int)$pkg['agency_id'] !== $agencyId) {
            http_response_code(400);
            echo json_encode(['error' => 'Package does not belong to the specified agency']);
            exit();
        }

        $disc = (int)$data['discount_pct'];
        if ($disc < 1 || $disc > 99) {
            http_response_code(400);
            echo json_encode(['error' => 'Discount must be between 1 and 99']);
            exit();
        }

        $stmt = $pdo->prepare('
            INSERT INTO collaborations
                (initiator_id, partner_id, service_id, service_ids, package_id,
                 title, discount_pct, offer_type, start_date, end_date, message, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending")
        ');
        $stmt->execute([
            (int)   $data['initiator_id'],
            (int)   $data['partner_id'],
                    $serviceIds[0],
                    json_encode($serviceIds),
            (int)   $data['package_id'],
                    $data['title'],
            $disc,
                    $data['offer_type']  ?? 'Bundle',
            !empty($data['start_date']) ? $data['start_date'] : null,
            !empty($data['end_date'])   ? $data['end_date']   : null,
                    $data['message']     ?? null,
        ]);
        $collabId = (int) $pdo->lastInsertId();

        $collab = fetchCollabById($pdo, $collabId);
        http_response_code(201);
        echo json_encode([
            'message'       => 'Collaboration request sent',
            'collaboration' => $collab,
        ]);

    /* ────────────────────────────────────────────────────────────────────────
       PUT — accept / decline / counter
    ──────────────────────────────────────────────────────────────────────── */
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'], $data['action'], $data['actor_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id, action, or actor_id']);
            exit();
        }

        $collabId = (int) $data['id'];
        $action   = $data['action'];
        $actorId  = (int) $data['actor_id'];

        $fetchStmt = $pdo->prepare('SELECT * FROM collaborations WHERE id = ?');
        $fetchStmt->execute([$collabId]);
        $collab = $fetchStmt->fetch();

        if (!$collab) {
            http_response_code(404);
            echo json_encode(['error' => 'Collaboration not found']);
            exit();
        }

        $isInitiator = (int)$collab['initiator_id'] === $actorId;
        $isPartner   = (int)$collab['partner_id']   === $actorId;

        if (!$isInitiator && !$isPartner) {
            http_response_code(403);
            echo json_encode(['error' => 'You are not part of this collaboration']);
            exit();
        }

        if (!in_array($collab['status'], ['pending', 'countered'], true)) {
            http_response_code(409);
            echo json_encode(['error' => 'This collaboration is already ' . $collab['status']]);
            exit();
        }

        /* ── accept ─────────────────────────────────────────────────────── */
        if ($action === 'accept') {
            if ($collab['status'] === 'pending' && !$isPartner) {
                http_response_code(403);
                echo json_encode(['error' => 'Only the provider can accept a pending request']);
                exit();
            }
            if ($collab['status'] === 'countered' && !$isInitiator) {
                http_response_code(403);
                echo json_encode(['error' => 'Only the initiating agency can accept a counter-proposal']);
                exit();
            }

            /* Merge counter terms before creating the offer */
            if ($collab['status'] === 'countered' && !empty($collab['counter_data'])) {
                $counter = is_string($collab['counter_data'])
                    ? json_decode($collab['counter_data'], true)
                    : $collab['counter_data'];

                $pdo->prepare('
                    UPDATE collaborations
                    SET discount_pct = ?,
                        start_date   = ?,
                        end_date     = ?
                    WHERE id = ?
                ')->execute([
                    $counter['discount_pct'] ?? $collab['discount_pct'],
                    $counter['start_date']   ?? $collab['start_date'],
                    $counter['end_date']     ?? $collab['end_date'],
                    $collabId,
                ]);

                $fetchStmt->execute([$collabId]);
                $collab = $fetchStmt->fetch();
            }

            $pdo->beginTransaction();
            try {
                $offerId = createOfferFromCollab($pdo, $collab);

                $pdo->prepare('
                    UPDATE collaborations
                    SET status     = "accepted",
                        offer_id   = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ')->execute([$offerId, $collabId]);

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

            $result = fetchCollabById($pdo, $collabId);
            echo json_encode([
                'message'       => 'Collaboration accepted — offer is now live',
                'collaboration' => $result,
            ]);

        /* ── decline ────────────────────────────────────────────────────── */
        } elseif ($action === 'decline') {
            $pdo->prepare('
                UPDATE collaborations
                SET status = "declined", updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ')->execute([$collabId]);

            $result = fetchCollabById($pdo, $collabId);
            echo json_encode([
                'message'       => 'Collaboration declined',
                'collaboration' => $result,
            ]);

        /* ── counter ────────────────────────────────────────────────────── */
        } elseif ($action === 'counter') {
            if (!$isPartner) {
                http_response_code(403);
                echo json_encode(['error' => 'Only the provider can send a counter-proposal']);
                exit();
            }

            $counterData = [];
            if (isset($data['counter_discount_pct'])) {
                $val = (int) $data['counter_discount_pct'];
                if ($val < 1 || $val > 99) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Discount must be between 1 and 99']);
                    exit();
                }
                $counterData['discount_pct'] = $val;
            }
            if (!empty($data['counter_start_date'])) $counterData['start_date'] = $data['counter_start_date'];
            if (!empty($data['counter_end_date']))   $counterData['end_date']   = $data['counter_end_date'];
            if (!empty($data['counter_message']))    $counterData['message']    = $data['counter_message'];

            if (empty($counterData)) {
                http_response_code(400);
                echo json_encode(['error' => 'Counter-proposal must adjust at least one field']);
                exit();
            }

            $pdo->prepare('
                UPDATE collaborations
                SET status       = "countered",
                    counter_data = ?,
                    updated_at   = CURRENT_TIMESTAMP
                WHERE id = ?
            ')->execute([json_encode($counterData), $collabId]);

            $result = fetchCollabById($pdo, $collabId);
            echo json_encode([
                'message'       => 'Counter-proposal sent',
                'collaboration' => $result,
            ]);

        } else {
            http_response_code(400);
            echo json_encode(['error' => "Unknown action: $action. Use accept, decline, or counter"]);
        }

    /* ────────────────────────────────────────────────────────────────────────
       DELETE — withdraw a pending request (initiator only)
    ──────────────────────────────────────────────────────────────────────── */
    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'], $data['actor_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id or actor_id']);
            exit();
        }

        $collabId = (int) $data['id'];
        $actorId  = (int) $data['actor_id'];

        $fetchStmt = $pdo->prepare('SELECT * FROM collaborations WHERE id = ?');
        $fetchStmt->execute([$collabId]);
        $collab = $fetchStmt->fetch();

        if (!$collab) {
            http_response_code(404);
            echo json_encode(['error' => 'Collaboration not found']);
            exit();
        }

        if ((int)$collab['initiator_id'] !== $actorId) {
            http_response_code(403);
            echo json_encode(['error' => 'Only the initiating agency can withdraw a request']);
            exit();
        }

        if ($collab['status'] !== 'pending') {
            http_response_code(409);
            echo json_encode(['error' => 'Only pending requests can be withdrawn']);
            exit();
        }

        $pdo->prepare('DELETE FROM collaborations WHERE id = ?')->execute([$collabId]);
        echo json_encode(['message' => 'Collaboration request withdrawn']);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}