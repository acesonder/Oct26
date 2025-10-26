<?php
/**
 * OUTSINC Platform - Worker Dashboard
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('worker');

$pageTitle = 'Worker Dashboard';
$currentUser = getCurrentUser();

// Get worker stats
try {
    $pdo = getDatabaseConnection();
    
    // Get assigned cases
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total,
               SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_cases,
               SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_cases,
               SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_cases
        FROM cases 
        WHERE assigned_worker_id = ?
    ");
    $stmt->execute([getCurrentUserId()]);
    $caseStats = $stmt->fetch();
    
    // Get recent cases
    $stmt = $pdo->prepare("
        SELECT c.*, u.first_name, u.last_name
        FROM cases c
        JOIN users u ON c.client_id = u.id
        WHERE c.assigned_worker_id = ?
        ORDER BY c.updated_at DESC
        LIMIT 5
    ");
    $stmt->execute([getCurrentUserId()]);
    $recentCases = $stmt->fetchAll();
    
    // Get today's outreach logs
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM outreach_logs
        WHERE worker_id = ? AND DATE(log_time) = CURDATE()
    ");
    $stmt->execute([getCurrentUserId()]);
    $todayOutreach = $stmt->fetchColumn();
    
    // Get pending incidents
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM incident_reports
        WHERE reporter_id = ? AND status = 'reported'
    ");
    $stmt->execute([getCurrentUserId()]);
    $pendingIncidents = $stmt->fetchColumn();
    
} catch (Exception $e) {
    error_log("Worker dashboard error: " . $e->getMessage());
    $caseStats = ['total' => 0, 'open_cases' => 0, 'active_cases' => 0, 'urgent_cases' => 0];
    $recentCases = [];
    $todayOutreach = 0;
    $pendingIncidents = 0;
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo e($currentUser['first_name']); ?>! 👷</h1>
        <p class="text-muted">Your outreach command center</p>
    </div>
    
    <!-- Quick Stats -->
    <div class="row">
        <div class="col-3">
            <div class="stat-card">
                <div class="stat-icon">📁</div>
                <div class="stat-info">
                    <h3><?php echo $caseStats['active_cases']; ?></h3>
                    <p>Active Cases</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card urgent">
                <div class="stat-icon">⚠️</div>
                <div class="stat-info">
                    <h3><?php echo $caseStats['urgent_cases']; ?></h3>
                    <p>Urgent</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <div class="stat-icon">🚶</div>
                <div class="stat-info">
                    <h3><?php echo $todayOutreach; ?></h3>
                    <p>Today's Outreach</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <h3><?php echo $pendingIncidents; ?></h3>
                    <p>Pending Reports</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Safety Check-in -->
    <div class="card safety-card">
        <div class="card-header">
            <h3 class="card-title">✅ Safety Check-in</h3>
        </div>
        <div class="card-body">
            <p>Are you safe and ready to work?</p>
            <div class="safety-buttons">
                <button class="btn btn-success" onclick="submitSafetyCheckin('safe')">
                    ✅ I'm Safe
                </button>
                <button class="btn btn-warning" onclick="submitSafetyCheckin('need_assistance')">
                    ⚠️ Need Assistance
                </button>
                <button class="btn btn-danger" onclick="submitSafetyCheckin('emergency')">
                    🆘 Emergency
                </button>
            </div>
        </div>
    </div>
    
    <!-- Recent Cases -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Cases</h3>
            <a href="cases.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="card-body">
            <?php if (count($recentCases) > 0): ?>
                <div class="cases-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Case #</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Last Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentCases as $case): ?>
                                <tr>
                                    <td><?php echo e($case['case_number']); ?></td>
                                    <td><?php echo e($case['first_name'] . ' ' . $case['last_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $case['status']; ?>">
                                            <?php echo ucfirst($case['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="priority-badge priority-<?php echo $case['priority']; ?>">
                                            <?php echo ucfirst($case['priority']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo timeAgo($case['updated_at']); ?></td>
                                    <td>
                                        <a href="case-detail.php?id=<?php echo $case['id']; ?>" class="btn btn-sm btn-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">No cases assigned yet.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-buttons">
            <a href="outreach.php" class="action-btn">
                <span class="action-icon">🚶</span>
                <span>Log Outreach</span>
            </a>
            <a href="incidents.php" class="action-btn">
                <span class="action-icon">⚠️</span>
                <span>Report Incident</span>
            </a>
            <a href="supplies.php" class="action-btn">
                <span class="action-icon">📦</span>
                <span>Track Supplies</span>
            </a>
            <a href="cases.php" class="action-btn">
                <span class="action-icon">📁</span>
                <span>Manage Cases</span>
            </a>
        </div>
    </div>
</div>

<style>
.stat-card.urgent {
    background: linear-gradient(135deg, var(--danger-color), #C0392B);
}

.safety-card {
    background: linear-gradient(135deg, #E8F5E9, #C8E6C9);
}

.safety-buttons {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-md);
}

.cases-table {
    overflow-x: auto;
}

.cases-table table {
    width: 100%;
    border-collapse: collapse;
}

.cases-table th,
.cases-table td {
    padding: var(--spacing-sm) var(--spacing-md);
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.cases-table th {
    background-color: var(--bg-color);
    font-weight: 600;
}

.priority-badge {
    padding: 4px 12px;
    border-radius: var(--radius-full);
    font-size: 12px;
    font-weight: 600;
}

.priority-low {
    background-color: #E3F2FD;
    color: #1976D2;
}

.priority-medium {
    background-color: #FFF3E0;
    color: #F57C00;
}

.priority-high {
    background-color: #FFE0B2;
    color: #E65100;
}

.priority-urgent {
    background-color: #FFCDD2;
    color: #C62828;
}

.badge-open {
    background-color: var(--info-color);
    color: white;
}

.badge-active {
    background-color: var(--primary-color);
    color: white;
}

.badge-closed {
    background-color: var(--text-secondary);
    color: white;
}
</style>

<script>
function submitSafetyCheckin(status) {
    fetch('/api/safety-checkin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Safety check-in recorded. Stay safe!', 'success');
            playSound('success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to record check-in. Please try again.', 'error');
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
