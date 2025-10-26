<?php
/**
 * OUTSINC Platform - Home/Index Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect to appropriate dashboard if logged in
if (isLoggedIn()) {
    $role = getCurrentUserRole();
    switch ($role) {
        case 'admin':
            redirect('/modules/admin/dashboard.php');
        case 'worker':
            redirect('/modules/worker/dashboard.php');
        case 'service_provider':
            redirect('/modules/client/dashboard.php');
        default:
            redirect('/modules/client/dashboard.php');
    }
}

// Otherwise redirect to login
redirect('/modules/auth/login.php');
?>
