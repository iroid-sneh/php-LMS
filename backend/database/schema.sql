-- Leave Management System Database Schema
-- MySQL Database Setup

CREATE DATABASE IF NOT EXISTS lms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lms_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('employee', 'hr') DEFAULT 'employee',
    department VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    employee_id VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    joining_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_employee_id (employee_id),
    INDEX idx_role (role)
);

-- Leaves table
CREATE TABLE leaves (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    leave_type ENUM('sick', 'vacation', 'personal', 'emergency', 'other') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    duration DECIMAL(5,2) NOT NULL,
    duration_unit ENUM('hours', 'days') DEFAULT 'days',
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_comment TEXT,
    rejected_reason TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee_status (employee_id, status),
    INDEX idx_start_end_date (start_date, end_date),
    INDEX idx_status (status),
    INDEX idx_applied_at (applied_at)
);

-- Insert sample HR admin user
INSERT INTO users (name, email, password, role, department, position, employee_id, phone) 
VALUES ('HR Admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr', 'Human Resources', 'HR Manager', 'HR001', '1234567890');

-- Insert sample employee
INSERT INTO users (name, email, password, role, department, position, employee_id, phone) 
VALUES ('John Doe', 'john@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'IT', 'Software Developer', 'EMP001', '1234567891');

-- Insert sample leave requests
INSERT INTO leaves (employee_id, leave_type, start_date, end_date, duration, duration_unit, reason, status) 
VALUES (2, 'vacation', '2024-01-15', '2024-01-17', 3, 'days', 'Family vacation trip', 'pending');

INSERT INTO leaves (employee_id, leave_type, start_date, end_date, duration, duration_unit, reason, status, reviewed_by, reviewed_at) 
VALUES (2, 'sick', '2024-01-10', '2024-01-10', 1, 'days', 'Flu symptoms', 'approved', 1, '2024-01-09 10:30:00');
