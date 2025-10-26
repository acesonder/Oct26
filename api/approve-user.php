<?php
/**
 * OUTSINC Platform - API: Approve/Reject User
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Not authenticated'], 401);
}

if (!hasRole('admin')) {
    jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = (int)($data['user_id'] ?? 0);
$action = $data['action'] ?? '';

if ($userId <= 0) {
    jsonResponse(['success' => false, 'error' => 'Invalid user ID'], 400);
}

if (!in_array($action, ['approve', 'reject'])) {
    jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
}

try {
    $pdo = getDatabaseConnection();
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$userId]);
        
        logAudit('user_approved', 'users', $userId);
        
        // TODO: Send approval email to user
        
        jsonResponse(['success' => true, 'message' => 'User approved successfully']);
    } else {
        // Reject user - mark as inactive or delete
        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$userId]);
        
        logAudit('user_rejected', 'users', $userId);
        
        // TODO: Send rejection email to user
        
        jsonResponse(['success' => true, 'message' => 'User rejected']);
    }
} catch (Exception $e) {
    error_log("User approval error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Failed to process request'], 500);
}
?>
