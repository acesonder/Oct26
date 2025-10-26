<?php
/**
 * OUTSINC Platform - Security and Utility Functions
 */

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

/**
 * Check if user has any of the specified roles
 */
function hasAnyRole($roles) {
    if (!isLoggedIn()) return false;
    return in_array($_SESSION['user_role'], $roles);
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /modules/auth/login.php');
        exit;
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied. Insufficient permissions.');
    }
}

/**
 * Require any of specified roles
 */
function requireAnyRole($roles) {
    requireLogin();
    if (!hasAnyRole($roles)) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied. Insufficient permissions.');
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT id, username, email, role, first_name, last_name FROM users WHERE id = ?");
    $stmt->execute([getCurrentUserId()]);
    return $stmt->fetch();
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Log audit event
 */
function logAudit($action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            getCurrentUserId(),
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Audit log failed: " . $e->getMessage());
    }
}

/**
 * Check login attempts and lockout
 */
function checkLoginAttempts($username) {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("
        SELECT failed_login_attempts, locked_until 
        FROM users 
        WHERE username = ? OR email = ?
    ");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user) return true;
    
    // Check if account is locked
    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        return false;
    }
    
    // Check if too many failed attempts
    if ($user['failed_login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        // Lock the account
        $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
        $stmt = $pdo->prepare("UPDATE users SET locked_until = ? WHERE username = ? OR email = ?");
        $stmt->execute([$lockUntil, $username, $username]);
        return false;
    }
    
    return true;
}

/**
 * Record failed login attempt
 */
function recordFailedLogin($username) {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("
        UPDATE users 
        SET failed_login_attempts = failed_login_attempts + 1 
        WHERE username = ? OR email = ?
    ");
    $stmt->execute([$username, $username]);
    logAudit('failed_login', 'users', null, null, ['username' => $username]);
}

/**
 * Reset failed login attempts
 */
function resetFailedLoginAttempts($userId) {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("
        UPDATE users 
        SET failed_login_attempts = 0, locked_until = NULL 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function isValidPassword($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'M d, Y g:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Time ago format
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return formatDateTime($datetime);
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Upload file securely
 */
function uploadFile($file, $uploadDir = null) {
    if (!$uploadDir) {
        $uploadDir = UPLOAD_PATH;
    }
    
    // Validate file
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception('Invalid file upload');
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    
    // Check file size
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        throw new Exception('File too large. Maximum size: ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . 'MB');
    }
    
    // Validate file type
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, ALLOWED_FILE_TYPES)) {
        throw new Exception('File type not allowed');
    }
    
    // Generate unique filename
    $newFilename = uniqid() . '_' . time() . '.' . $fileExt;
    $targetPath = $uploadDir . $newFilename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    return [
        'original_name' => $file['name'],
        'stored_name' => $newFilename,
        'path' => $targetPath,
        'size' => $file['size'],
        'type' => $fileExt,
        'mime_type' => mime_content_type($targetPath)
    ];
}

/**
 * Get system setting
 */
function getSetting($key, $default = null) {
    static $cache = [];
    
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetchColumn();
    
    $value = $result !== false ? $result : $default;
    $cache[$key] = $value;
    
    return $value;
}

/**
 * Set system setting
 */
function setSetting($key, $value, $userId = null) {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("
        INSERT INTO system_settings (setting_key, setting_value, updated_by) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE setting_value = ?, updated_by = ?
    ");
    $stmt->execute([$key, $value, $userId, $value, $userId]);
}

/**
 * Escape output for HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
