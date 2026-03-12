-- Migration: Add admin role functionality to users table
-- Date: 2025-01-20

-- Add role column to users table
ALTER TABLE users 
ADD COLUMN role ENUM('user', 'admin') NOT NULL DEFAULT 'user' AFTER subscription_type;

-- Add status column for user activation/deactivation
ALTER TABLE users 
ADD COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active' AFTER role;

-- Create an index on role for faster admin queries
CREATE INDEX idx_users_role ON users(role);

-- Create an index on status for filtering
CREATE INDEX idx_users_status ON users(status);

-- Create first admin user (update email/password as needed)
-- Password: admin123 (hashed with password_hash)
INSERT INTO users (first_name, last_name, email, password, role, subscription_type, status, created_at) 
VALUES (
    'Admin', 
    'User', 
    'admin@budgie.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'admin', 
    'premium', 
    'active', 
    NOW()
) ON DUPLICATE KEY UPDATE role = 'admin';

-- Add admin activity log table for audit trail
CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id INT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_activity_admin_id (admin_id),
    INDEX idx_admin_activity_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
