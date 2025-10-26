<?php
/**
 * OUTSINC Platform - Client Journal
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAnyRole(['client', 'worker', 'service_provider']);

$pageTitle = 'My Journal';
$error = '';
$success = '';

// Handle journal entry creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $entryText = sanitizeInput($_POST['entry_text'] ?? '');
        $mood = sanitizeInput($_POST['mood'] ?? '');
        
        if (!empty($entryText)) {
            try {
                $pdo = getDatabaseConnection();
                $stmt = $pdo->prepare("
                    INSERT INTO journal_entries (client_id, entry_text, mood, is_private)
                    VALUES (?, ?, ?, TRUE)
                ");
                $stmt->execute([getCurrentUserId(), $entryText, $mood]);
                
                logAudit('journal_entry_created', 'journal_entries', $pdo->lastInsertId());
                $success = 'Journal entry saved successfully!';
                playSound('success');
            } catch (Exception $e) {
                error_log("Journal entry error: " . $e->getMessage());
                $error = 'Failed to save journal entry. Please try again.';
            }
        } else {
            $error = 'Please write something in your journal.';
        }
    }
}

// Get journal entries
try {
    $pdo = getDatabaseConnection();
    
    $stmt = $pdo->prepare("
        SELECT * FROM journal_entries 
        WHERE client_id = ? 
        ORDER BY created_at DESC
        LIMIT 50
    ");
    $stmt->execute([getCurrentUserId()]);
    $entries = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Journal fetch error: " . $e->getMessage());
    $entries = [];
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container journal-container">
    <div class="journal-header">
        <h1>📔 My Private Journal</h1>
        <p class="text-muted">Your safe space to reflect, process, and track your journey. All entries are private.</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>
    
    <!-- New Entry Form -->
    <div class="card journal-form-card">
        <div class="card-header">
            <h3 class="card-title">✍️ New Entry</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="mood" class="form-label">How are you feeling?</label>
                    <div class="mood-buttons">
                        <input type="radio" name="mood" value="great" id="mood-great">
                        <label for="mood-great" class="mood-label">😄 Great</label>
                        
                        <input type="radio" name="mood" value="good" id="mood-good">
                        <label for="mood-good" class="mood-label">🙂 Good</label>
                        
                        <input type="radio" name="mood" value="okay" id="mood-okay" checked>
                        <label for="mood-okay" class="mood-label">😐 Okay</label>
                        
                        <input type="radio" name="mood" value="not_great" id="mood-not-great">
                        <label for="mood-not-great" class="mood-label">😞 Not Great</label>
                        
                        <input type="radio" name="mood" value="struggling" id="mood-struggling">
                        <label for="mood-struggling" class="mood-label">😢 Struggling</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="entry_text" class="form-label">What's on your mind?</label>
                    <textarea 
                        id="entry_text" 
                        name="entry_text" 
                        class="form-control journal-textarea" 
                        rows="8" 
                        placeholder="Write freely... This is your safe space."
                        required
                    ></textarea>
                    <span class="form-help">This entry is private and only visible to you.</span>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Save Entry</button>
                    <button type="reset" class="btn btn-outline">Clear</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Grounding Prompt -->
    <div class="grounding-card">
        <p><em>"Take a moment to breathe. You're doing great by taking time to reflect."</em> 🌟</p>
    </div>
    
    <!-- Previous Entries -->
    <div class="entries-section">
        <h2>Previous Entries</h2>
        
        <?php if (count($entries) > 0): ?>
            <div class="entries-list">
                <?php foreach ($entries as $entry): ?>
                    <div class="entry-card">
                        <div class="entry-header">
                            <div class="entry-mood">
                                <?php
                                $moodEmojis = [
                                    'great' => '😄',
                                    'good' => '🙂',
                                    'okay' => '😐',
                                    'not_great' => '😞',
                                    'struggling' => '😢'
                                ];
                                echo $moodEmojis[$entry['mood']] ?? '😐';
                                ?>
                                <span><?php echo ucwords(str_replace('_', ' ', $entry['mood'])); ?></span>
                            </div>
                            <div class="entry-date">
                                <?php echo formatDateTime($entry['created_at'], 'M d, Y g:i A'); ?>
                            </div>
                        </div>
                        <div class="entry-content">
                            <?php echo nl2br(e($entry['entry_text'])); ?>
                        </div>
                        <div class="entry-footer">
                            <small class="text-muted">
                                <?php echo timeAgo($entry['created_at']); ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <h3>No journal entries yet</h3>
                    <p>Start writing to create your first entry!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.journal-container {
    max-width: 900px;
    margin: 0 auto;
}

.journal-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.journal-form-card {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border: none;
}

.mood-buttons {
    display: flex;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.mood-buttons input[type="radio"] {
    display: none;
}

.mood-label {
    padding: var(--spacing-sm) var(--spacing-md);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    background-color: var(--surface-color);
    cursor: pointer;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

.mood-buttons input[type="radio"]:checked + .mood-label {
    border-color: var(--primary-color);
    background-color: var(--primary-color);
    color: white;
}

.mood-label:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

.journal-textarea {
    font-size: var(--font-size-base);
    line-height: 1.8;
    resize: vertical;
    min-height: 200px;
}

.grounding-card {
    background: linear-gradient(135deg, var(--secondary-color), #3FAA63);
    color: white;
    padding: var(--spacing-lg);
    border-radius: var(--radius-md);
    text-align: center;
    margin: var(--spacing-xl) 0;
    font-size: var(--font-size-large);
}

.entries-section {
    margin-top: var(--spacing-xl);
}

.entries-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.entry-card {
    background-color: var(--surface-color);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    transition: all var(--transition-base);
}

.entry-card:hover {
    box-shadow: var(--shadow-hover);
}

.entry-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-md);
    border-bottom: 1px solid var(--border-color);
}

.entry-mood {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: var(--font-size-large);
    font-weight: 600;
}

.entry-date {
    color: var(--text-secondary);
    font-size: var(--font-size-small);
}

.entry-content {
    line-height: 1.8;
    margin-bottom: var(--spacing-md);
    white-space: pre-wrap;
}

.entry-footer {
    padding-top: var(--spacing-sm);
    border-top: 1px solid var(--border-color);
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
