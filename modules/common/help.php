<?php
/**
 * OUTSINC Platform - Help & Documentation
 * Role-based help and documentation viewer
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$pageTitle = 'Help & Documentation';
$currentUser = getCurrentUser();
$userRole = $currentUser['role'];

// Determine which sections to show based on role
$allowedSections = [];

switch ($userRole) {
    case 'admin':
        // Admin sees all sections
        $allowedSections = [
            'Getting Started',
            'Client Features',
            'Outreach Worker Features',
            'Service Provider Features',
            'Administrator Features',
            'Common Features',
            'Troubleshooting',
            'FAQ'
        ];
        break;
    case 'worker':
        $allowedSections = [
            'Getting Started',
            'Outreach Worker Features',
            'Common Features',
            'Troubleshooting',
            'FAQ'
        ];
        break;
    case 'service_provider':
        $allowedSections = [
            'Getting Started',
            'Service Provider Features',
            'Common Features',
            'Troubleshooting',
            'FAQ'
        ];
        break;
    case 'client':
    default:
        $allowedSections = [
            'Getting Started',
            'Client Features',
            'Common Features',
            'Troubleshooting',
            'FAQ'
        ];
        break;
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container help-container">
    <div class="help-header">
        <h1>📚 Help & Documentation</h1>
        <p class="text-muted">Learn how to use the OUTSINC platform</p>
    </div>
    
    <!-- Search Bar -->
    <div class="help-search">
        <input type="text" id="help-search-input" class="form-control" placeholder="Search documentation...">
    </div>
    
    <!-- Navigation Tabs -->
    <div class="help-nav">
        <?php foreach ($allowedSections as $index => $section): ?>
            <button class="help-nav-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                    onclick="showSection('<?php echo e(str_replace(' ', '-', strtolower($section))); ?>')">
                <?php echo e($section); ?>
            </button>
        <?php endforeach; ?>
    </div>
    
    <!-- Quick Links -->
    <div class="quick-links-section">
        <h3>Quick Links</h3>
        <div class="quick-links-grid">
            <?php if ($userRole === 'client' || $userRole === 'admin'): ?>
                <a href="#client-dashboard" class="quick-link-card">
                    <span class="quick-link-icon">📊</span>
                    <span class="quick-link-title">Dashboard Guide</span>
                </a>
                <a href="#goal-setting" class="quick-link-card">
                    <span class="quick-link-icon">🎯</span>
                    <span class="quick-link-title">Goal Setting</span>
                </a>
                <a href="#journal" class="quick-link-card">
                    <span class="quick-link-icon">📔</span>
                    <span class="quick-link-title">Using Journal</span>
                </a>
            <?php endif; ?>
            
            <?php if ($userRole === 'worker' || $userRole === 'admin'): ?>
                <a href="#case-management" class="quick-link-card">
                    <span class="quick-link-icon">📋</span>
                    <span class="quick-link-title">Case Management</span>
                </a>
                <a href="#safety-check-ins" class="quick-link-card">
                    <span class="quick-link-icon">🛡️</span>
                    <span class="quick-link-title">Safety Check-ins</span>
                </a>
            <?php endif; ?>
            
            <?php if ($userRole === 'admin'): ?>
                <a href="#user-management" class="quick-link-card">
                    <span class="quick-link-icon">👥</span>
                    <span class="quick-link-title">User Management</span>
                </a>
                <a href="#system-health" class="quick-link-card">
                    <span class="quick-link-icon">🔧</span>
                    <span class="quick-link-title">System Health</span>
                </a>
            <?php endif; ?>
            
            <a href="#troubleshooting" class="quick-link-card">
                <span class="quick-link-icon">🔧</span>
                <span class="quick-link-title">Troubleshooting</span>
            </a>
            <a href="#faq" class="quick-link-card">
                <span class="quick-link-icon">❓</span>
                <span class="quick-link-title">FAQ</span>
            </a>
        </div>
    </div>
    
    <!-- Documentation Content -->
    <div class="help-content">
        <?php if ($userRole === 'admin'): ?>
            <div class="alert alert-info">
                <strong>Administrator View:</strong> You can see documentation for all user roles.
            </div>
        <?php endif; ?>
        
        <div class="documentation-viewer" id="documentation-content">
            <!-- Manual content will be loaded here -->
            <div class="loading-state">
                <p>Loading documentation...</p>
            </div>
        </div>
    </div>
    
    <!-- Video Tutorials Section -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🎥 Video Tutorials</h3>
        </div>
        <div class="card-body">
            <p>Coming soon: Step-by-step video tutorials for all features!</p>
        </div>
    </div>
    
    <!-- Contact Support -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">💬 Need More Help?</h3>
        </div>
        <div class="card-body">
            <p>Can't find what you're looking for? Contact support:</p>
            <div class="support-options">
                <a href="/modules/messenger/index.php" class="btn btn-primary">
                    Message Support
                </a>
                <?php if ($userRole === 'admin'): ?>
                    <a href="mailto:admin@outsinc.local" class="btn btn-outline">
                        Email Administrator
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.help-header {
    margin-bottom: var(--spacing-xl);
}

.help-search {
    margin-bottom: var(--spacing-lg);
}

.help-search input {
    max-width: 600px;
    margin: 0 auto;
    display: block;
    padding: 12px 20px;
    font-size: 16px;
}

.help-nav {
    display: flex;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
    overflow-x: auto;
    padding-bottom: var(--spacing-sm);
    border-bottom: 2px solid var(--border-color);
}

.help-nav-btn {
    padding: 10px 20px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-weight: 600;
    white-space: nowrap;
    transition: all var(--transition-fast);
    color: var(--text-secondary);
}

.help-nav-btn:hover {
    color: var(--primary-color);
}

.help-nav-btn.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.quick-links-section {
    margin-bottom: var(--spacing-xl);
}

.quick-links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--spacing-md);
    margin-top: var(--spacing-md);
}

.quick-link-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: var(--spacing-lg);
    background: var(--surface-color);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--text-color);
    transition: all var(--transition-fast);
    text-align: center;
}

.quick-link-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.quick-link-icon {
    font-size: 48px;
    margin-bottom: var(--spacing-sm);
}

.quick-link-title {
    font-weight: 600;
}

.help-content {
    margin-bottom: var(--spacing-xl);
}

.documentation-viewer {
    background: var(--surface-color);
    border-radius: var(--radius-md);
    padding: var(--spacing-xl);
    min-height: 400px;
}

.documentation-viewer h1,
.documentation-viewer h2,
.documentation-viewer h3 {
    color: var(--primary-color);
    margin-top: var(--spacing-lg);
    margin-bottom: var(--spacing-md);
}

.documentation-viewer h1 {
    font-size: 32px;
    border-bottom: 2px solid var(--border-color);
    padding-bottom: var(--spacing-sm);
}

.documentation-viewer h2 {
    font-size: 24px;
}

.documentation-viewer h3 {
    font-size: 20px;
}

.documentation-viewer p {
    line-height: 1.6;
    margin-bottom: var(--spacing-md);
}

.documentation-viewer ul,
.documentation-viewer ol {
    margin-left: var(--spacing-lg);
    margin-bottom: var(--spacing-md);
}

.documentation-viewer li {
    margin-bottom: var(--spacing-sm);
}

.documentation-viewer code {
    background: var(--bg-color);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    font-family: monospace;
}

.documentation-viewer pre {
    background: var(--bg-color);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    overflow-x: auto;
}

.screenshot-placeholder {
    background: var(--bg-color);
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-md);
    padding: var(--spacing-xl);
    text-align: center;
    margin: var(--spacing-md) 0;
    color: var(--text-secondary);
}

.loading-state {
    text-align: center;
    padding: var(--spacing-xl);
    color: var(--text-secondary);
}

.support-options {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-md);
}
</style>

<script>
// Load manual content
document.addEventListener('DOMContentLoaded', () => {
    loadManualContent();
    setupSearch();
});

function loadManualContent() {
    const userRole = '<?php echo e($userRole); ?>';
    const allowedSections = <?php echo json_encode($allowedSections); ?>;
    
    // Fetch manual content
    fetch('/MANUAL.md')
        .then(response => response.text())
        .then(content => {
            // Parse and filter content based on role
            const filtered = filterContentByRole(content, allowedSections);
            displayContent(filtered);
        })
        .catch(error => {
            console.error('Error loading manual:', error);
            document.getElementById('documentation-content').innerHTML = 
                '<p class="text-danger">Error loading documentation. Please try again later.</p>';
        });
}

function filterContentByRole(content, allowedSections) {
    // Simple filtering - in production, use a proper markdown parser
    const sections = content.split('## ');
    let filtered = sections[0]; // Keep intro
    
    sections.slice(1).forEach(section => {
        const sectionTitle = section.split('\n')[0];
        if (allowedSections.some(allowed => sectionTitle.includes(allowed))) {
            filtered += '## ' + section;
        }
    });
    
    return filtered;
}

function displayContent(content) {
    // Convert markdown to HTML (simple conversion)
    let html = content;
    
    // Convert screenshots to placeholders
    html = html.replace(/\*\*Screenshot\*\*: `([^`]+)`/g, 
        '<div class="screenshot-placeholder">📸 Screenshot: $1</div>');
    
    // Convert headers
    html = html.replace(/^### (.+)$/gm, '<h3>$1</h3>');
    html = html.replace(/^## (.+)$/gm, '<h2>$1</h2>');
    html = html.replace(/^# (.+)$/gm, '<h1>$1</h1>');
    
    // Convert bold
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    
    // Convert links
    html = html.replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2">$1</a>');
    
    // Convert line breaks
    html = html.replace(/\n\n/g, '</p><p>');
    html = '<p>' + html + '</p>';
    
    document.getElementById('documentation-content').innerHTML = html;
}

function setupSearch() {
    const searchInput = document.getElementById('help-search-input');
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        const content = document.getElementById('documentation-content');
        
        if (query.length < 3) {
            // Reset highlighting
            return;
        }
        
        // Simple search highlighting
        // In production, use a proper search library
        const text = content.textContent;
        if (text.toLowerCase().includes(query)) {
            // Scroll to first match
            const firstMatch = content.querySelector(`*:contains("${query}")`);
            if (firstMatch) {
                firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
}

function showSection(sectionId) {
    // Remove active class from all buttons
    document.querySelectorAll('.help-nav-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked button
    event.target.classList.add('active');
    
    // Scroll to section
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
