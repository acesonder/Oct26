<?php
/**
 * OUTSINC Platform - Client Dashboard
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAnyRole(['client', 'worker', 'service_provider']);

$pageTitle = 'Dashboard';
$currentUser = getCurrentUser();

// Get user stats
try {
    $pdo = getDatabaseConnection();
    
    // Get goal stats
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_goals,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_goals,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_goals
        FROM client_goals 
        WHERE client_id = ?
    ");
    $stmt->execute([getCurrentUserId()]);
    $goalStats = $stmt->fetch();
    
    // Get recent goals
    $stmt = $pdo->prepare("
        SELECT * FROM client_goals 
        WHERE client_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([getCurrentUserId()]);
    $recentGoals = $stmt->fetchAll();
    
    // Get recent journal entries
    $stmt = $pdo->prepare("
        SELECT * FROM journal_entries 
        WHERE client_id = ? 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $stmt->execute([getCurrentUserId()]);
    $recentJournals = $stmt->fetchAll();
    
    // Get upcoming reminders
    $stmt = $pdo->prepare("
        SELECT * FROM reminders 
        WHERE user_id = ? AND is_completed = FALSE AND reminder_time > NOW()
        ORDER BY reminder_time ASC 
        LIMIT 5
    ");
    $stmt->execute([getCurrentUserId()]);
    $upcomingReminders = $stmt->fetchAll();
    
    // Get milestones
    $stmt = $pdo->prepare("
        SELECT m.*, um.achieved_at 
        FROM user_milestones um
        JOIN milestones m ON um.milestone_id = m.id
        WHERE um.user_id = ?
        ORDER BY um.achieved_at DESC
        LIMIT 5
    ");
    $stmt->execute([getCurrentUserId()]);
    $milestones = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $goalStats = ['total_goals' => 0, 'completed_goals' => 0, 'active_goals' => 0];
    $recentGoals = [];
    $recentJournals = [];
    $upcomingReminders = [];
    $milestones = [];
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome back, <?php echo e($currentUser['first_name']); ?>! 👋</h1>
        <p class="text-muted">Here's what's happening with your journey today.</p>
    </div>
    
    <!-- Quick Stats -->
    <div class="row">
        <div class="col-3">
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-info">
                    <h3><?php echo $goalStats['active_goals']; ?></h3>
                    <p>Active Goals</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo $goalStats['completed_goals']; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <div class="stat-icon">⏰</div>
                <div class="stat-info">
                    <h3><?php echo count($upcomingReminders); ?></h3>
                    <p>Reminders</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <div class="stat-icon">🏆</div>
                <div class="stat-info">
                    <h3><?php echo count($milestones); ?></h3>
                    <p>Achievements</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mood Check-in -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">How are you feeling today?</h3>
        </div>
        <div class="card-body">
            <div class="mood-selector">
                <button class="mood-btn" onclick="submitMoodCheckin(5)" title="Great">😄</button>
                <button class="mood-btn" onclick="submitMoodCheckin(4)" title="Good">🙂</button>
                <button class="mood-btn" onclick="submitMoodCheckin(3)" title="Okay">😐</button>
                <button class="mood-btn" onclick="submitMoodCheckin(2)" title="Not Great">😞</button>
                <button class="mood-btn" onclick="submitMoodCheckin(1)" title="Struggling">😢</button>
            </div>
        </div>
    </div>
    
    <!-- Recent Goals -->
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Your Goals</h3>
                    <a href="goals.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="card-body">
                    <?php if (count($recentGoals) > 0): ?>
                        <div class="goals-list">
                            <?php foreach ($recentGoals as $goal): ?>
                                <div class="goal-item">
                                    <div class="goal-header">
                                        <strong><?php echo e($goal['title']); ?></strong>
                                        <span class="badge badge-<?php echo $goal['status'] === 'completed' ? 'success' : 'primary'; ?>">
                                            <?php echo ucfirst($goal['status']); ?>
                                        </span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $goal['progress']; ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?php echo $goal['progress']; ?>% Complete</small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No goals yet. <a href="goals.php">Create your first goal!</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upcoming Reminders</h3>
                    <a href="#" class="btn btn-sm btn-outline">Manage</a>
                </div>
                <div class="card-body">
                    <?php if (count($upcomingReminders) > 0): ?>
                        <div class="reminders-list">
                            <?php foreach ($upcomingReminders as $reminder): ?>
                                <div class="reminder-item">
                                    <div class="reminder-icon">⏰</div>
                                    <div class="reminder-info">
                                        <strong><?php echo e($reminder['title']); ?></strong>
                                        <small><?php echo formatDateTime($reminder['reminder_time']); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No upcoming reminders</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Achievements -->
    <?php if (count($milestones) > 0): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Achievements 🏆</h3>
        </div>
        <div class="card-body">
            <div class="milestones-grid">
                <?php foreach ($milestones as $milestone): ?>
                    <div class="milestone-card">
                        <div class="milestone-badge">🏆</div>
                        <strong><?php echo e($milestone['milestone_name']); ?></strong>
                        <small><?php echo timeAgo($milestone['achieved_at']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-buttons">
            <a href="journal.php" class="action-btn">
                <span class="action-icon">📔</span>
                <span>New Journal Entry</span>
            </a>
            <a href="goals.php" class="action-btn">
                <span class="action-icon">🎯</span>
                <span>Set a Goal</span>
            </a>
            <a href="services.php" class="action-btn">
                <span class="action-icon">📍</span>
                <span>Find Services</span>
            </a>
            <a href="/modules/messenger/index.php" class="action-btn">
                <span class="action-icon">💬</span>
                <span>Send Message</span>
            </a>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    margin-bottom: var(--spacing-xl);
}

.stat-card {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: var(--spacing-lg);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.stat-icon {
    font-size: 48px;
}

.stat-info h3 {
    margin: 0;
    font-size: 32px;
}

.stat-info p {
    margin: 0;
    opacity: 0.9;
}

.mood-selector {
    display: flex;
    justify-content: space-around;
    gap: var(--spacing-md);
}

.mood-btn {
    font-size: 48px;
    background: none;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.mood-btn:hover {
    transform: scale(1.1);
    border-color: var(--primary-color);
    background-color: var(--bg-color);
}

.goals-list, .reminders-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.goal-item {
    padding: var(--spacing-md);
    background-color: var(--bg-color);
    border-radius: var(--radius-md);
}

.goal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-sm);
}

.progress-bar {
    height: 8px;
    background-color: var(--border-color);
    border-radius: var(--radius-full);
    overflow: hidden;
    margin: var(--spacing-sm) 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    transition: width var(--transition-slow);
}

.badge {
    padding: 4px 12px;
    border-radius: var(--radius-full);
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background-color: var(--success-color);
    color: white;
}

.badge-primary {
    background-color: var(--primary-color);
    color: white;
}

.reminder-item {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background-color: var(--bg-color);
    border-radius: var(--radius-md);
}

.reminder-icon {
    font-size: 24px;
}

.reminder-info {
    flex: 1;
}

.reminder-info strong {
    display: block;
}

.reminder-info small {
    color: var(--text-secondary);
}

.milestones-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: var(--spacing-md);
}

.milestone-card {
    text-align: center;
    padding: var(--spacing-md);
    background-color: var(--bg-color);
    border-radius: var(--radius-md);
}

.milestone-badge {
    font-size: 32px;
    margin-bottom: var(--spacing-sm);
}

.milestone-card strong {
    display: block;
    margin-bottom: var(--spacing-xs);
}

.quick-actions {
    margin-top: var(--spacing-xl);
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--spacing-md);
    margin-top: var(--spacing-md);
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-lg);
    background-color: var(--surface-color);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--text-color);
    transition: all var(--transition-fast);
}

.action-btn:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.action-icon {
    font-size: 32px;
}
</style>

<script>
function submitMoodCheckin(level) {
    fetch('/api/mood-checkin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ mood_level: level })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Mood check-in saved! Thank you for sharing.', 'success');
            playSound('success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to save mood check-in. Please try again.', 'error');
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
