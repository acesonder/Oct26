# OUTSINC Platform - Installation Guide

## Prerequisites

Before installing OUTSINC, ensure your server meets these requirements:

- **PHP** 8.0 or higher
- **MySQL** 5.7+ or MariaDB 10.3+
- **Web Server** Apache 2.4+ or Nginx 1.18+
- **PHP Extensions:**
  - PDO
  - pdo_mysql
  - mbstring
  - openssl
  - json
  - session
  - curl (optional, for external API integrations)

## Step-by-Step Installation

### 1. Download and Extract

```bash
# Clone from repository
git clone https://github.com/acesonder/Oct26.git outsinc
cd outsinc

# Or download and extract ZIP file
unzip outsinc-main.zip
cd outsinc-main
```

### 2. Set Up Database

#### Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'outsinc_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON outsinc_db.* TO 'outsinc_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Import Schema

```bash
mysql -u outsinc_user -p outsinc_db < sql/schema.sql
```

### 3. Configure Application

#### Database Configuration

Copy the example config file and update with your credentials:

```bash
cp config/database.php.example config/database.php
nano config/database.php
```

Update these values:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'outsinc_db');
define('DB_USER', 'outsinc_user');
define('DB_PASS', 'your_secure_password');
```

#### Application Configuration

Edit `config/config.php`:

```php
define('APP_URL', 'https://your-domain.com');
define('TIMEZONE', 'America/New_York'); // Your timezone
```

### 4. Set File Permissions

```bash
# Make uploads and logs directories writable
chmod 755 uploads/
chmod 755 logs/

# For Apache on Linux
chown -R www-data:www-data uploads/
chown -R www-data:www-data logs/

# Protect config files
chmod 640 config/config.php
chmod 640 config/database.php
```

### 5. Configure Web Server

#### Apache (.htaccess)

Create `.htaccess` in the root directory:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect to HTTPS (if SSL is enabled)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Protect config and sensitive files
    <FilesMatch "^(config|includes|sql)/">
        Require all denied
    </FilesMatch>
</IfModule>

# Disable directory listing
Options -Indexes

# Protect .git directory
<DirectoryMatch "\.git">
    Require all denied
</DirectoryMatch>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# PHP settings
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 60
    php_value session.cookie_httponly 1
</IfModule>
```

#### Nginx

Add to your Nginx server block:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/outsinc;
    index index.php index.html;

    # Security headers
    add_header X-Content-Type-Options "nosniff";
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /(config|sql|logs)/  {
        deny all;
    }

    location ~ /\.git {
        deny all;
    }

    # File upload limits
    client_max_body_size 10M;
}
```

### 6. Test Installation

1. Navigate to your domain in a web browser
2. You should be redirected to the login page
3. Use default admin credentials (change immediately!):
   - **Username:** admin
   - **Email:** admin@outsinc.local
   - **Password:** admin123

### 7. Post-Installation Security

#### Change Default Admin Password

1. Login as admin
2. Go to Profile → Settings
3. Change password immediately

#### Update Security Settings

Edit `config/config.php` for production:

```php
// Disable error display in production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Enable HTTPS cookie security
ini_set('session.cookie_secure', 1); // Only if using HTTPS
```

#### Set Up SSL/HTTPS

We strongly recommend using SSL/HTTPS in production:

```bash
# Using Let's Encrypt (Certbot)
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

### 8. Optional Configuration

#### Email Settings (for notifications)

Add to `config/config.php`:

```php
// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM', 'noreply@your-domain.com');
define('SMTP_FROM_NAME', 'OUTSINC Platform');
```

#### Cron Jobs (for scheduled tasks)

Add to crontab:

```bash
# Backup database daily at 2 AM
0 2 * * * /usr/bin/php /var/www/outsinc/scripts/backup.php

# Clean old logs weekly
0 0 * * 0 /usr/bin/php /var/www/outsinc/scripts/cleanup.php
```

### 9. Populate Sample Data (Optional)

If you want to test with sample data:

```bash
mysql -u outsinc_user -p outsinc_db < sql/sample_data.sql
```

## Troubleshooting

### Database Connection Errors

- Verify database credentials in `config/database.php`
- Ensure MySQL service is running: `sudo systemctl status mysql`
- Check MySQL user permissions

### File Upload Issues

- Check upload directory permissions: `ls -la uploads/`
- Verify PHP upload settings in `php.ini`
- Check web server error logs

### Session Problems

- Ensure sessions directory is writable
- Check PHP session configuration
- Clear browser cookies and cache

### Permission Denied Errors

```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/outsinc

# Fix permissions
sudo find /var/www/outsinc -type d -exec chmod 755 {} \;
sudo find /var/www/outsinc -type f -exec chmod 644 {} \;
sudo chmod 755 uploads/ logs/
```

## Updating

To update to a new version:

```bash
# Backup database first
mysqldump -u outsinc_user -p outsinc_db > backup_$(date +%Y%m%d).sql

# Backup files
tar -czf outsinc_backup_$(date +%Y%m%d).tar.gz /var/www/outsinc

# Pull updates
git pull origin main

# Run any database migrations
mysql -u outsinc_user -p outsinc_db < sql/migrations/update_vX.X.sql

# Clear cache if applicable
rm -rf tmp/cache/*
```

## Getting Help

- Check documentation: `/docs/`
- Review logs: `logs/error.log`
- GitHub Issues: https://github.com/acesonder/Oct26/issues

## Security Checklist

- [ ] Changed default admin password
- [ ] Updated database credentials
- [ ] Set secure file permissions
- [ ] Enabled HTTPS/SSL
- [ ] Configured firewall rules
- [ ] Set up automated backups
- [ ] Reviewed error logging
- [ ] Tested all user roles
- [ ] Configured email notifications
- [ ] Set up monitoring/alerts

---

**Installation Complete!** 🎉

Your OUTSINC platform is now ready to use. Visit the admin dashboard to configure additional settings and create user accounts.
