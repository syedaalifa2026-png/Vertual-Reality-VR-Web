-- ============================================================
-- VRverse Complete Database Schema
-- MySQL 5.7+ / MariaDB 10.3+
-- Run this file in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS vrverse_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vrverse_db;

-- ============================================================
-- TABLE 1: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,       -- bcrypt hashed
    avatar      VARCHAR(10) DEFAULT NULL,    -- first letter of name
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active   TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 2: bookings
-- ============================================================
CREATE TABLE IF NOT EXISTS bookings (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT DEFAULT NULL,       -- NULL = guest order
    full_name        VARCHAR(100) NOT NULL,
    email            VARCHAR(150) NOT NULL,
    phone            VARCHAR(20) NOT NULL,
    product_name     VARCHAR(200) NOT NULL,
    product_price    DECIMAL(10,2) NOT NULL,
    delivery_location VARCHAR(100) NOT NULL,
    full_address     TEXT NOT NULL,
    delivery_date    DATE NOT NULL,
    payment_method   VARCHAR(50) NOT NULL,
    delivery_charge  DECIMAL(10,2) DEFAULT 0,
    total_amount     DECIMAL(10,2) NOT NULL,
    order_id         VARCHAR(20) NOT NULL,   -- e.g. VR83421
    status           ENUM('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'confirmed',
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 3: contacts
-- ============================================================
CREATE TABLE IF NOT EXISTS contacts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    subject    VARCHAR(200) DEFAULT NULL,
    message    TEXT NOT NULL,
    status     ENUM('unread','read','replied') DEFAULT 'unread',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 4: reviews
-- ============================================================
CREATE TABLE IF NOT EXISTS reviews (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT DEFAULT NULL,
    reviewer_name VARCHAR(100) NOT NULL,
    email        VARCHAR(150) DEFAULT NULL,
    product      VARCHAR(200) DEFAULT NULL,
    rating       TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text  TEXT NOT NULL,
    status       ENUM('pending','approved','rejected') DEFAULT 'approved',
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 5: newsletter (bonus)
-- ============================================================
CREATE TABLE IF NOT EXISTS newsletter (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(150) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Sample test user (password: Test@1234)
-- ============================================================
INSERT INTO users (full_name, email, password, avatar) VALUES
('Test User', 'test@vrverse.com', '$2y$12$5G5G5G5G5G5G5G5G5G5G5O1234567890abcdefghijklmnopqrstuv', 'T');

-- Note: Use php register.php to create real users with proper bcrypt hash