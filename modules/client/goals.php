<?php
/**
 * OUTSINC Platform - Client Goals
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAnyRole(['client', 'worker', 'service_provider']);

$pageTitle = 'My Goals';
$error = '';
$success = '';

// Handle goal creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $category = sanitizeInput($_POST['category'] ?? '');
        $targetDate = sanitizeInput($_POST['target_date'] ?? null);
        
        if (!empty($title)) {
            try {
                $pdo = getDatabaseConnection();
                $stmt = $pdo->prepare("
                    INSERT INTO client_goals (client_id, title, description, category, target_date, status, progress)
                    VALUES (?, ?, ?, ?, ?, 'active', 0)
                ");
                $stmt->execute([getCurrentUserId(), $title, $description, $category, $targetDate]);
                
                logAudit('goal_created', 'client_goals', $pdo->lastInsertId());
                $success = 'Goal created successfully!';
            } catch (Exception $e) {
                error_log("Goal creation error: " . $e->getMessage());
                $error = 'Failed to create goal. Please try again.';
            }
        } else {
            $error = 'Please enter a goal title.';
        }
    }
}

// Handle goal update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $goalId = (int)($_POST['goal_id'] ?? 0);
        $progress = (int)($_POST['progress'] ?? 0);
        
        if ($goalId > 0) {
            try {
                $pdo = getDatabaseConnection();
                
                // Check if 100% and update status
                $status = $progress >= 100 ? 'completed' : 'active';
                $completedAt = $progress >= 100 ? date('Y-m-d H:i:s') : null;
                
                $stmt = $pdo->prepare("
                    UPDATE client_goals 
                    SET progress = ?, status = ?, completed_at = ?
                    WHERE id = ? AND client_id = ?
                ");
                $stmt->execute([$progress, $status, $completedAt, $goalId, getCurrentUserId()]);
                
                logAudit('goal_updated', 'client_goals', $goalId);
                $success = 'Goal updated successfully!';
            } catch (Exception $e) {
                error_log("Goal update error: " . $e->getMessage());
                $error = 'Failed to update goal. Please try again.';
            }
        }
    }
}

// Get all goals
try {
    $pdo = getDatabaseConnection();
    
    $stmt = $pdo->prepare("
        SELECT * FROM client_goals 
        WHERE client_id = ? 
        ORDER BY 
            CASE status 
                WHEN 'active' THEN 1 
                WHEN 'completed' THEN 2 
                ELSE 3 
            END,
            created_at DESC
    ");
    $stmt->execute([getCurrentUserId()]);
    $goals = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Goals fetch error: " . $e->getMessage());
    $goals = [];
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>🎯 My Goals</h1>
        <button class="btn btn-primary" onclick="toggleGoalForm()">+ New Goal</button>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>
    
    <!-- Goal Creation Form -->
    <div id="goal-form" class="card" style="display: none;">
        <div class="card-header">
            <h3 class="card-title">Create New Goal</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="title" class="form-label">Goal Title *</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="category" class="form-label">Category</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">Select category</option>
                                <option value="health">Health & Wellness</option>
                                <option value="housing">Housing</option>
                                <option value="employment">Employment</option>
                                <option value="education">Education</option>
                                <option value="relationships">Relationships</option>
                                <option value="personal">Personal Development</option>
                                <option value="financial">Financial</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="target_date" class="form-label">Target Date</label>
                            <input type="date" id="target_date" name="target_date" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Goal</button>
                    <button type="button" class="btn btn-outline" onclick="toggleGoalForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Goals List -->
    <?php if (count($goals) > 0): ?>
        <div class="goals-grid">
            <?php foreach ($goals as $goal): ?>
                <div class="goal-card <?php echo $goal['status']; ?>">
                    <div class="goal-header">
                        <h3><?php echo e($goal['title']); ?></h3>
                        <span class="goal-status badge badge-<?php echo $goal['status'] === 'completed' ? 'success' : 'primary'; ?>">
                            <?php echo ucfirst($goal['status']); ?>
                        </span>
                    </div>
                    
                    <?php if ($goal['description']): ?>
                        <p class="goal-description"><?php echo e($goal['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="goal-meta">
                        <?php if ($goal['category']): ?>
                            <span class="goal-category">📂 <?php echo ucfirst($goal['category']); ?></span>
                        <?php endif; ?>
                        <?php if ($goal['target_date']): ?>
                            <span class="goal-date">📅 <?php echo formatDate($goal['target_date']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="goal-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $goal['progress']; ?>%"></div>
                        </div>
                        <span class="progress-text"><?php echo $goal['progress']; ?>% Complete</span>
                    </div>
                    
                    <?php if ($goal['status'] !== 'completed'): ?>
                        <form method="POST" action="" class="progress-update-form">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="goal_id" value="<?php echo $goal['id']; ?>">
                            <div class="progress-slider">
                                <input 
                                    type="range" 
                                    name="progress" 
                                    min="0" 
                                    max="100" 
                                    value="<?php echo $goal['progress']; ?>"
                                    class="slider"
                                    onchange="this.form.submit()"
                                >
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="goal-completed">
                            ✅ Completed <?php echo timeAgo($goal['completed_at']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <small class="text-muted">Created <?php echo timeAgo($goal['created_at']); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center">
                <h3>No goals yet</h3>
                <p>Start your journey by setting your first goal!</p>
                <button class="btn btn-primary" onclick="toggleGoalForm()">Create Your First Goal</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-xl);
}

.goals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--spacing-lg);
}

.goal-card {
    background-color: var(--surface-color);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    transition: all var(--transition-base);
}

.goal-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
}

.goal-card.completed {
    border-color: var(--success-color);
    background-color: rgba(39, 174, 96, 0.05);
}

.goal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-md);
}

.goal-header h3 {
    margin: 0;
    font-size: var(--font-size-h4);
}

.goal-description {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-md);
}

.goal-meta {
    display: flex;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-md);
    font-size: var(--font-size-small);
}

.goal-progress {
    margin: var(--spacing-md) 0;
}

.progress-text {
    display: block;
    text-align: center;
    margin-top: var(--spacing-xs);
    font-weight: 600;
    color: var(--primary-color);
}

.progress-slider {
    margin-top: var(--spacing-md);
}

.slider {
    width: 100%;
    height: 6px;
    border-radius: var(--radius-full);
    background: var(--border-color);
    outline: none;
}

.slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--primary-color);
    cursor: pointer;
}

.slider::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--primary-color);
    cursor: pointer;
}

.goal-completed {
    background-color: var(--success-color);
    color: white;
    padding: var(--spacing-sm);
    border-radius: var(--radius-md);
    text-align: center;
    margin: var(--spacing-md) 0;
}

.form-actions {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
}
</style>

<script>
function toggleGoalForm() {
    const form = document.getElementById('goal-form');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
