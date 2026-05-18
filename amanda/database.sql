-- Book Haven Database Schema
-- MySQL Database for Book Store Application

-- Create database
CREATE DATABASE IF NOT EXISTS book_haven;
USE book_haven;

-- Users table (customers and admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Books table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    category_id INT,
    isbn VARCHAR(20) UNIQUE,
    publisher VARCHAR(100),
    publish_date DATE,
    pages INT,
    language VARCHAR(50) DEFAULT 'English',
    cover_image VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_address TEXT,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    shipped_date TIMESTAMP NULL,
    delivered_date TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Shopping cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_book (user_id, book_id)
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Fiction', 'Novels, short stories, and other fictional works'),
('Non-Fiction', 'Biographies, history, science, and other non-fiction books'),
('Science Fiction', 'Science fiction novels and stories'),
('Mystery', 'Mystery and thriller novels'),
('Romance', 'Romance novels'),
('Self-Help', 'Self-improvement and personal development'),
('Technology', 'Books about technology and programming'),
('History', 'Historical books and documents');

-- Insert sample books
INSERT INTO books (title, author, description, price, stock_quantity, category_id, isbn, publisher, pages, language) VALUES
('The Project', 'Andy Weir', 'A humanity-first sci-fi thriller about survival and hope.', 24.99, 50, 3, '978-0593135204', 'Ballantine Books', 368, 'English'),
('The Four Winds', 'Kristin Hannah', 'A story of love, sacrifice, and hope during the Great Depression.', 28.99, 35, 1, '978-1250178602', 'St. Martin Press', 464, 'English'),
('Klara and the Sun', 'Kazuo Ishiguro', 'A thrilling book that offers a look at our changing world.', 22.99, 40, 3, '978-0571364879', 'Faber & Faber', 320, 'English'),
('Project Hail Mary', 'Andy Weir', 'A lone astronaut must save the earth from disaster.', 26.99, 45, 3, '978-0593135204', 'Ballantine Books', 496, 'English'),
('The Midnight Library', 'Matt Haig', 'Between life and death there is a library.', 18.99, 60, 1, '978-0525559474', 'Viking', 304, 'English');

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES
('admin', 'admin@bookhaven.com', '$2b$10$abcdefghijklmnopqrstuvwxyz1234567890', 'Admin', 'User', 'admin');

-- Insert sample customer (password: customer123)
INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES
('customer', 'customer@example.com', '$2b$10$zyxwvutsrqponmlkjihgfedcba9876543210', 'John', 'Doe', 'customer');