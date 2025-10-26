<?php
/**
 * OUTSINC Platform - Services Directory
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$pageTitle = 'Services Directory';

// Get filter parameters
$category = sanitizeInput($_GET['category'] ?? '');
$search = sanitizeInput($_GET['search'] ?? '');

// Get services
try {
    $pdo = getDatabaseConnection();
    
    $sql = "SELECT * FROM services WHERE is_active = TRUE";
    $params = [];
    
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($search) {
        $sql .= " AND (service_name LIKE ? OR description LIKE ? OR provider_name LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " ORDER BY service_name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();
    
    // Get all categories
    $stmt = $pdo->query("SELECT DISTINCT category FROM services WHERE is_active = TRUE AND category IS NOT NULL ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    error_log("Services fetch error: " . $e->getMessage());
    $services = [];
    $categories = [];
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container services-container">
    <div class="page-header">
        <h1>📍 Services Directory</h1>
        <p class="text-muted">Find support services, resources, and assistance in your area</p>
    </div>
    
    <!-- Search and Filter -->
    <div class="card search-card">
        <div class="card-body">
            <form method="GET" action="" class="search-form">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="search" class="form-label">Search Services</label>
                            <input 
                                type="text" 
                                id="search" 
                                name="search" 
                                class="form-control" 
                                placeholder="Search by name, description, or provider..."
                                value="<?php echo e($search); ?>"
                            >
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="category" class="form-label">Category</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo e($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">🔍 Search</button>
                    <a href="services.php" class="btn btn-outline">Clear</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Services List -->
    <div class="services-results">
        <p class="results-count">
            Found <strong><?php echo count($services); ?></strong> service(s)
        </p>
        
        <?php if (count($services) > 0): ?>
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <div class="service-header">
                            <h3><?php echo e($service['service_name']); ?></h3>
                            <?php if ($service['is_verified']): ?>
                                <span class="verified-badge" title="Verified Service">✓</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($service['category']): ?>
                            <span class="service-category"><?php echo ucfirst($service['category']); ?></span>
                        <?php endif; ?>
                        
                        <?php if ($service['provider_name']): ?>
                            <p class="service-provider">
                                <strong>Provider:</strong> <?php echo e($service['provider_name']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($service['description']): ?>
                            <p class="service-description">
                                <?php echo e(substr($service['description'], 0, 150)); ?>
                                <?php echo strlen($service['description']) > 150 ? '...' : ''; ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="service-contact">
                            <?php if ($service['address']): ?>
                                <p class="contact-item">
                                    📍 <?php echo e($service['address']); ?>
                                    <?php if ($service['city']): ?>
                                        , <?php echo e($service['city']); ?>
                                        <?php if ($service['state']): ?>
                                            , <?php echo e($service['state']); ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($service['phone']): ?>
                                <p class="contact-item">
                                    📞 <a href="tel:<?php echo e($service['phone']); ?>"><?php echo e($service['phone']); ?></a>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($service['email']): ?>
                                <p class="contact-item">
                                    📧 <a href="mailto:<?php echo e($service['email']); ?>"><?php echo e($service['email']); ?></a>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($service['website']): ?>
                                <p class="contact-item">
                                    🌐 <a href="<?php echo e($service['website']); ?>" target="_blank">Visit Website</a>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($service['hours_of_operation']): ?>
                            <div class="service-hours">
                                <strong>Hours:</strong> <?php echo e($service['hours_of_operation']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($service['latitude'] && $service['longitude']): ?>
                            <a 
                                href="https://www.google.com/maps?q=<?php echo e($service['latitude']); ?>,<?php echo e($service['longitude']); ?>" 
                                target="_blank" 
                                class="btn btn-sm btn-outline"
                            >
                                📍 Get Directions
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <h3>No services found</h3>
                    <p>Try adjusting your search criteria or category filter.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Crisis Resources -->
    <div class="crisis-resources">
        <h3>🆘 Immediate Help</h3>
        <div class="crisis-grid">
            <div class="crisis-resource">
                <h4>988 Suicide & Crisis Lifeline</h4>
                <p>24/7 support for people in distress</p>
                <a href="tel:988" class="btn btn-danger">Call 988</a>
            </div>
            <div class="crisis-resource">
                <h4>Emergency Services</h4>
                <p>For immediate life-threatening situations</p>
                <a href="tel:911" class="btn btn-danger">Call 911</a>
            </div>
            <div class="crisis-resource">
                <h4>SAMHSA Helpline</h4>
                <p>Mental health & substance abuse support</p>
                <a href="tel:18002738255" class="btn btn-primary">1-800-273-8255</a>
            </div>
        </div>
    </div>
</div>

<style>
.search-card {
    margin-bottom: var(--spacing-xl);
}

.search-form .form-actions {
    display: flex;
    gap: var(--spacing-md);
}

.results-count {
    margin-bottom: var(--spacing-lg);
    font-size: var(--font-size-large);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--spacing-lg);
}

.service-card {
    background-color: var(--surface-color);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    transition: all var(--transition-base);
}

.service-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
}

.service-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-sm);
}

.service-header h3 {
    margin: 0;
    font-size: var(--font-size-h4);
    color: var(--primary-color);
}

.verified-badge {
    background-color: var(--success-color);
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.service-category {
    display: inline-block;
    padding: 4px 12px;
    background-color: var(--primary-color);
    color: white;
    border-radius: var(--radius-full);
    font-size: 12px;
    margin-bottom: var(--spacing-md);
}

.service-provider {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-sm);
}

.service-description {
    margin: var(--spacing-md) 0;
    line-height: 1.6;
}

.service-contact {
    background-color: var(--bg-color);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    margin: var(--spacing-md) 0;
}

.contact-item {
    margin-bottom: var(--spacing-xs);
}

.contact-item a {
    color: var(--primary-color);
    text-decoration: none;
}

.contact-item a:hover {
    text-decoration: underline;
}

.service-hours {
    margin: var(--spacing-md) 0;
    padding: var(--spacing-sm);
    background-color: var(--bg-color);
    border-radius: var(--radius-md);
    font-size: var(--font-size-small);
}

.crisis-resources {
    margin-top: var(--spacing-xl);
    padding-top: var(--spacing-xl);
    border-top: 2px solid var(--border-color);
}

.crisis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-md);
}

.crisis-resource {
    background: linear-gradient(135deg, #FFEBEE, #FFCDD2);
    border: 2px solid var(--danger-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    text-align: center;
}

.crisis-resource h4 {
    color: var(--danger-color);
    margin-bottom: var(--spacing-sm);
}

.crisis-resource p {
    margin-bottom: var(--spacing-md);
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
