<?php
/**
 * OUTSINC Platform - Admin Dashboard
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('admin');

$pageTitle = 'Admin Dashboard';

// Get system stats
try {
    $pdo = getDatabaseConnection();
    
    // User stats
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_users,
            SUM(CASE WHEN role = 'client' THEN 1 ELSE 0 END) as total_clients,
            SUM(CASE WHEN role = 'worker' THEN 1 ELSE 0 END) as total_workers
        FROM users
    ");
    $userStats = $stmt->fetch();
    
    // Recent registrations
    $stmt = $pdo->query("
        SELECT * FROM users 
        WHERE status = 'pending' AND role IN ('worker', 'service_provider')
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $pendingApprovals = $stmt->fetchAll();
    
    // System health
    $stmt = $pdo->query("
        SELECT * FROM system_health_logs 
        WHERE status != 'healthy'
        ORDER BY checked_at DESC 
        LIMIT 5
    ");
    $healthIssues = $stmt->fetchAll();
    
    // Recent activity
    $stmt = $pdo->query("
        SELECT al.*, u.username 
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC 
        LIMIT 10
    ");
    $recentActivity = $stmt->fetchAll();
    
    // Database size
    $stmt = $pdo->query("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
        FROM information_schema.TABLES 
        WHERE table_schema = '" . DB_NAME . "'
    ");
    $dbSize = $stmt->fetchColumn();
    
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $userStats = ['total_users' => 0, 'active_users' => 0, 'pending_users' => 0, 'total_clients' => 0, 'total_workers' => 0];
    $pendingApprovals = [];
    $healthIssues = [];
    $recentActivity = [];
    $dbSize = 0;
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container admin-dashboard">
    <div class="dashboard-header">
        <h1>🛠️ Admin Dashboard</h1>
        <p class="text-muted">System overview and management</p>
    </div>
    
    <!-- System Stats -->
    <div class="row">
        <div class="col-3">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?php echo $userStats['total_users']; ?></h3>
                    <p>Total Users</p>
                    <small><?php echo $userStats['active_users']; ?> active</small>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card pending">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3><?php echo $userStats['pending_users']; ?></h3>
                    <p>Pending Approvals</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <div class="stat-icon">💾</div>
                <div class="stat-info">
                    <h3><?php echo $dbSize; ?> MB</h3>
                    <p>Database Size</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card <?php echo count($healthIssues) > 0 ? 'warning' : 'healthy'; ?>">
                <div class="stat-icon">💊</div>
                <div class="stat-info">
                    <h3><?php echo count($healthIssues) > 0 ? '⚠️' : '✅'; ?></h3>
                    <p>System Health</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="admin-quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-grid">
            <a href="users.php" class="admin-action-card">
                <span class="action-icon">👥</span>
                <span class="action-title">Manage Users</span>
                <span class="action-count"><?php echo $userStats['total_users']; ?></span>
            </a>
            <a href="approvals.php" class="admin-action-card highlight">
                <span class="action-icon">✓</span>
                <span class="action-title">Pending Approvals</span>
                <span class="action-count"><?php echo $userStats['pending_users']; ?></span>
            </a>
            <a href="system-health.php" class="admin-action-card">
                <span class="action-icon">💊</span>
                <span class="action-title">System Health</span>
            </a>
            <a href="diagnostics.php" class="admin-action-card">
                <span class="action-icon">🔧</span>
                <span class="action-title">Diagnostics</span>
            </a>
            <a href="backup.php" class="admin-action-card">
                <span class="action-icon">💾</span>
                <span class="action-title">Backup & Restore</span>
            </a>
            <a href="reports.php" class="admin-action-card">
                <span class="action-icon">📊</span>
                <span class="action-title">Reports</span>
            </a>
        </div>
    </div>
    
    <!-- Pending Approvals -->
    <?php if (count($pendingApprovals) > 0): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">⏳ Pending Account Approvals</h3>
            <a href="approvals.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="card-body">
            <div class="approval-list">
                <?php foreach ($pendingApprovals as $user): ?>
                    <div class="approval-item">
                        <div class="approval-info">
                            <strong><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                            <span class="text-muted">(<?php echo e($user['username']); ?>)</span>
                            <span class="role-badge"><?php echo ucfirst($user['role']); ?></span>
                            <small class="text-muted">Applied <?php echo timeAgo($user['created_at']); ?></small>
                        </div>
                        <div class="approval-actions">
                            <button class="btn btn-sm btn-success" onclick="approveUser(<?php echo $user['id']; ?>)">
                                ✓ Approve
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="rejectUser(<?php echo $user['id']; ?>)">
                                ✗ Reject
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- System Health Issues -->
    <?php if (count($healthIssues) > 0): ?>
    <div class="card health-card">
        <div class="card-header">
            <h3 class="card-title">⚠️ System Health Alerts</h3>
            <a href="system-health.php" class="btn btn-sm btn-outline">View Details</a>
        </div>
        <div class="card-body">
            <div class="health-issues">
                <?php foreach ($healthIssues as $issue): ?>
                    <div class="health-item status-<?php echo $issue['status']; ?>">
                        <span class="health-icon">
                            <?php 
                            echo $issue['status'] === 'critical' ? '🔴' : 
                                 ($issue['status'] === 'warning' ? '🟡' : '🟢');
                            ?>
                        </span>
                        <div class="health-details">
                            <strong><?php echo ucfirst($issue['check_type']); ?></strong>
                            <p><?php echo e($issue['message']); ?></p>
                            <small><?php echo timeAgo($issue['checked_at']); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📋 Recent Activity</h3>
        </div>
        <div class="card-body">
            <div class="activity-log">
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="activity-item">
                        <span class="activity-time"><?php echo timeAgo($activity['created_at']); ?></span>
                        <span class="activity-user"><?php echo e($activity['username'] ?? 'System'); ?></span>
                        <span class="activity-action"><?php echo e($activity['action']); ?></span>
                        <?php if ($activity['table_name']): ?>
                            <span class="activity-table"><?php echo e($activity['table_name']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card.pending {
    background: linear-gradient(135deg, var(--warning-color), #E67E22);
}

.stat-card.healthy {
    background: linear-gradient(135deg, var(--success-color), #27AE60);
}

.stat-card.warning {
    background: linear-gradient(135deg, var(--danger-color), #C0392B);
}

.admin-quick-actions {
    margin: var(--spacing-xl) 0;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--spacing-md);
    margin-top: var(--spacing-md);
}

.admin-action-card {
    background-color: var(--surface-color);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    text-align: center;
    text-decoration: none;
    color: var(--text-color);
    transition: all var(--transition-fast);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.admin-action-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.admin-action-card.highlight {
    border-color: var(--warning-color);
    background-color: rgba(243, 156, 18, 0.1);
}

.admin-action-card .action-icon {
    font-size: 48px;
}

.admin-action-card .action-title {
    font-weight: 600;
    font-size: var(--font-size-large);
}

.admin-action-card .action-count {
    font-size: var(--font-size-h2);
    font-weight: 700;
    color: var(--primary-color);
}

.approval-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.approval-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-md);
    background-color: var(--bg-color);
    border-radius: var(--radius-md);
}

.approval-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.role-badge {
    padding: 4px 12px;
    background-color: var(--primary-color);
    color: white;
    border-radius: var(--radius-full);
    font-size: 12px;
    font-weight: 600;
}

.approval-actions {
    display: flex;
    gap: var(--spacing-sm);
}

.health-card {
    border-left: 4px solid var(--warning-color);
}

.health-issues {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.health-item {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background-color: var(--bg-color);
    border-radius: var(--radius-md);
}

.health-icon {
    font-size: 24px;
}

.activity-log {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.activity-item {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-sm);
    border-bottom: 1px solid var(--border-color);
    font-size: var(--font-size-small);
}

.activity-time {
    color: var(--text-secondary);
    min-width: 100px;
}

.activity-user {
    font-weight: 600;
    min-width: 120px;
}

.activity-action {
    color: var(--primary-color);
}

.activity-table {
    color: var(--text-secondary);
    font-style: italic;
}
</style>

<script>
function approveUser(userId) {
    if (!confirm('Approve this user account?')) return;
    
    fetch('/api/approve-user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id: userId, action: 'approve' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('User approved successfully!', 'success');
            location.reload();
        } else {
            showAlert('Failed to approve user.', 'error');
        }
    });
}

function rejectUser(userId) {
    if (!confirm('Reject this user account? This action cannot be undone.')) return;
    
    fetch('/api/approve-user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id: userId, action: 'reject' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('User rejected.', 'info');
            location.reload();
        } else {
            showAlert('Failed to reject user.', 'error');
        }
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
