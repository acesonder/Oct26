<?php
/**
 * OUTSINC Platform - Login Page
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getCurrentUserRole();
    switch ($role) {
        case 'admin':
            redirect('/modules/admin/dashboard.php');
        case 'worker':
            redirect('/modules/worker/dashboard.php');
        default:
            redirect('/modules/client/dashboard.php');
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username/email and password.';
        } else {
            // Check login attempts
            if (!checkLoginAttempts($username)) {
                $error = 'Account is temporarily locked due to too many failed login attempts. Please try again in 15 minutes.';
            } else {
                try {
                    $pdo = getDatabaseConnection();
                    $stmt = $pdo->prepare("
                        SELECT id, username, email, password_hash, role, status, first_name, last_name 
                        FROM users 
                        WHERE (username = ? OR email = ?)
                    ");
                    $stmt->execute([$username, $username]);
                    $user = $stmt->fetch();
                    
                    if ($user && verifyPassword($password, $user['password_hash'])) {
                        // Check if user is active
                        if ($user['status'] !== 'active') {
                            if ($user['status'] === 'pending') {
                                $error = 'Your account is pending approval. Please wait for an administrator to activate your account.';
                            } else {
                                $error = 'Your account is not active. Please contact support.';
                            }
                            recordFailedLogin($username);
                        } else {
                            // Successful login
                            resetFailedLoginAttempts($user['id']);
                            
                            // Set session variables
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_role'] = $user['role'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['first_name'] = $user['first_name'];
                            
                            // Update last login
                            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                            $stmt->execute([$user['id']]);
                            
                            // Log session
                            $stmt = $pdo->prepare("
                                INSERT INTO session_logs (user_id, session_id, ip_address, user_agent) 
                                VALUES (?, ?, ?, ?)
                            ");
                            $stmt->execute([
                                $user['id'],
                                session_id(),
                                $_SERVER['REMOTE_ADDR'] ?? null,
                                $_SERVER['HTTP_USER_AGENT'] ?? null
                            ]);
                            
                            // Log audit
                            logAudit('login', 'users', $user['id']);
                            
                            // Redirect based on role
                            switch ($user['role']) {
                                case 'admin':
                                    redirect('/modules/admin/dashboard.php');
                                case 'worker':
                                    redirect('/modules/worker/dashboard.php');
                                default:
                                    redirect('/modules/client/dashboard.php');
                            }
                        }
                    } else {
                        $error = 'Invalid username/email or password.';
                        recordFailedLogin($username);
                    }
                } catch (Exception $e) {
                    error_log("Login error: " . $e->getMessage());
                    $error = 'An error occurred. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Login';
include __DIR__ . '/../../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>🌐 <?php echo e(APP_NAME); ?></h1>
            <p>Welcome back! Please login to continue.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo e($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="username" class="form-label">Username or Email</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    required 
                    autofocus
                    value="<?php echo isset($_POST['username']) ? e($_POST['username']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    required
                >
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </div>
            
            <div class="auth-links">
                <a href="register.php">Don't have an account? Register</a>
                <a href="forgot-password.php">Forgot password?</a>
            </div>
        </form>
        
        <div class="crisis-info">
            <h4>Need Immediate Help?</h4>
            <p><strong>988</strong> - Suicide & Crisis Lifeline</p>
            <p><strong>911</strong> - Emergency Services</p>
        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-lg);
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

.auth-card {
    background-color: var(--surface-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    max-width: 450px;
    width: 100%;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.auth-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.auth-header h1 {
    color: var(--primary-color);
    margin-bottom: var(--spacing-sm);
}

.auth-header p {
    color: var(--text-secondary);
    margin: 0;
}

.auth-form {
    margin-bottom: var(--spacing-lg);
}

.auth-links {
    display: flex;
    justify-content: space-between;
    margin-top: var(--spacing-md);
    font-size: var(--font-size-small);
}

.crisis-info {
    margin-top: var(--spacing-xl);
    padding-top: var(--spacing-lg);
    border-top: 1px solid var(--border-color);
    text-align: center;
}

.crisis-info h4 {
    color: var(--danger-color);
    margin-bottom: var(--spacing-sm);
}

.crisis-info p {
    margin-bottom: var(--spacing-xs);
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
