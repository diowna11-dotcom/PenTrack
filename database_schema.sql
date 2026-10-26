-- ============================================================================
-- PenTrack Database Schema
-- ============================================================================
-- STEP 1: Run this entire script in phpMyAdmin (SQL tab) or MySQL CLI
-- STEP 2: Then log in at /pages/login.php with: admin / admin123
-- ============================================================================

CREATE DATABASE IF NOT EXISTS barangay_pension_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barangay_pension_db;

-- ----------------------------------------------------------------------------
-- Users table (staff accounts - for login & session tracking)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Senior citizens table
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS senior_citizens (
    senior_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Pension programs table
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pension_programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Pension claims table
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pension_claims (
    claim_id INT AUTO_INCREMENT PRIMARY KEY,
    senior_id INT,
    program_id INT,
    user_id INT,
    claim_date DATE,
    status VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (senior_id) REFERENCES senior_citizens(senior_id) ON DELETE SET NULL,
    FOREIGN KEY (program_id) REFERENCES pension_programs(program_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Transactions / session activity log table (audit trail)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Default admin user (username: admin, password: admin123)
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO users (username, full_name, password, role) VALUES
('admin', 'System Administrator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ----------------------------------------------------------------------------
-- Sample data so you can immediately see transactions in the dashboard
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO senior_citizens (full_name) VALUES
('Juan Dela Cruz'),
('Maria Santos'),
('Jose Rizal');

INSERT IGNORE INTO pension_programs (name) VALUES
('Social Pension'),
('Senior Citizen Discount');

-- Insert a sample claim so add/edit/delete logging has context
INSERT INTO pension_claims (senior_id, program_id, user_id, claim_date, status)
SELECT sc.senior_id, pp.program_id, u.user_id, '2026-10-20', 'Claimed'
FROM senior_citizens sc, pension_programs pp, users u
WHERE sc.full_name = 'Juan Dela Cruz'
  AND pp.name = 'Social Pension'
  AND u.username = 'admin'
LIMIT 1;