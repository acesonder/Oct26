<?php
/**
 * OUTSINC Platform - User Settings
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$pageTitle = 'Settings';
$currentUser = getCurrentUser();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        try {
            $pdo = getDatabaseConnection();
            
            // Update profile
            if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
                $firstName = sanitizeInput($_POST['first_name'] ?? '');
                $lastName = sanitizeInput($_POST['last_name'] ?? '');
                $phone = sanitizeInput($_POST['phone'] ?? '');
                $location = sanitizeInput($_POST['location'] ?? '');
                
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, phone = ?
                    WHERE id = ?
                ");
                $stmt->execute([$firstName, $lastName, $phone, getCurrentUserId()]);
                
                $stmt = $pdo->prepare("
                    UPDATE user_profiles 
                    SET location = ?
                    WHERE user_id = ?
                ");
                $stmt->execute([$location, getCurrentUserId()]);
                
                $success = 'Profile updated successfully!';
            }
            
            // Update password
            if (isset($_POST['action']) && $_POST['action'] === 'update_password') {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                if (empty($currentPassword) || empty($newPassword)) {
                    $error = 'Please fill in all password fields.';
                } elseif (!isValidPassword($newPassword)) {
                    $error = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'New passwords do not match.';
                } else {
                    // Verify current password
                    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                    $stmt->execute([getCurrentUserId()]);
                    $user = $stmt->fetch();
                    
                    if (!password_verify($currentPassword, $user['password_hash'])) {
                        $error = 'Current password is incorrect.';
                    } else {
                        // Update password
                        $newHash = hashPassword($newPassword);
                        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                        $stmt->execute([$newHash, getCurrentUserId()]);
                        
                        $success = 'Password updated successfully!';
                        logAudit('password_changed', 'users', getCurrentUserId(), null, null);
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Settings error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container settings-container">
    <div class="settings-header">
        <h1>⚙️ Settings</h1>
        <p class="text-muted">Manage your account and preferences</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>
    
    <!-- Profile Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Profile Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?php echo e($currentUser['username']); ?>" disabled>
                    <span class="form-help">Username cannot be changed</span>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                   value="<?php echo e($currentUser['first_name']); ?>" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                   value="<?php echo e($currentUser['last_name']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo e($currentUser['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" id="location" name="location" class="form-control" 
                           value="<?php echo e($currentUser['location'] ?? ''); ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">Save Profile</button>
            </form>
        </div>
    </div>
    
    <!-- Password Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Change Password</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="update_password">
                
                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" 
                           minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                    <span class="form-help">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</span>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
    
    <!-- Welcome Tour -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Welcome Tour</h3>
        </div>
        <div class="card-body">
            <p>Want to see the welcome tour again? Click the button below to restart it.</p>
            <button onclick="restartWelcomeTour()" class="btn btn-outline">
                🎯 Restart Welcome Tour
            </button>
        </div>
    </div>
    
    <!-- Theme Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Appearance</h3>
        </div>
        <div class="card-body">
            <p>Current theme: <strong id="current-theme-name"></strong></p>
            <button onclick="toggleTheme()" class="btn btn-outline">
                Toggle Theme
            </button>
        </div>
    </div>
</div>

<style>
.settings-header {
    margin-bottom: var(--spacing-xl);
}

.settings-container .card {
    margin-bottom: var(--spacing-lg);
}
</style>

<script>
// Update current theme name
document.addEventListener('DOMContentLoaded', () => {
    const theme = localStorage.getItem('theme') || 'light';
    const themeNameElement = document.getElementById('current-theme-name');
    if (themeNameElement) {
        themeNameElement.textContent = theme.charAt(0).toUpperCase() + theme.slice(1);
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
