-- Database schema for Libidex Admin Panel

CREATE DATABASE IF NOT EXISTS libidex_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE libidex_db;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    name_hindi VARCHAR(200) NOT NULL,
    description TEXT,
    description_hindi TEXT,
    price DECIMAL(10, 2) NOT NULL,
    old_price DECIMAL(10, 2) NULL,
    image VARCHAR(255) NULL,
    image_secondary VARCHAR(255) NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    stock INT DEFAULT 100,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    country VARCHAR(10) DEFAULT 'IN',
    clickid VARCHAR(255),
    utm_campaign VARCHAR(255),
    utm_content VARCHAR(255),
    utm_medium VARCHAR(255),
    utm_source VARCHAR(255),
    product VARCHAR(255) DEFAULT 'Libidex',
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    age INT,
    image VARCHAR(255) DEFAULT 'live-1.jpg',
    review_text TEXT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, email, password_hash) VALUES 
('admin', 'admin@libidex.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username = username;

-- Insert default product
INSERT INTO products (name, name_hindi, description, description_hindi, price, old_price, image, status) VALUES 
('Libidex', 'लिबिडेक्स', 'Libidex - Natural male enhancement capsules', 'लिबिडेक्स - पुरुषों के स्वास्थ्य के लिए प्राकृतिक कैप्सूल', 2490.00, 4980.00, 'product-1.png', 'active')
ON DUPLICATE KEY UPDATE name = name;

-- Insert default reviews
INSERT INTO reviews (name, age, image, review_text, status, sort_order) VALUES
('राजेश शर्मा', 42, 'live-1.jpg', 'बहुत बढ़िया उत्पाद! 2 हफ्ते में ही असर दिखा।', 'active', 1),
('अमित वर्मा', 38, 'live-2.jpg', 'आत्मविश्वास वापस आ गया। Highly recommended!', 'active', 2),
('संजय पटेल', 45, 'live-3.jpg', 'पत्नी भी खुश हैं। अब कोई परेशानी नहीं।', 'active', 3)
ON DUPLICATE KEY UPDATE name = name;
