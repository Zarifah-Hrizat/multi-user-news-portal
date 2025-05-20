-- Create database
CREATE DATABASE IF NOT EXISTS news_system;
USE news_system;

-- Create user table
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('author', 'editor', 'admin') NOT NULL
);

-- Create category table
CREATE TABLE IF NOT EXISTS category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Create news table
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    image VARCHAR(255),
    dateposted DATETIME DEFAULT CURRENT_TIMESTAMP,
    category_id INT,
    author_id INT,
    status ENUM('pending', 'approved', 'denied') DEFAULT 'pending',
    FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES user(id) ON DELETE SET NULL
);

-- Insert sample data
-- Sample users
INSERT INTO user (name, email, password, role) VALUES
('John Author', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'author'),
('Jane Editor', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor'),
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Sarah Author', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'author');

-- Sample categories
INSERT INTO category (name, description) VALUES
('Politics', 'News related to politics and government'),
('Technology', 'News about technology and innovation'),
('Sports', 'Sports news and updates'),
('Entertainment', 'Entertainment and celebrity news');

-- Sample news items
INSERT INTO news (title, body, image, category_id, author_id, status) VALUES
('New Technology Breakthrough', 'Scientists have discovered a new technology that could revolutionize the industry...', 'tech1.jpg', 2, 1, 'approved'),
('Election Results Announced', 'The results of the recent election have been announced with surprising outcomes...', 'election.jpg', 1, 1, 'pending'),
('Major Sports Event Cancelled', 'The upcoming sports event has been cancelled due to unforeseen circumstances...', 'sports.jpg', 3, 4, 'approved'),
('Celebrity Wedding', 'Famous celebrity couple tied the knot in a private ceremony...', 'wedding.jpg', 4, 4, 'denied'),
('New Smartphone Release', 'The latest smartphone model has been released with impressive features...', 'phone.jpg', 2, 1, 'pending');
