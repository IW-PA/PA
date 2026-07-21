-- Email verification support. IDEMPOTENT: safe to run on every deploy.
-- MySQL 8 has no "ADD COLUMN IF NOT EXISTS", so guard the ALTER via information_schema.

SET @col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email_verified_at'
);

-- Add the column only if it is missing.
SET @ddl := IF(@col = 0,
    'ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL AFTER status',
    'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

-- Grandfather existing accounts ONLY on the first run (when the column was just added).
-- On later runs this is a no-op, so pending unverified signups are never auto-verified.
SET @grandfather := IF(@col = 0, 'UPDATE users SET email_verified_at = NOW()', 'DO 0');
PREPARE g FROM @grandfather; EXECUTE g; DEALLOCATE PREPARE g;

CREATE TABLE IF NOT EXISTS email_verification_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(190) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
