-- Fix fine_payments table structure
-- Run this in phpMyAdmin SQL tab for library_database

-- Drop tables in correct order (child first, then parent)
DROP TABLE IF EXISTS fine_payments;

-- Recreate fine_payments table WITHOUT foreign key constraints
CREATE TABLE fine_payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    issue_id INT NOT NULL,
    member_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'online', 'other') DEFAULT 'cash',
    payment_date DATETIME NOT NULL,
    receipt_number VARCHAR(50),
    notes TEXT,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_member (member_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_issue (issue_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DESCRIBE fine_payments;
