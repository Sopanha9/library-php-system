-- ============================================================================
-- MEMBER MANAGEMENT - DATABASE SCHEMA
-- ============================================================================

-- 1. Alter existing members table to add missing columns
-- Run these one by one, skip if you get "Duplicate column" error
ALTER TABLE members ADD COLUMN date_of_birth DATE AFTER address;
ALTER TABLE members ADD COLUMN id_number VARCHAR(50) AFTER date_of_birth;
ALTER TABLE members ADD COLUMN profile_photo VARCHAR(255) AFTER id_number;
ALTER TABLE members ADD COLUMN membership_type ENUM('standard', 'premium', 'student') DEFAULT 'standard' AFTER profile_photo;
-- ALTER TABLE members ADD COLUMN join_date DATE NOT NULL DEFAULT (CURDATE()) AFTER membership_type; -- Already exists
ALTER TABLE members ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' AFTER join_date;
ALTER TABLE members ADD COLUMN max_books_allowed INT DEFAULT 5 AFTER status;
ALTER TABLE members ADD COLUMN notes TEXT AFTER max_books_allowed;

-- 2. Create library_settings table (for configurable settings)
CREATE TABLE IF NOT EXISTS library_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO library_settings (setting_key, setting_value, description) VALUES
('fine_per_day', '1.00', 'Fine amount charged per day for overdue books (in Riel)'),
('grace_period_days', '0', 'Number of grace days before fine starts'),
('max_fine_amount', '50.00', 'Maximum fine amount per book'),
('default_borrow_days', '14', 'Default number of days for borrowing'),
('max_books_per_member', '5', 'Maximum books a member can borrow'),
('fine_threshold_for_suspension', '20.00', 'Fine amount threshold to suspend member')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- 3. Update users table to link with members (if not already done)
-- ALTER TABLE users ADD COLUMN member_id INT NULL AFTER user_id;
-- ALTER TABLE users ADD FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE SET NULL;

-- 4. Create issued_books table (for circulation)
CREATE TABLE IF NOT EXISTS issued_books (
    issue_id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('Issued', 'Returned', 'Lost') DEFAULT 'Issued',
    book_condition_on_return ENUM('Good', 'Damaged', 'Lost') NULL,
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    fine_paid DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    issued_by INT,
    returned_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE RESTRICT,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE RESTRICT,
    FOREIGN KEY (issued_by) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (returned_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_member (member_id),
    INDEX idx_book (book_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create fine_payments table
CREATE TABLE IF NOT EXISTS fine_payments (
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
    FOREIGN KEY (issue_id) REFERENCES issued_books(issue_id) ON DELETE RESTRICT,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE RESTRICT,
    FOREIGN KEY (recorded_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_member (member_id),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Insert sample members for testing
INSERT INTO members (full_name, email, phone, address, date_of_birth, membership_type, join_date, status) VALUES
('John Doe', 'john.doe@email.com', '012345678', '123 Main St, Phnom Penh', '1995-05-15', 'standard', CURDATE(), 'active'),
('Jane Smith', 'jane.smith@email.com', '012987654', '456 Oak Ave, Phnom Penh', '1998-08-20', 'premium', CURDATE(), 'active'),
('Bob Wilson', 'bob.wilson@email.com', '012555666', '789 Pine Rd, Phnom Penh', '2000-03-10', 'student', CURDATE(), 'active');

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

-- Check if tables were created
SHOW TABLES;

-- Check members table structure
DESCRIBE members;

-- Check library_settings
SELECT * FROM library_settings;

-- Check sample members
SELECT member_id, full_name, email, membership_type, status FROM members;

-- ============================================================================
