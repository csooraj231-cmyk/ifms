-- Migration: add attendance_audit table
-- Run this SQL to add audit logging for attendance actions

CREATE TABLE IF NOT EXISTS attendance_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor_id INT NULL,
    employee_id INT NOT NULL,
    action VARCHAR(64) NOT NULL,
    `date` DATE NULL,
    status VARCHAR(64) NULL,
    notes TEXT NULL,
    details TEXT NULL,
    ip VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (employee_id),
    INDEX (actor_id),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
