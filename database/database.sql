CREATE DATABASE IF NOT EXISTS db_grahabag CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_grahabag;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'owner') NOT NULL,
    last_notif_read TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE login_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    login_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE category_id (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE material (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    material_name VARCHAR(100) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    unit VARCHAR(255) NOT NULL,
    minimum_stock INT NOT NULL DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES category_id(category_id)
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    no_telp VARCHAR(50) NOT NULL,
    address VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE material_purchase (
    purchase_id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    supplier_id INT NOT NULL,
    user_id INT NOT NULL,
    total INT NOT NULL,
    purchase_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    contact_method ENUM('whatsapp', 'email') DEFAULT 'whatsapp',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES material(material_id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE stock_in (
    stock_in_id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    supplier_id INT NULL,
    user_id INT NOT NULL,
    date_stock_in DATE NOT NULL,
    total_in INT NOT NULL,
    description_in TEXT,
    photo VARCHAR(255) NULL,
    FOREIGN KEY (material_id) REFERENCES material(material_id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE stock_out (
    stock_out_id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    user_id INT NOT NULL,
    date_stock_out DATE NOT NULL,
    total_out INT NOT NULL,
    description_out TEXT,
    photo VARCHAR(255) NULL,
    FOREIGN KEY (material_id) REFERENCES material(material_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;