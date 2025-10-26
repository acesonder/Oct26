<?php
/**
 * OUTSINC Platform - API: Mood Check-in
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Not authenticated'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$moodLevel = $data['mood_level'] ?? null;
$moodType = $data['mood_type'] ?? null;
$notes = $data['notes'] ?? null;

if ($moodLevel === null || $moodLevel < 1 || $moodLevel > 5) {
    jsonResponse(['success' => false, 'error' => 'Invalid mood level'], 400);
}

try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("
        INSERT INTO mood_checkins (user_id, mood_level, mood_type, notes)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([getCurrentUserId(), $moodLevel, $moodType, $notes]);
    
    logAudit('mood_checkin', 'mood_checkins', $pdo->lastInsertId());
    
    jsonResponse(['success' => true, 'message' => 'Mood check-in saved']);
} catch (Exception $e) {
    error_log("Mood check-in error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Failed to save mood check-in'], 500);
}
?>
