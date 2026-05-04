<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {

        if (isset($_GET['role'])) {
            // ── List users by role ─────────────────────────────────────────
            // Used by CollabFormModal to populate the provider picker.
            // Returns only the fields the frontend needs; password_hash excluded.
            $allowed = ['tourist', 'agency', 'provider', 'admin'];
            $role    = $_GET['role'];

            if (!in_array($role, $allowed, true)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid role. Allowed: ' . implode(', ', $allowed)]);
                exit();
            }

            $stmt = $pdo->prepare('
                SELECT id, first_name, last_name, email, role, company_name, avatar_url
                FROM   users
                WHERE  role = ?
                ORDER  BY company_name ASC, first_name ASC
            ');
            $stmt->execute([$role]);
            $rows = $stmt->fetchAll();

            // Cast id to int for JS
            foreach ($rows as &$row) {
                $row['id'] = (int) $row['id'];
            }
            unset($row);

            echo json_encode(['users' => $rows]);

        } elseif (isset($_GET['email'])) {
            // ── Lookup by email (used by ComposeModal) ─────────────────────
            $stmt = $pdo->prepare('
                SELECT id, first_name, last_name, email, role, company_name
                FROM   users
                WHERE  email = ?
                LIMIT  1
            ');
            $stmt->execute([trim($_GET['email'])]);
            $user = $stmt->fetch();

            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                exit();
            }

            echo json_encode([
                'user' => [
                    'id'      => (int) $user['id'],
                    'name'    => $user['first_name'] . ' ' . $user['last_name'],
                    'email'   => $user['email'],
                    'role'    => $user['role'],
                    'company' => $user['company_name'],
                ],
            ]);

        } elseif (isset($_GET['id'])) {
            // ── Lookup by id ───────────────────────────────────────────────
            $stmt = $pdo->prepare('
                SELECT id, first_name, last_name, email, role, company_name
                FROM   users
                WHERE  id = ?
                LIMIT  1
            ');
            $stmt->execute([(int) $_GET['id']]);
            $user = $stmt->fetch();

            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                exit();
            }

            echo json_encode([
                'user' => [
                    'id'      => (int) $user['id'],
                    'name'    => $user['first_name'] . ' ' . $user['last_name'],
                    'email'   => $user['email'],
                    'role'    => $user['role'],
                    'company' => $user['company_name'],
                ],
            ]);

        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Provide role, email, or id as a query parameter']);
        }

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}