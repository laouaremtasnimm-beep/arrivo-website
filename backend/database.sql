-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS arrivo_db;
USE arrivo_db;

-- Drop tables if they exist to allow clean recreation
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS listings;
DROP TABLE IF EXISTS users;

-- 1. Create Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'tourist',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Create Listings Table
CREATE TABLE listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Create Bookings Table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    listing_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);

-- 4. Create Reviews Table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    listing_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);

-- ==============================================
-- INSERT DUMMY DATA FOR TESTING
-- ==============================================

-- Insert Dummy Users
-- Password for all users is 'password123'
INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES 
('Alice', 'Smith', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tourist'),
('Bob', 'Jones', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tourist'),
('Agency', 'Admin', 'agency@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency');

-- Insert Dummy Listings
INSERT INTO listings (title, description, location, price, category, image_url) VALUES 
('Luxury Villa with Private Pool', 'A stunning 4-bedroom villa offering spectacular views and a private infinity pool.', 'Bali, Indonesia', 350.00, 'hotel', 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=800&q=80'),
('Cozy Downtown Apartment', 'Modern apartment in the heart of the city, walking distance to major attractions.', 'Tokyo, Japan', 120.00, 'hotel', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80'),
('Grand Canyon Helicopter Tour', 'Experience the majesty of the Grand Canyon from above with this guided 2-hour helicopter tour.', 'Arizona, USA', 299.00, 'tour', 'https://images.unsplash.com/photo-1527004013195-2a3b005c1901?auto=format&fit=crop&w=800&q=80'),
('Seine River Dinner Cruise', 'Enjoy a romantic 3-course dinner while cruising past the illuminated landmarks of Paris.', 'Paris, France', 150.00, 'tour', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=800&q=80'),
('Seaside Resort & Spa', 'Relax at our 5-star beachfront resort with full spa amenities and all-inclusive dining.', 'Maldives', 850.00, 'hotel', 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=800&q=80');

-- Insert Dummy Bookings
INSERT INTO bookings (user_id, listing_id, start_date, end_date, status) VALUES 
(1, 1, '2024-06-10', '2024-06-17', 'confirmed'),
(2, 3, '2024-07-05', '2024-07-05', 'completed'),
(1, 4, '2024-08-15', '2024-08-15', 'confirmed');

-- Insert Dummy Reviews
INSERT INTO reviews (user_id, listing_id, rating, comment) VALUES 
(2, 3, 5, 'Absolutely breathtaking! The pilot was very knowledgeable and the views were incredible.'),
(1, 1, 5, 'The villa exceeded all our expectations. The pool was perfect and the host was very accommodating.'),
(2, 2, 4, 'Great location, very clean. A bit noisy at night, but overall a solid stay.');
