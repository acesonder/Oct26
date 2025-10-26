/**
 * OUTSINC Platform - Tour Configurations
 * Defines tour steps for different user roles
 */

// Client Tour Steps
const clientTourSteps = [
    {
        element: '.dashboard-header h1',
        title: '👋 Welcome to OUTSINC!',
        content: 'Your unique username is <strong id="tour-username"></strong>. Please write it down and keep it safe for future logins!',
        position: 'bottom'
    },
    {
        element: '.stat-card:nth-child(1)',
        title: '🎯 Your Goals',
        content: 'Track your personal goals here. Set targets and watch your progress grow!',
        position: 'bottom'
    },
    {
        element: '.mood-selector',
        title: '😊 Daily Mood Check-in',
        content: 'Share how you\'re feeling today. This helps us support you better and track your wellness journey.',
        position: 'bottom'
    },
    {
        element: 'a[href="journal.php"]',
        title: '📔 Private Journal',
        content: 'Write down your thoughts privately. Your journal is completely confidential and only you can see it.',
        position: 'right'
    },
    {
        element: 'a[href="goals.php"]',
        title: '🎯 Goal Setting',
        content: 'Create and manage your personal goals. Break them down into steps and track your progress!',
        position: 'right'
    },
    {
        element: 'a[href="services.php"]',
        title: '📍 Find Services',
        content: 'Search for support services in your area. Filter by type and location to find what you need.',
        position: 'right'
    },
    {
        element: '.theme-toggle',
        title: '🌙 Theme Settings',
        content: 'Switch between light and dark themes. Customize your experience for comfort!',
        position: 'left'
    },
    {
        element: '.notification-icon',
        title: '🔔 Notifications',
        content: 'Check your notifications here. Stay updated on messages and important updates.',
        position: 'left'
    }
];

// Worker Tour Steps
const workerTourSteps = [
    {
        element: '.dashboard-header h1',
        title: '👋 Welcome, Outreach Worker!',
        content: 'Your unique username is <strong id="tour-username"></strong>. Keep it safe for future logins!',
        position: 'bottom'
    },
    {
        element: '.stat-card:nth-child(1)',
        title: '📊 Your Cases',
        content: 'View and manage your active cases. Keep track of all clients you\'re supporting.',
        position: 'bottom'
    },
    {
        element: 'a[href*="safety"]',
        title: '🛡️ Safety Check-ins',
        content: 'Regular safety check-ins help keep you protected. Use this before and after field visits.',
        position: 'right'
    },
    {
        element: '.case-management',
        title: '📋 Case Management',
        content: 'Manage client cases, add notes, and track progress. All client information is secure and confidential.',
        position: 'bottom'
    },
    {
        element: '.messenger-icon',
        title: '💬 Secure Messaging',
        content: 'Communicate securely with clients and colleagues. Messages are encrypted and private.',
        position: 'left'
    }
];

// Admin Tour Steps
const adminTourSteps = [
    {
        element: '.dashboard-header h1',
        title: '👋 Welcome, Administrator!',
        content: 'Your admin username is <strong id="tour-username"></strong>. Keep it secure!',
        position: 'bottom'
    },
    {
        element: '.stat-card:nth-child(1)',
        title: '👥 User Management',
        content: 'View and manage all platform users. Approve workers, manage roles, and monitor activity.',
        position: 'bottom'
    },
    {
        element: 'a[href*="approvals"]',
        title: '✅ Pending Approvals',
        content: 'Review and approve worker and service provider registrations here.',
        position: 'right'
    },
    {
        element: '.system-health',
        title: '🔧 System Health',
        content: 'Monitor platform performance, check logs, and ensure everything runs smoothly.',
        position: 'bottom'
    },
    {
        element: 'a[href*="audit"]',
        title: '📜 Audit Logs',
        content: 'Review all system activities and user actions for security and compliance.',
        position: 'right'
    }
];

/**
 * Initialize tour for current user
 */
function initializeWelcomeTour() {
    // Get user info from page
    const userRole = document.body.dataset.userRole || 'client';
    const username = document.body.dataset.username || '';

    // Select appropriate tour steps
    let tourSteps;
    switch (userRole) {
        case 'worker':
            tourSteps = workerTourSteps;
            break;
        case 'admin':
            tourSteps = adminTourSteps;
            break;
        case 'service_provider':
            tourSteps = workerTourSteps; // Service providers use worker tour for now
            break;
        default:
            tourSteps = clientTourSteps;
    }

    // Check if user has completed tour
    fetch('/api/tour-progress.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && !data.tour_completed && !data.tour_skipped) {
                // Start tour after a short delay to let page load
                setTimeout(() => {
                    const tour = new WelcomeTour(tourSteps, {
                        onComplete: () => {
                            console.log('Tour completed!');
                        },
                        onSkip: () => {
                            console.log('Tour skipped');
                        }
                    });

                    // Inject username into first step
                    tour.start();
                    setTimeout(() => {
                        const usernameElement = document.getElementById('tour-username');
                        if (usernameElement) {
                            usernameElement.textContent = username;
                        }
                    }, 100);
                }, 1000);
            }
        })
        .catch(err => {
            console.error('Error checking tour status:', err);
        });
}

/**
 * Restart tour from settings
 */
function restartWelcomeTour() {
    if (confirm('This will restart the welcome tour. Continue?')) {
        WelcomeTour.reset();
        location.reload();
    }
}

// Auto-initialize tour on page load
document.addEventListener('DOMContentLoaded', () => {
    // Only run on dashboard pages
    if (window.location.pathname.includes('dashboard.php')) {
        initializeWelcomeTour();
    }
});
