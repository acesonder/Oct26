/**
 * OUTSINC Platform - Main JavaScript
 * Core functionality for UI interactions
 */

// ===== Theme Management =====
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Update theme icon
    const themeIcon = document.querySelector('.theme-toggle .theme-icon');
    if (themeIcon) {
        themeIcon.textContent = newTheme === 'light' ? '🌙' : '☀️';
    }
    
    playSound('success');
}

// ===== Mobile Menu =====
function toggleMobileMenu() {
    const menu = document.getElementById('navbar-menu');
    if (menu) {
        menu.classList.toggle('active');
    }
}

// ===== User Menu =====
function toggleUserMenu() {
    const panel = document.getElementById('user-menu-panel');
    if (panel) {
        const isVisible = panel.style.display !== 'none';
        closeAllDropdowns();
        panel.style.display = isVisible ? 'none' : 'block';
    }
}

// ===== Notifications =====
function toggleNotifications() {
    const panel = document.getElementById('notification-panel');
    if (panel) {
        const isVisible = panel.style.display !== 'none';
        closeAllDropdowns();
        panel.style.display = isVisible ? 'none' : 'block';
        
        if (!isVisible) {
            loadNotifications();
        }
    }
}

function loadNotifications() {
    const notificationList = document.getElementById('notification-list');
    if (!notificationList) return;
    
    // AJAX call to fetch notifications
    fetch('/api/notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.notifications && data.notifications.length > 0) {
                notificationList.innerHTML = data.notifications.map(notif => `
                    <div class="notification-item ${notif.is_read ? '' : 'unread'}">
                        <p><strong>${escapeHtml(notif.title)}</strong></p>
                        <p>${escapeHtml(notif.message)}</p>
                        <small>${escapeHtml(notif.time_ago)}</small>
                    </div>
                `).join('');
            } else {
                notificationList.innerHTML = '<p class="no-notifications">No new notifications</p>';
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

// ===== Messenger =====
function toggleMessenger() {
    const panel = document.getElementById('messenger-panel');
    if (panel) {
        const isVisible = panel.style.display !== 'none';
        panel.style.display = isVisible ? 'none' : 'block';
        
        if (!isVisible) {
            loadConversations();
        }
    }
}

function loadConversations() {
    const conversations = document.getElementById('messenger-conversations');
    if (!conversations) return;
    
    // AJAX call to fetch conversations
    fetch('/api/conversations.php')
        .then(response => response.json())
        .then(data => {
            if (data.conversations && data.conversations.length > 0) {
                conversations.innerHTML = data.conversations.map(conv => `
                    <div class="conversation-item" onclick="openConversation(${conv.id})">
                        <div class="conv-avatar">${escapeHtml(conv.initials)}</div>
                        <div class="conv-info">
                            <strong>${escapeHtml(conv.name)}</strong>
                            <p>${escapeHtml(conv.last_message)}</p>
                        </div>
                        ${conv.unread_count > 0 ? `<span class="unread-count">${conv.unread_count}</span>` : ''}
                    </div>
                `).join('');
            } else {
                conversations.innerHTML = '<p class="text-center text-muted">No conversations yet</p>';
            }
        })
        .catch(error => {
            console.error('Error loading conversations:', error);
        });
}

function openConversation(id) {
    window.location.href = `/modules/messenger/conversation.php?id=${id}`;
}

// ===== Close All Dropdowns =====
function closeAllDropdowns() {
    const dropdowns = ['user-menu-panel', 'notification-panel'];
    dropdowns.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.style.display = 'none';
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const userMenuBtn = document.querySelector('.user-menu-btn');
    const notificationBtn = document.querySelector('.notification-btn');
    
    if (!event.target.closest('.user-dropdown')) {
        const panel = document.getElementById('user-menu-panel');
        if (panel) panel.style.display = 'none';
    }
    
    if (!event.target.closest('.notification-dropdown')) {
        const panel = document.getElementById('notification-panel');
        if (panel) panel.style.display = 'none';
    }
});

// ===== Sound Effects =====
function playSound(type) {
    const soundEnabled = localStorage.getItem('sounds_enabled') !== 'false';
    if (!soundEnabled) return;
    
    const soundElement = document.getElementById(`${type}-sound`);
    if (soundElement) {
        soundElement.currentTime = 0;
        soundElement.play().catch(error => {
            console.log('Sound play failed:', error);
        });
    }
}

// ===== Form Validation =====
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// ===== AJAX Helper =====
function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    return fetch(url, options)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
}

// ===== Utility Functions =====
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.getElementById('main-content');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'just now';
    if (diffMins < 60) return `${diffMins} min ago`;
    
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours} hours ago`;
    
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) return `${diffDays} days ago`;
    
    return date.toLocaleDateString();
}

function confirmAction(message) {
    return confirm(message);
}

// ===== Accessibility =====
function increaseFontSize() {
    const currentSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
    document.documentElement.style.fontSize = (currentSize + 2) + 'px';
    localStorage.setItem('font-size', currentSize + 2);
}

function decreaseFontSize() {
    const currentSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
    if (currentSize > 12) {
        document.documentElement.style.fontSize = (currentSize - 2) + 'px';
        localStorage.setItem('font-size', currentSize - 2);
    }
}

function resetFontSize() {
    document.documentElement.style.fontSize = '16px';
    localStorage.setItem('font-size', 16);
}

// ===== Auto-update badges and counters =====
function updateCounts() {
    // Update message count
    fetch('/api/unread-messages.php')
        .then(response => response.json())
        .then(data => {
            const messageBadge = document.getElementById('message-count');
            const messengerBadge = document.querySelector('.messenger-float .unread-badge');
            
            if (data.count > 0) {
                if (messageBadge) {
                    messageBadge.textContent = data.count;
                    messageBadge.style.display = 'inline';
                }
                if (messengerBadge) {
                    messengerBadge.textContent = data.count;
                    messengerBadge.style.display = 'inline';
                }
            } else {
                if (messageBadge) messageBadge.style.display = 'none';
                if (messengerBadge) messengerBadge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error updating message count:', error));
    
    // Update notification count
    fetch('/api/unread-notifications.php')
        .then(response => response.json())
        .then(data => {
            const notificationBadge = document.getElementById('notification-count');
            if (data.count > 0 && notificationBadge) {
                notificationBadge.textContent = data.count;
                notificationBadge.style.display = 'inline';
            } else if (notificationBadge) {
                notificationBadge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error updating notification count:', error));
}

// ===== Initialize on page load =====
document.addEventListener('DOMContentLoaded', function() {
    // Load saved font size
    const savedFontSize = localStorage.getItem('font-size');
    if (savedFontSize) {
        document.documentElement.style.fontSize = savedFontSize + 'px';
    }
    
    // Load theme
    const theme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', theme);
    
    const themeIcon = document.querySelector('.theme-toggle .theme-icon');
    if (themeIcon) {
        themeIcon.textContent = theme === 'light' ? '🌙' : '☀️';
    }
    
    // Update counts every 30 seconds
    if (document.querySelector('.navbar')) {
        updateCounts();
        setInterval(updateCounts, 30000);
    }
    
    // Add smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});

// ===== Export functions for use in other scripts =====
window.OUTSINC = {
    toggleTheme,
    toggleMobileMenu,
    toggleUserMenu,
    toggleNotifications,
    toggleMessenger,
    playSound,
    showAlert,
    ajaxRequest,
    escapeHtml,
    formatDate,
    confirmAction,
    increaseFontSize,
    decreaseFontSize,
    resetFontSize
};
