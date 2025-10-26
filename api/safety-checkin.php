<?php
/**
 * OUTSINC Platform - API: Safety Check-in
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Not authenticated'], 401);
}

if (!hasRole('worker')) {
    jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$status = $data['status'] ?? 'safe';
$location = $data['location'] ?? null;
$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;
$notes = $data['notes'] ?? null;

if (!in_array($status, ['safe', 'need_assistance', 'emergency'])) {
    jsonResponse(['success' => false, 'error' => 'Invalid status'], 400);
}

try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("
        INSERT INTO safety_checkins (worker_id, location, latitude, longitude, status, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([getCurrentUserId(), $location, $latitude, $longitude, $status, $notes]);
    
    logAudit('safety_checkin', 'safety_checkins', $pdo->lastInsertId(), null, ['status' => $status]);
    
    // If emergency, send alerts (would integrate with notification system)
    if ($status === 'emergency') {
        // TODO: Send emergency alerts to admin/supervisors
        error_log("EMERGENCY SAFETY CHECKIN: Worker ID " . getCurrentUserId());
    }
    
    jsonResponse(['success' => true, 'message' => 'Safety check-in recorded']);
} catch (Exception $e) {
    error_log("Safety check-in error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Failed to record check-in'], 500);
}
?>
