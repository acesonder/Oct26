<?php
/**
 * OUTSINC Platform - Registration Page
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/modules/client/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $role = sanitizeInput($_POST['role'] ?? 'client');
        
        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            $error = 'Please fill in all required fields.';
        } elseif (!isValidEmail($email)) {
            $error = 'Please enter a valid email address.';
        } elseif (!isValidPassword($password)) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (!in_array($role, ['client', 'worker', 'service_provider'])) {
            $error = 'Invalid role selected.';
        } else {
            try {
                $pdo = getDatabaseConnection();
                
                // Check if username exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Username already exists. Please choose another.';
                } else {
                    // Check if email exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'Email already exists. Please use another email or login.';
                    } else {
                        // Create user
                        $passwordHash = hashPassword($password);
                        
                        // Clients are activated immediately, workers and service providers need approval
                        $status = ($role === 'client') ? 'active' : 'pending';
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO users (username, email, password_hash, role, status, first_name, last_name) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$username, $email, $passwordHash, $role, $status, $firstName, $lastName]);
                        $userId = $pdo->lastInsertId();
                        
                        // Create user profile
                        $stmt = $pdo->prepare("
                            INSERT INTO user_profiles (user_id, preferences, theme_settings, accessibility_settings) 
                            VALUES (?, '{}', '{}', '{}')
                        ");
                        $stmt->execute([$userId]);
                        
                        // Log audit
                        logAudit('user_registered', 'users', $userId, null, ['username' => $username, 'role' => $role]);
                        
                        if ($status === 'active') {
                            // Auto-login for clients
                            $_SESSION['user_id'] = $userId;
                            $_SESSION['user_role'] = $role;
                            $_SESSION['username'] = $username;
                            $_SESSION['first_name'] = $firstName;
                            
                            redirect('/modules/client/dashboard.php');
                        } else {
                            $success = 'Registration successful! Your account is pending approval. You will receive an email once approved.';
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Registration error: " . $e->getMessage());
                $error = 'An error occurred during registration. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register';
include __DIR__ . '/../../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>🌐 <?php echo e(APP_NAME); ?></h1>
            <p>Create your account to get started.</p>
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
                <label for="role" class="form-label">I am a...</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="client">Client (Seeking Support)</option>
                    <option value="worker">Outreach Worker</option>
                    <option value="service_provider">Service Provider</option>
                </select>
                <span class="form-help">Workers and Service Providers require admin approval</span>
            </div>
            
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            class="form-control" 
                            required
                            value="<?php echo isset($_POST['first_name']) ? e($_POST['first_name']) : ''; ?>"
                        >
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            class="form-control" 
                            required
                            value="<?php echo isset($_POST['last_name']) ? e($_POST['last_name']) : ''; ?>"
                        >
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    required
                    value="<?php echo isset($_POST['username']) ? e($_POST['username']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    required
                    value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>"
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
                    minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                >
                <span class="form-help">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</span>
            </div>
            
            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="form-control" 
                    required
                >
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary w-100">Create Account</button>
            </div>
            
            <div class="auth-links">
                <a href="login.php">Already have an account? Login</a>
            </div>
        </form>
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
    max-width: 600px;
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
    justify-content: center;
    margin-top: var(--spacing-md);
    font-size: var(--font-size-small);
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
