<?php
require 'c:/xampp/htdocs/arrivo-website/backend/config/db.php';
$stmt = $pdo->query('DESCRIBE bookings');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $pdo->query('DESCRIBE special_offers');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
