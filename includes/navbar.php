<?php
$currentUser = getCurrentUser();
$currentRole = getCurrentUserRole();
?>
<nav class="navbar" id="main-navbar">
    <div class="navbar-container">
        <!-- Logo/Brand -->
        <div class="navbar-brand">
            <a href="/index.php">
                <span class="logo">🌐</span>
                <span class="brand-name"><?php echo e(APP_NAME); ?></span>
            </a>
        </div>
        
        <!-- Navigation Links -->
        <div class="navbar-menu" id="navbar-menu">
            <ul class="navbar-nav">
                <!-- Client Navigation -->
                <?php if ($currentRole === 'client'): ?>
                    <li><a href="/modules/client/dashboard.php">
                        <span class="icon">🏠</span> Dashboard
                    </a></li>
                    <li><a href="/modules/client/goals.php">
                        <span class="icon">🎯</span> Goals
                    </a></li>
                    <li><a href="/modules/client/services.php">
                        <span class="icon">📍</span> Services
                    </a></li>
                    <li><a href="/modules/client/journal.php">
                        <span class="icon">📔</span> Journal
                    </a></li>
                    <li><a href="/modules/client/intake.php">
                        <span class="icon">📋</span> Intake
                    </a></li>
                <?php endif; ?>
                
                <!-- Worker Navigation -->
                <?php if ($currentRole === 'worker'): ?>
                    <li><a href="/modules/worker/dashboard.php">
                        <span class="icon">🏠</span> Dashboard
                    </a></li>
                    <li><a href="/modules/worker/cases.php">
                        <span class="icon">📁</span> Cases
                    </a></li>
                    <li><a href="/modules/worker/outreach.php">
                        <span class="icon">🚶</span> Outreach
                    </a></li>
                    <li><a href="/modules/worker/incidents.php">
                        <span class="icon">⚠️</span> Incidents
                    </a></li>
                    <li><a href="/modules/worker/supplies.php">
                        <span class="icon">📦</span> Supplies
                    </a></li>
                    <li><a href="/modules/worker/safety.php">
                        <span class="icon">✅</span> Check-in
                    </a></li>
                <?php endif; ?>
                
                <!-- Admin Navigation -->
                <?php if ($currentRole === 'admin'): ?>
                    <li><a href="/modules/admin/dashboard.php">
                        <span class="icon">🏠</span> Dashboard
                    </a></li>
                    <li><a href="/modules/admin/users.php">
                        <span class="icon">👥</span> Users
                    </a></li>
                    <li><a href="/modules/admin/approvals.php">
                        <span class="icon">✓</span> Approvals
                    </a></li>
                    <li><a href="/modules/admin/system-health.php">
                        <span class="icon">💊</span> System Health
                    </a></li>
                    <li><a href="/modules/admin/diagnostics.php">
                        <span class="icon">🔧</span> Diagnostics
                    </a></li>
                    <li><a href="/modules/admin/reports.php">
                        <span class="icon">📊</span> Reports
                    </a></li>
                <?php endif; ?>
                
                <!-- Common Links -->
                <li><a href="/modules/messenger/index.php">
                    <span class="icon">💬</span> Messages
                    <span class="badge" id="message-count" style="display: none;">0</span>
                </a></li>
            </ul>
        </div>
        
        <!-- Right Side Menu -->
        <div class="navbar-right">
            <!-- Theme Toggle -->
            <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                <span class="theme-icon">🌙</span>
            </button>
            
            <!-- Notifications -->
            <div class="notification-dropdown">
                <button class="notification-btn" onclick="toggleNotifications()">
                    <span class="icon">🔔</span>
                    <span class="badge" id="notification-count" style="display: none;">0</span>
                </button>
                <div class="notification-panel" id="notification-panel" style="display: none;">
                    <div class="notification-header">
                        <h4>Notifications</h4>
                        <a href="#" class="mark-read">Mark all read</a>
                    </div>
                    <div class="notification-list" id="notification-list">
                        <p class="no-notifications">No new notifications</p>
                    </div>
                </div>
            </div>
            
            <!-- User Menu -->
            <div class="user-dropdown">
                <button class="user-menu-btn" onclick="toggleUserMenu()">
                    <span class="user-avatar">
                        <?php echo strtoupper(substr($currentUser['first_name'] ?? $currentUser['username'], 0, 1)); ?>
                    </span>
                    <span class="user-name">
                        <?php echo e($currentUser['first_name'] ?? $currentUser['username']); ?>
                    </span>
                    <span class="status-dot online" title="Online"></span>
                </button>
                <div class="user-menu-panel" id="user-menu-panel" style="display: none;">
                    <div class="user-info">
                        <strong><?php echo e($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></strong>
                        <small><?php echo e(ucfirst($currentRole)); ?></small>
                    </div>
                    <ul>
                        <li><a href="/modules/client/profile.php">
                            <span class="icon">👤</span> Profile
                        </a></li>
                        <li><a href="/modules/client/settings.php">
                            <span class="icon">⚙️</span> Settings
                        </a></li>
                        <li><a href="/modules/common/help.php">
                            <span class="icon">📚</span> Help & Documentation
                        </a></li>
                        <li><a href="/modules/client/accessibility.php">
                            <span class="icon">♿</span> Accessibility
                        </a></li>
                        <li class="divider"></li>
                        <li><a href="/modules/auth/logout.php">
                            <span class="icon">🚪</span> Logout
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</nav>

<!-- Crisis Quick Access Bar (for clients) -->
<?php if ($currentRole === 'client'): ?>
<div class="crisis-bar">
    <div class="crisis-bar-content">
        <span class="crisis-label">Need Help?</span>
        <a href="tel:988" class="crisis-btn emergency">
            <span class="icon">🆘</span> 988 Crisis Line
        </a>
        <a href="/modules/client/peer-support.php" class="crisis-btn">
            <span class="icon">🤝</span> Peer Support
        </a>
        <a href="/modules/client/contact-worker.php" class="crisis-btn">
            <span class="icon">👷</span> Contact Worker
        </a>
    </div>
</div>
<?php endif; ?>
