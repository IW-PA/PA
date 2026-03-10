-- Budgie Database Schema
-- Personal Finance Management Application

CREATE DATABASE IF NOT EXISTS budgie_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE budgie_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    subscription_type ENUM('free', 'premium') DEFAULT 'free',
    subscription_start_date DATETIME NULL,
    subscription_end_date DATETIME NULL,
    stripe_customer_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
);

-- Accounts table
CREATE TABLE accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    balance DECIMAL(15,2) DEFAULT 0.00,
    interest_rate DECIMAL(5,2) DEFAULT 0.00,
    tax_rate DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Expenses table
CREATE TABLE expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    amount DECIMAL(15,2) NOT NULL,
    frequency ENUM('ponctuel', 'mensuel', 'bimensuel', 'trimestriel', 'semestriel', 'annuel') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
);

-- Incomes table
CREATE TABLE incomes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    amount DECIMAL(15,2) NOT NULL,
    frequency ENUM('ponctuel', 'mensuel', 'bimensuel', 'trimestriel', 'semestriel', 'annuel') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
);

-- Exceptions table
CREATE TABLE exceptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    expense_id INT NULL,
    income_id INT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    amount DECIMAL(15,2) NOT NULL,
    frequency ENUM('ponctuel', 'mensuel', 'bimensuel', 'trimestriel', 'semestriel', 'annuel') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE,
    FOREIGN KEY (income_id) REFERENCES incomes(id) ON DELETE CASCADE,
    CHECK ((expense_id IS NOT NULL AND income_id IS NULL) OR (expense_id IS NULL AND income_id IS NOT NULL))
);

-- Account sharing table
CREATE TABLE account_shares (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    owner_id INT NOT NULL,
    shared_with_email VARCHAR(255) NOT NULL,
    shared_with_user_id INT NULL,
    access_type ENUM('read_only', 'read_write') DEFAULT 'read_only',
    status ENUM('pending', 'accepted', 'declined', 'revoked') DEFAULT 'pending',
    invitation_token VARCHAR(190) UNIQUE NOT NULL,
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_with_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Transactions table (for actual transaction records)
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    type ENUM('expense', 'income', 'interest', 'transfer') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    transaction_date DATE NOT NULL,
    expense_id INT NULL,
    income_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE SET NULL,
    FOREIGN KEY (income_id) REFERENCES incomes(id) ON DELETE SET NULL
);

-- Subscription payments table
CREATE TABLE subscription_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'EUR',
    stripe_payment_intent_id VARCHAR(255) NULL,
    status ENUM('pending', 'succeeded', 'failed', 'canceled') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Activity logs table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NULL,
    entity_id INT NULL,
    metadata JSON NULL,
    ip_address VARCHAR(64) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- User sessions table
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(190) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Password reset tokens table
CREATE TABLE password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(190) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_subscription ON users(subscription_type);
CREATE INDEX idx_accounts_user_id ON accounts(user_id);
CREATE INDEX idx_expenses_user_id ON expenses(user_id);
CREATE INDEX idx_expenses_account_id ON expenses(account_id);
CREATE INDEX idx_expenses_active ON expenses(is_active);
CREATE INDEX idx_incomes_user_id ON incomes(user_id);
CREATE INDEX idx_incomes_account_id ON incomes(account_id);
CREATE INDEX idx_incomes_active ON incomes(is_active);
CREATE INDEX idx_transactions_user_id ON transactions(user_id);
CREATE INDEX idx_transactions_account_id ON transactions(account_id);
CREATE INDEX idx_transactions_date ON transactions(transaction_date);
CREATE INDEX idx_account_shares_account_id ON account_shares(account_id);
CREATE INDEX idx_account_shares_shared_with ON account_shares(shared_with_email);
CREATE INDEX idx_user_sessions_token ON user_sessions(session_token);
CREATE INDEX idx_password_reset_tokens_token ON password_reset_tokens(token);
CREATE INDEX idx_activity_logs_user ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_action ON activity_logs(action);

-- Insert sample data
INSERT INTO users (first_name, last_name, email, password_hash, subscription_type) VALUES
('Jean', 'Dupont', 'jean.dupont@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'free'),
('Marie', 'Martin', 'marie.martin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'premium'),
('Admin', 'User', 'admin@budgie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'premium');

-- Update admin user role (we'll handle this in the application)
UPDATE users SET first_name = 'Admin', last_name = 'Administrator' WHERE email = 'admin@budgie.com';

-- Sample accounts
INSERT INTO accounts (user_id, name, description, balance, interest_rate, tax_rate) VALUES
(1, 'Compte Courant', 'Compte courant Société Générale', 3500.00, 0.00, 0.00),
(1, 'Livret A', 'Livret A individuel', 8500.00, 1.70, 0.00),
(1, 'CTO', 'Compte Titre Ordinaire', 450.00, 7.00, 30.00),
(2, 'Compte Principal', 'Compte principal Marie', 5000.00, 0.00, 0.00);

-- Sample expenses
INSERT INTO expenses (user_id, account_id, name, description, amount, frequency, start_date, end_date) VALUES
(1, 1, 'Crédit Moto', 'Crédit pour la Triumph Tiger 660 Sport 2023', 250.00, 'mensuel', '2023-01-01', '2028-12-31'),
(1, 1, 'iPhone 19', 'iPhone 19 Pro Max Limited Hanna Montana Edition', 4321.00, 'ponctuel', '2025-09-01', NULL),
(1, 1, 'Courses Alimentaires', 'Courses hebdomadaires', 150.00, 'mensuel', '2025-01-01', NULL),
(1, 1, 'Essence', 'Carburant voiture', 80.00, 'bimensuel', '2025-01-01', NULL);

-- Sample incomes
INSERT INTO incomes (user_id, account_id, name, description, amount, frequency, start_date, end_date) VALUES
(1, 1, 'Salaire', 'Salaire Alternant Développeur Web', 1170.00, 'mensuel', '2025-01-01', '2027-12-31'),
(1, 1, 'Prime de fin d\'année', 'Prime de fin d\'année', 150.00, 'annuel', '2025-01-01', '2027-12-31'),
(1, 3, 'Alimentation CTO', 'Alimentation mensuelle du compte titre ordinaire', 50.00, 'mensuel', '2025-01-01', '2025-12-31'),
(1, 1, 'Freelance', 'Projets freelance occasionnels', 500.00, 'ponctuel', '2025-01-15', NULL);
