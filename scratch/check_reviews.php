<?php
require_once __DIR__ . '/../backend/config/db.php';
try {
    $stmt = $pdo->query("DESC reviews");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
