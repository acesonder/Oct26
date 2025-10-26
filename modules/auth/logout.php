<?php
/**
 * OUTSINC Platform - Logout
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

if (isLoggedIn()) {
    // Log audit
    logAudit('logout', 'users', getCurrentUserId());
    
    // Update session log
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("
            UPDATE session_logs 
            SET logout_time = NOW() 
            WHERE user_id = ? AND session_id = ? AND logout_time IS NULL
        ");
        $stmt->execute([getCurrentUserId(), session_id()]);
    } catch (Exception $e) {
        error_log("Logout session update error: " . $e->getMessage());
    }
}

// Destroy session
session_destroy();
session_start();
session_regenerate_id(true);

// Redirect to login
redirect('/modules/auth/login.php');
?>
