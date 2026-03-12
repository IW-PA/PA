# Budgie Admin System Documentation

## Overview

This comprehensive admin system provides secure administrative control over the Budgie personal finance application. The system follows MVC architecture and implements security best practices including role-based access control, CSRF protection, and activity logging.

## Database Schema

### Users Table Updates

```sql
-- New columns added to users table:
role ENUM('user', 'admin') NOT NULL DEFAULT 'user'
status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
```

### Admin Activity Log Table

```sql
CREATE TABLE admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id INT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Installation

### 1. Run Database Migration

```bash
mysql -u root -p budgie_db < database/migrations/add_admin_role.sql
```

This will:
- Add `role` and `status` columns to the users table
- Create admin activity log table
- Create first admin user (email: admin@budgie.com, password: admin123)
- Add necessary indexes for performance

### 2. Verify File Structure

Ensure the following files are in place:

```
src/
├── middleware/
│   └── AdminGuard.php          # Authentication & authorization middleware
├── services/
│   └── AdminService.php        # Business logic for admin operations
└── views/
    └── errors/
        └── 403.php             # Forbidden error page

public/
├── admin.php                    # Admin dashboard
└── actions/
    ├── admin_activate_user.php
    ├── admin_deactivate_user.php
    ├── admin_promote_user.php
    └── admin_demote_user.php
```

### 3. Set Environment Permissions

Ensure PHP can write to the database and session directories.

## Security Features

### 1. AdminGuard Middleware

The `AdminGuard` class provides:
- **Role verification**: Checks if user has admin role
- **Status checking**: Ensures user account is active
- **CSRF protection**: Validates tokens for all admin actions
- **Activity logging**: Tracks all administrative actions
- **403 handling**: Custom forbidden page for unauthorized access

Usage:
```php
// Require admin access
AdminGuard::requireAdmin();

// Verify CSRF token
AdminGuard::requireCsrfToken();

// Log admin activity
AdminGuard::logActivity('deactivate_user', 'user', $userId, 'User deactivated');
```

### 2. CSRF Protection

All admin actions require valid CSRF tokens:

```php
<!-- In forms -->
<?php echo CSRFProtection::getTokenField(); ?>

<!-- In action handlers -->
AdminGuard::requireCsrfToken();
```

### 3. SQL Injection Prevention

All queries use PDO prepared statements:

```php
$user = fetchOne(
    "SELECT * FROM users WHERE id = ?",
    [$userId]
);
```

### 4. XSS Prevention

All user input is sanitized and escaped:

```php
echo htmlspecialchars($user['name']);
```

## Admin Dashboard Features

### Global Statistics

The dashboard displays:
- Total users
- Active/inactive users
- Free vs Premium users
- Admin count
- Total accounts, expenses, and incomes
- New users in last 30 days
- Active users in last 7 days

### User Management

Admins can:
1. **View all users** with filtering by:
   - Role (user/admin)
   - Status (active/inactive)
   - Subscription type (free/premium)
   - Search by name or email

2. **Manage user accounts**:
   - Activate/Deactivate users
   - Promote users to admin
   - Demote admins to users
   - View user details and statistics

### Activity Logging

All admin actions are logged with:
- Admin who performed the action
- Action type
- Target user/resource
- Timestamp
- IP address
- Additional details

### Safeguards

- **Cannot self-deactivate**: Admins cannot deactivate their own account
- **Cannot self-demote**: Admins cannot remove their own admin role
- **Last admin protection**: Cannot demote the last admin
- **Active status check**: Deactivated admins lose access immediately

## API Reference

### AdminService Methods

```php
// Get all users with optional filters
AdminService::getAllUsers($filters = [])

// Get global statistics
AdminService::getGlobalStats()

// User management
AdminService::activateUser($userId)
AdminService::deactivateUser($userId)
AdminService::promoteToAdmin($userId)
AdminService::demoteToUser($userId)
AdminService::deleteUser($userId)

// Activity monitoring
AdminService::getRecentActivity($limit = 50)
AdminService::getUserDetails($userId)
```

### AdminGuard Methods

```php
// Access control
AdminGuard::isAdmin()
AdminGuard::isActive()
AdminGuard::requireAdmin($checkActive = true)

// CSRF protection
AdminGuard::verifyCsrfToken($token = null)
AdminGuard::requireCsrfToken()

// Activity logging
AdminGuard::logActivity($action, $targetType, $targetId = null, $details = null)
```

## Docker Deployment

The admin system is fully compatible with Docker/Nginx/PHP-FPM:

### Dockerfile Configuration

```dockerfile
FROM php:8.1-fpm

# Install required extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy application files
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name budgie.local;
    root /var/www/html/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Docker Compose

```yaml
version: '3.8'
services:
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./:/var/www/html
      - ./nginx.conf:/etc/nginx/conf.d/default.conf

  php:
    build: .
    volumes:
      - ./:/var/www/html

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: budgie_db
    volumes:
      - dbdata:/var/lib/mysql

volumes:
  dbdata:
```

## Testing

### Create Test Admin

```sql
INSERT INTO users (first_name, last_name, email, password_hash, role, status)
VALUES ('Test', 'Admin', 'test@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');
-- Password: password
```

### Test Scenarios

1. **Admin Login**: Login with admin account and verify access to /admin.php
2. **User Login**: Login as regular user and verify redirect from /admin.php
3. **User Management**: Test activate/deactivate/promote/demote functions
4. **CSRF Protection**: Try admin actions without CSRF token
5. **Self-Protection**: Try to deactivate/demote yourself
6. **Activity Logging**: Perform actions and verify they appear in activity log
7. **Deactivated User**: Deactivate a user and verify they cannot login

## Troubleshooting

### Issue: "Access Denied" on admin.php

**Solution**: Verify user has admin role in database:
```sql
SELECT role FROM users WHERE email = 'your@email.com';
UPDATE users SET role = 'admin' WHERE email = 'your@email.com';
```

### Issue: CSRF token validation fails

**Solution**: 
- Ensure session is started before config.php loads
- Check that CSRFProtection class is included
- Verify browser cookies are enabled

### Issue: Admin activity not logging

**Solution**:
- Verify admin_activity_log table exists
- Check database connection
- Review error logs for SQL errors

### Issue: Cannot see Admin link in navigation

**Solution**: The admin link only appears when `$_SESSION['user_role'] === 'admin'`. Check:
```php
var_dump($_SESSION['user_role']); // Should output 'admin'
```

## Security Best Practices

1. **Change default admin password** immediately after installation
2. **Use strong passwords** for all admin accounts
3. **Enable 2FA** (implementation recommended for production)
4. **Regular audits** of admin activity logs
5. **Limit admin accounts** to only necessary personnel
6. **Monitor failed login attempts** in activity logs
7. **Keep PHP and MySQL updated** to latest stable versions
8. **Use HTTPS** in production environments
9. **Set proper file permissions** (644 for PHP files, 755 for directories)
10. **Regular database backups** especially before admin operations

## Future Enhancements

- Two-factor authentication for admin accounts
- Email notifications for critical admin actions
- Bulk user operations
- Advanced filtering and sorting
- Export functionality (CSV/PDF)
- User impersonation for support
- Custom roles and permissions
- API access for admin operations
- Real-time dashboard updates
- Advanced analytics and reports

## Support

For issues or questions regarding the admin system:
1. Check the troubleshooting section above
2. Review PHP error logs
3. Check MySQL slow query log
4. Verify file permissions
5. Ensure all migrations have run successfully

## License

This admin system is part of the Budgie personal finance application.
