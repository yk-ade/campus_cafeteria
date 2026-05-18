CREATE DATABASE IF NOT EXISTS campus_cafeteria_db;
USE campus_cafeteria_db;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS campus_locations;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    matric_no VARCHAR(40) NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE campus_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    item_name VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    availability_status ENUM('Available', 'Unavailable') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_method ENUM('Pickup', 'Campus Delivery') NOT NULL DEFAULT 'Pickup',
    payment_method VARCHAR(50) NOT NULL,
    order_status ENUM('Pending','Confirmed','Preparing','Ready for Pickup','Out for Delivery','Delivered','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
    delivery_address TEXT NULL,
    order_token VARCHAR(20) NULL,
    special_request TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    guests INT NOT NULL,
    special_request TEXT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('Pending', 'Paid', 'Failed') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

INSERT INTO categories (category_name) VALUES
('Breakfast'),
('Rice Meals'),
('Fast Food'),
('Drinks'),
('Snacks'),
('Swallow');

INSERT INTO campus_locations (location_name) VALUES
('Main Hostel'),
('Female Hostel'),
('Male Hostel'),
('Library Front'),
('Science Block'),
('Administrative Block');

INSERT INTO users (full_name, matric_no, email, phone, password, role) VALUES
('Admin', NULL, 'admin@campuscafeteria.com', '+2348000000000', '$2y$12$Ud.mOQtVYbHzYqNI0FZ5n.rUbz.2dZBx6W9503fWzla91J2QMe3GW', 'admin'),
('Kitchen Staff', NULL, 'staff@campuscafeteria.com', '+2348000000001', '$2y$12$Ud.mOQtVYbHzYqNI0FZ5n.rUbz.2dZBx6W9503fWzla91J2QMe3GW', 'staff'),
('Demo Student', 'CAMP/24/001', 'student@campuscafeteria.com', '+2348000000002', '$2y$12$Ud.mOQtVYbHzYqNI0FZ5n.rUbz.2dZBx6W9503fWzla91J2QMe3GW', 'user');

INSERT INTO menu_items (category_id, item_name, description, price, image, availability_status) VALUES
(1, 'Breakfast Combo', 'Bread, egg, and a hot drink prepared for quick morning service.', 2500.00, 'burger.jpg', 'Available'),
(2, 'Student Rice Bowl', 'Rice meal served with protein and side sauce for busy lecture days.', 3500.00, 'pizza.jpg', 'Available'),
(3, 'Campus Chicken Combo', 'Chicken and fries combo built for lunch breaks and evening hunger.', 4200.00, 'chicken.jpg', 'Available'),
(3, 'Cafeteria Shawarma Wrap', 'Fast food wrap option for students who want a quick filling meal.', 2800.00, 'shawarma.jpg', 'Available'),
(4, 'Soft Drink', 'Chilled bottled drink to complete a combo or snack order.', 700.00, 'drink.jpg', 'Available'),
(5, 'French Fries', 'Crispy snack portion served hot for short breaks or add-ons.', 1800.00, 'french-fries.jpg', 'Available');
