<?php
/**
 * OUTSINC Platform - API: Unread Notifications Count
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['count' => 0]);
}

try {
    $pdo = getDatabaseConnection();
    // For now, return 0 until notifications table is implemented
    jsonResponse(['count' => 0]);
} catch (Exception $e) {
    error_log("Unread notifications error: " . $e->getMessage());
    jsonResponse(['count' => 0]);
}
?>
