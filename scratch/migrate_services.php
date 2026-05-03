<?php
require 'backend/config/db.php';
try {
    $pdo->exec("ALTER TABLE services ADD COLUMN start_date DATE AFTER features");
    echo "Added start_date\n";
} catch (Exception $e) {}
try {
    $pdo->exec("ALTER TABLE services ADD COLUMN end_date DATE AFTER start_date");
    echo "Added end_date\n";
} catch (Exception $e) {}
echo "Done";
?>
