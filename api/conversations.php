<?php
/**
 * OUTSINC Platform - API: Conversations List
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Not authenticated'], 401);
}

try {
    $pdo = getDatabaseConnection();
    
    // Get all conversations for current user
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN m.sender_id = ? THEN m.recipient_id 
                ELSE m.sender_id 
            END as other_user_id,
            MAX(m.id) as last_message_id,
            MAX(m.created_at) as last_message_time
        FROM messages m
        WHERE (m.sender_id = ? OR m.recipient_id = ?)
            AND m.is_deleted_by_sender = FALSE 
            AND m.is_deleted_by_recipient = FALSE
        GROUP BY other_user_id
        ORDER BY last_message_time DESC
        LIMIT 50
    ");
    $stmt->execute([getCurrentUserId(), getCurrentUserId(), getCurrentUserId()]);
    $conversations = $stmt->fetchAll();
    
    $result = [];
    foreach ($conversations as $conv) {
        // Get other user info
        $stmt = $pdo->prepare("
            SELECT id, username, first_name, last_name 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$conv['other_user_id']]);
        $otherUser = $stmt->fetch();
        
        if (!$otherUser) continue;
        
        // Get last message
        $stmt = $pdo->prepare("
            SELECT message_text, created_at 
            FROM messages 
            WHERE id = ?
        ");
        $stmt->execute([$conv['last_message_id']]);
        $lastMessage = $stmt->fetch();
        
        // Count unread messages
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM messages 
            WHERE sender_id = ? AND recipient_id = ? AND is_read = FALSE
        ");
        $stmt->execute([$conv['other_user_id'], getCurrentUserId()]);
        $unreadCount = $stmt->fetchColumn();
        
        $result[] = [
            'id' => $conv['other_user_id'],
            'name' => $otherUser['first_name'] . ' ' . $otherUser['last_name'],
            'initials' => strtoupper(substr($otherUser['first_name'], 0, 1) . substr($otherUser['last_name'], 0, 1)),
            'last_message' => $lastMessage ? substr($lastMessage['message_text'], 0, 50) : '',
            'last_message_time' => $lastMessage['created_at'] ?? null,
            'unread_count' => (int)$unreadCount
        ];
    }
    
    jsonResponse(['conversations' => $result]);
} catch (Exception $e) {
    error_log("Conversations error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Failed to load conversations'], 500);
}
?>
