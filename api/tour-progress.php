<?php
/**
 * OUTSINC Platform - Tour Progress API
 * Saves and retrieves user tour progress
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Require authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = getCurrentUserId();
$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDatabaseConnection();

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['reset']) && $data['reset']) {
            // Reset tour progress
            $stmt = $pdo->prepare("
                UPDATE user_tour_progress 
                SET tour_completed = FALSE, tour_skipped = FALSE, current_step = 0, completed_at = NULL, updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);

            echo json_encode(['success' => true, 'message' => 'Tour reset successfully']);
            exit;
        }

        // Check if user has a tour progress record
        $stmt = $pdo->prepare("SELECT id FROM user_tour_progress WHERE user_id = ?");
        $stmt->execute([$userId]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Update existing record
            $updates = [];
            $params = [];

            if (isset($data['current_step'])) {
                $updates[] = "current_step = ?";
                $params[] = $data['current_step'];
            }

            if (isset($data['tour_completed']) && $data['tour_completed']) {
                $updates[] = "tour_completed = TRUE";
                $updates[] = "completed_at = NOW()";
            }

            if (isset($data['tour_skipped']) && $data['tour_skipped']) {
                $updates[] = "tour_skipped = TRUE";
                $updates[] = "completed_at = NOW()";
            }

            if (!empty($updates)) {
                $params[] = $userId;
                $sql = "UPDATE user_tour_progress SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
        } else {
            // Create new record
            $tourCompleted = isset($data['tour_completed']) && $data['tour_completed'] ? 1 : 0;
            $tourSkipped = isset($data['tour_skipped']) && $data['tour_skipped'] ? 1 : 0;
            $currentStep = $data['current_step'] ?? 0;
            
            $stmt = $pdo->prepare("
                INSERT INTO user_tour_progress (user_id, tour_completed, tour_skipped, current_step, completed_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            $completedAt = ($tourCompleted || $tourSkipped) ? date('Y-m-d H:i:s') : null;
            $stmt->execute([$userId, $tourCompleted, $tourSkipped, $currentStep, $completedAt]);
        }

        echo json_encode(['success' => true]);
    } elseif ($method === 'GET') {
        // Get tour progress
        $stmt = $pdo->prepare("SELECT * FROM user_tour_progress WHERE user_id = ?");
        $stmt->execute([$userId]);
        $progress = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$progress) {
            // No progress record, tour not started
            echo json_encode([
                'success' => true,
                'tour_completed' => false,
                'tour_skipped' => false,
                'current_step' => 0
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'tour_completed' => (bool)$progress['tour_completed'],
                'tour_skipped' => (bool)$progress['tour_skipped'],
                'current_step' => (int)$progress['current_step']
            ]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("Tour progress API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
