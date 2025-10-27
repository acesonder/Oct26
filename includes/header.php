<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?><?php echo e(APP_NAME); ?></title>
    <meta name="description" content="OUTSINC - Comprehensive Outreach & Case Management Platform">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo e($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    
    <!-- Theme -->
    <script>
        // Load theme preference
        const theme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', theme);
    </script>
    
    <!-- Welcome Tour Scripts -->
    <script src="/assets/js/welcome-tour.js"></script>
    <script src="/assets/js/tour-init.js"></script>
</head>
<body class="<?php echo isset($bodyClass) ? e($bodyClass) : ''; ?>" 
      <?php if (isLoggedIn()): ?>
      data-user-role="<?php echo e($_SESSION['user_role'] ?? 'client'); ?>"
      data-username="<?php echo e($_SESSION['username'] ?? ''); ?>"
      <?php endif; ?>>
    <?php if (isLoggedIn()): ?>
        <?php include __DIR__ . '/navbar.php'; ?>
    <?php endif; ?>
    
    <div id="main-content" class="<?php echo isLoggedIn() ? 'with-navbar' : ''; ?>">
