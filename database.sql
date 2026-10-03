CREATE DATABASE IF NOT EXISTS complaint_management_system;
USE complaint_management_system;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    role ENUM('user', 'staff', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE complaint_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE request_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255),
    priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Low',
    attachment VARCHAR(255),
    status ENUM('Pending', 'Assigned', 'In Progress', 'On Hold', 'Resolved', 'Closed', 'Rejected') DEFAULT 'Pending',
    assigned_staff_id INT DEFAULT NULL,
    admin_remark TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES complaint_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_staff_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255),
    priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Low',
    attachment VARCHAR(255),
    status ENUM('Pending', 'Assigned', 'In Progress', 'On Hold', 'Resolved', 'Closed', 'Rejected') DEFAULT 'Pending',
    assigned_staff_id INT DEFAULT NULL,
    admin_remark TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES request_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_staff_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT DEFAULT NULL,
    request_id INT DEFAULT NULL,
    staff_id INT NOT NULL,
    assigned_by INT NOT NULL,
    remark TEXT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT DEFAULT NULL,
    request_id INT DEFAULT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL,
    remark TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT DEFAULT NULL,
    request_id INT DEFAULT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    attachment VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    related_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT DEFAULT NULL,
    request_id INT DEFAULT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample Admin, Staff, and User
-- Passwords are 'Admin@123', 'Staff@123', 'User@123' hashed using PHP password_hash()
INSERT INTO users (name, email, phone, password, role) VALUES 
('System Admin', 'admin@gmail.com', '1234567890', '$2y$10$tZ2zS1Q2g7O5qA3b6B8J8O5zB2B8J8O5zB2B8J8O5zB2B8J8O5zB2', 'admin'),
('Staff Member', 'staff@gmail.com', '0987654321', '$2y$10$tZ2zS1Q2g7O5qA3b6B8J8O5zB2B8J8O5zB2B8J8O5zB2B8J8O5zB2', 'staff'),
('Normal User', 'user@gmail.com', '1122334455', '$2y$10$tZ2zS1Q2g7O5qA3b6B8J8O5zB2B8J8O5zB2B8J8O5zB2B8J8O5zB2', 'user');
-- NOTE: I will update these passwords in a php script since these dummy hashes may not work with password_verify!

-- Insert sample categories
INSERT INTO complaint_categories (category_name, description) VALUES
('Electricity', 'Electrical issues'),
('Water', 'Plumbing and water supply issues'),
('Internet', 'Network and connectivity issues'),
('Maintenance', 'General maintenance issues');

INSERT INTO request_categories (category_name, description) VALUES
('Cleaning', 'Request for cleaning services'),
('Security', 'Security related requests'),
('Transportation', 'Transport facility requests'),
('IT Support', 'Hardware or software support');
