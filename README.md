# 🌐 OUTSINC - Comprehensive Outreach & Case Management Platform

A trauma-informed, multi-role web application designed for clients, outreach workers, service providers, and administrators. Built with PHP, MySQL, HTML, CSS, JavaScript, and AJAX for security, customization, and long-term system resilience.

## 🚀 Features

### Core Framework
- **Authentication & Access Control** - Secure login, registration, logout, and password reset (CSRF protected, passwords hashed)
- **Role-Based System** - Clients activate instantly; Workers and Service Providers require admin approval
- **Shared Components** - Modular header, footer, and navbar with dynamic navigation, theme toggles, and live status icons
- **Responsive UI/UX** - Fully mobile-responsive with transitions, animations, and sound feedback
- **Session Management** - Encrypted PHP sessions, automatic regeneration, and full audit logging

### Client Tools
- Personal Dashboard with mood check-ins and progress tracking
- Smart Intake & Progress Tracker
- Crisis Quick-Access Bar for emergency contacts
- Service Directory & Map (filterable, location-based)
- Goal-Setting System with progress meters
- Anonymous Journal
- Medication & Appointment Reminders
- Milestones & Badges (gamification)
- Accessibility Modes (dyslexia font, large text, calm colors)

### Worker & Outreach Tools
- Case Management Dashboard
- Incident & Outreach Logs with GPS pinning
- Secure Messaging Hub
- Harm Reduction Supply Tracker
- Resource Library
- Client Heatmap
- Safety Check-Ins
- Shift Handover Notes

### Admin Tools & Diagnostics
- User & Role Management
- Approvals Dashboard
- System Health Monitor
- Usage Dashboard
- Error Log Viewer
- Database Repair Tools
- Backup & Restore Utility
- Security Audit

### Communication & Collaboration
- Facebook-Style Messenger with online status
- Group & Peer Chats
- File & Image Sharing
- Announcements & Alerts
- Internal Forum

## 📋 Requirements

- **PHP** 8.0 or higher
- **MySQL** 5.7+ or MariaDB 10.3+
- **Web Server** Apache or Nginx
- **PHP Extensions:**
  - PDO
  - pdo_mysql
  - mbstring
  - openssl
  - json
  - session

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/acesonder/Oct26.git
cd Oct26
```

### 2. Configure Database

1. Create a new MySQL database:
```sql
CREATE DATABASE outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:
```bash
mysql -u your_username -p outsinc_db < sql/schema.sql
```

3. Update database credentials in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'outsinc_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 3. Configure Application

1. Update application settings in `config/config.php`:
```php
define('APP_URL', 'http://your-domain.com');
define('TIMEZONE', 'America/New_York');
```

2. Set up file permissions:
```bash
chmod 755 uploads/
chmod 755 logs/
```

3. Configure your web server to point to the application root directory

### 4. Default Admin Account

**Username:** `admin`  
**Email:** `admin@outsinc.local`  
**Password:** `admin123`

⚠️ **IMPORTANT:** Change the default admin password immediately after first login!

## 🔐 Security Notes

- All passwords are hashed using PHP's `password_hash()` with bcrypt
- CSRF protection is enabled on all forms
- Session data is encrypted and regenerated periodically
- Login attempts are rate-limited with automatic lockout
- All user input is sanitized
- File uploads are validated and stored securely
- Audit logging tracks all critical actions

## 📁 Project Structure

```
Oct26/
├── assets/
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   ├── images/       # Images and icons
│   └── sounds/       # Sound effects
├── config/
│   ├── config.php    # Main configuration
│   └── database.php  # Database connection
├── includes/
│   ├── header.php    # Page header
│   ├── footer.php    # Page footer
│   ├── navbar.php    # Navigation bar
│   └── functions.php # Utility functions
├── modules/
│   ├── auth/         # Authentication (login, register, logout)
│   ├── client/       # Client-specific features
│   ├── worker/       # Worker-specific features
│   ├── admin/        # Admin panel
│   ├── messenger/    # Messaging system
│   └── reports/      # Reporting & analytics
├── sql/
│   └── schema.sql    # Database schema
├── uploads/          # User uploaded files
├── logs/             # Application logs
└── index.php         # Entry point
```

## 🎨 Customization

### Themes

The platform supports multiple themes:
- **Light Theme** (default)
- **Dark Theme**
- **Calm Theme** (reduced motion & contrast)
- **High Contrast Theme**

Users can switch themes using the theme toggle in the navigation bar.

### Accessibility

Built-in accessibility features:
- Keyboard-only navigation
- Screen reader support
- Reduced motion mode (respects `prefers-reduced-motion`)
- Dyslexia-friendly font option
- Adjustable font sizes
- Color-blind friendly palettes

## 🧪 Fine-Tuning

See `followupquestions.md` for 100 yes/no questions to help customize the platform based on your organization's specific needs.

## 📊 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8 + PDO (MySQL) |
| Frontend | HTML 5 / CSS 3 / JavaScript (ES6 + AJAX) |
| Database | MySQL via phpMyAdmin |
| Security | password_hash(), CSRF tokens, audit logs, encrypted sessions |
| UI/UX | Modular responsive design with animations and accessibility focus |

## 🤝 Contributing

This is a comprehensive platform designed for trauma-informed outreach and case management. Contributions are welcome to enhance features, improve security, or add new functionality.

## 📄 License

This project is open source and available for use in community support and outreach programs.

## 🆘 Crisis Resources

- **988** - Suicide & Crisis Lifeline
- **911** - Emergency Services
- **1-800-273-8255** - SAMHSA National Helpline

## 📞 Support

For technical support or questions about the platform, please open an issue in the repository.

---

**Version:** 1.0.0  
**Built with ❤️ for community outreach and support**
