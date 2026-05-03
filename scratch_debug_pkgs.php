<?php
require_once 'backend/config/db.php';
$stmt = $pdo->query("SELECT id, agency_id, title FROM packages");
$pkgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($pkgs);
