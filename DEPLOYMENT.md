# 🚀 Deployment Guide — DJ Booking Platform

---

## 📋 Requirements

| Requirement          | Minimum Version |
|----------------------|-----------------|
| PHP                  | 8.0+            |
| MySQL                | 5.7+ / MariaDB 10.3+ |
| Apache               | 2.4+ (with mod_rewrite) |
| PHP Extensions       | PDO, PDO_MySQL, fileinfo, mbstring, openssl |

---

## 💻 Local Development (XAMPP/WAMP/Laragon)

### Step 1: Download & Place Files

```bash
git clone https://github.com/kojo952/dj-booking-platform.git
# Copy the folder to your web server root:
# XAMPP: C:/xampp/htdocs/dj-booking-platform/
# WAMP:  C:/wamp64/www/dj-booking-platform/
# Mac:   /Applications/XAMPP/xamppfiles/htdocs/dj-booking-platform/
```

### Step 2: Enable mod_rewrite (Apache)

In `httpd.conf`:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
<Directory "htdocs">
    AllowOverride All
</Directory>
```

### Step 3: Database Setup

1. Start Apache + MySQL in XAMPP/WAMP control panel
2. Open `http://localhost/phpmyadmin`
3. Click **Import** > Choose `database/schema.sql` > **Go**

Or via CLI:
```bash
mysql -u root -p < database/schema.sql
```

### Step 4: Configure

Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dj_booking_platform');
define('DB_USER', 'root');      // Your MySQL username
define('DB_PASS', '');          // Your MySQL password (blank for XAMPP default)
```

Edit `config/config.php`:
```php
define('SITE_URL', 'http://localhost/dj-booking-platform');
define('SITE_NAME', 'DJ KoJo');
define('ADMIN_EMAIL', 'your@email.com');
```

### Step 5: Access the site

- **Public site**: `http://localhost/dj-booking-platform/`
- **Admin panel**: `http://localhost/dj-booking-platform/login.php`
- **Credentials**: admin / Admin@1234

---

## 🌐 VPS / Shared Hosting Deployment

### Step 1: Upload Files

Upload all files to your hosting's public directory using FTP/SFTP:
- cPanel: `/public_html/` or `/public_html/dj-booking-platform/`
- VPS: `/var/www/html/` or `/var/www/html/your-domain.com/`

### Step 2: Database Setup

1. Create a MySQL database and user in cPanel/Plesk
2. Import `database/schema.sql` via phpMyAdmin

### Step 3: Configure

Update `config/database.php` with your hosting database credentials:
```php
define('DB_HOST', 'localhost');  // Usually localhost on shared hosting
define('DB_NAME', 'your_cpanel_username_dbname');
define('DB_USER', 'your_cpanel_username_dbuser');
define('DB_PASS', 'your_secure_password');
```

Update `config/config.php`:
```php
define('SITE_URL', 'https://your-domain.com');
// Turn off error display in production:
error_reporting(0);
ini_set('display_errors', 0);
```

### Step 4: File Permissions

```bash
# Set directories writable for uploads
chmod 755 uploads/
chmod 755 uploads/music/
chmod 755 uploads/profile/

# Protect config directory
chmod 750 config/
```

### Step 5: SSL/HTTPS

1. Install an SSL certificate (free with Let's Encrypt via cPanel)
2. In `config/auth.php`, enable secure cookies:
   ```php
   'secure' => true,  // Was false for local dev
   ```
3. Update `SITE_URL` in `config/config.php` to use `https://`

---

## 🔧 PHP Extension Verification

Run this in a temporary `phpinfo.php` file:
```php
<?php phpinfo(); ?>
```

Check for these extensions:
- ✅ PDO
- ✅ pdo_mysql
- ✅ fileinfo
- ✅ mbstring
- ✅ openssl
- ✅ GD (for image processing)

---

## 📁 File Permissions Reference

```
dj-booking-platform/
├── uploads/          # chmod 755 (writable by web server)
│   ├── music/        # chmod 755
│   └── profile/      # chmod 755
├── config/           # chmod 750 (not web-accessible)
└── (other files)     # chmod 644
```

---

## 🔒 Post-Deployment Security Checklist

- [ ] Change admin password from `Admin@1234` to a strong unique password
- [ ] Set `error_reporting(0)` and `ini_set('display_errors', 0)` in production
- [ ] Set `'secure' => true` in session cookie params (HTTPS)
- [ ] Change `ADMIN_EMAIL` to your real email address
- [ ] Verify `.htaccess` is being processed (test by accessing `config/` directly — should get 403)
- [ ] Set up regular database backups

---

## 🐛 Troubleshooting

### "Database connection failed"
- Check `config/database.php` credentials
- Ensure MySQL service is running
- Verify the database and user exist

### "500 Internal Server Error"
- Check Apache `error.log`
- Ensure `mod_rewrite` is enabled
- Check `AllowOverride All` in Apache config

### File uploads not working
- Check `uploads/` directory exists and is writable
- Verify PHP `upload_max_filesize` and `post_max_size` settings
- Check disk space

### Admin login not working
- The default password is `Admin@1234` (capital A)
- Check session is starting correctly
- Ensure cookies are enabled in your browser

---

## 📧 Email Configuration

The platform uses PHP's `mail()` function for booking confirmations. For production, configure a proper SMTP server.

For better email delivery, consider integrating PHPMailer:

```bash
composer require phpmailer/phpmailer
```

Then update `api/booking.php` to use PHPMailer with SMTP credentials.

---

## 🔄 Updates

To update the platform:
```bash
git pull origin main
# Re-import any new SQL migrations (if provided)
```
