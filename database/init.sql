CREATE DATABASE IF NOT EXISTS citizen_management;
USE citizen_management;

CREATE TABLE IF NOT EXISTS citizens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    nic VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    province VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    birthday DATE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'citizen',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin user (password is admin123)
INSERT INTO citizens (name, nic, email, province, district, birthday, phone, password_hash, role) 
VALUES ('Administrator', '000000000V', 'admin@example.com', 'Western', 'Colombo', '1980-01-01', '0770000000', '$2y$10$wT0X7VfFhH4E3Y0oP9wG2eJ0q7R8r5hL6m.fU4Y8xMvHlZ9WpL1XW', 'admin')
ON DUPLICATE KEY UPDATE id=id;
