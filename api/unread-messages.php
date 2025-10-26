<?php
/**
 * OUTSINC Platform - API: Unread Messages Count
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['count' => 0]);
}

try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM messages 
        WHERE recipient_id = ? AND is_read = FALSE AND is_deleted_by_recipient = FALSE
    ");
    $stmt->execute([getCurrentUserId()]);
    $result = $stmt->fetch();
    
    jsonResponse(['count' => (int)$result['count']]);
} catch (Exception $e) {
    error_log("Unread messages error: " . $e->getMessage());
    jsonResponse(['count' => 0]);
}
?>
