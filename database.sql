-- database.sql
-- SQL script to create the hacking_interface_db database and users table

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS hacking_interface_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE hacking_interface_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    fingerprint_id VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    submission_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_submission_time (submission_time),
    INDEX idx_fingerprint_id (fingerprint_id(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Insert some test data
INSERT INTO users (name, fingerprint_id, ip_address, user_agent) VALUES
('John Smith', 'FPID_1699181234_ABC123XYZ', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
('Emma Johnson', 'FPID_1699185678_DEF456UVW', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15'),
('Michael Chen', 'FPID_1699189012_GHI789RST', '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15'),
('Sarah Williams', 'FPID_1699192345_JKL012MNO', '192.168.1.103', 'Mozilla/5.0 (Linux; Android 10; SM-G973F) AppleWebKit/537.36'),
('David Brown', 'FPID_1699195678_PQR345STU', '192.168.1.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0');

-- Optional: Create a view for recent scans
CREATE OR REPLACE VIEW recent_scans AS
SELECT 
    id,
    name,
    fingerprint_id,
    ip_address,
    DATE(submission_time) as scan_date,
    TIME(submission_time) as scan_time,
    submission_time
FROM users 
ORDER BY submission_time DESC;

-- Optional: Create a stored procedure to get daily stats
DELIMITER //
CREATE PROCEDURE GetDailyStats(IN target_date DATE)
BEGIN
    SELECT 
        DATE(submission_time) as scan_date,
        COUNT(*) as total_scans,
        GROUP_CONCAT(name ORDER BY submission_time ASC SEPARATOR ', ') as names
    FROM users
    WHERE DATE(submission_time) = target_date
    GROUP BY DATE(submission_time);
END //
DELIMITER ;

-- Optional: Create a trigger to log insertions (for audit purposes)
DELIMITER //
CREATE TRIGGER after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, action, record_id, action_time)
    VALUES ('users', 'INSERT', NEW.id, NOW());
END //
DELIMITER ;

-- Note: For the audit_log table, you would need to create it first:
-- CREATE TABLE IF NOT EXISTS audit_log (
--     id INT(11) NOT NULL AUTO_INCREMENT,
--     table_name VARCHAR(50) NOT NULL,
--     action VARCHAR(10) NOT NULL,
--     record_id INT(11) NOT NULL,
--     action_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     PRIMARY KEY (id)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Show table structure for verification
DESCRIBE users;

-- Show sample data
SELECT 
    id,
    name,
    fingerprint_id,
    ip_address,
    DATE_FORMAT(submission_time, '%Y-%m-%d %H:%i:%s') as submission_time
FROM users 
ORDER BY submission_time DESC 
LIMIT 5;