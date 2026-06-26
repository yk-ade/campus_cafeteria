CREATE DATABASE IF NOT EXISTS rectem_restaurant_db;
USE rectem_restaurant_db;

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
('Swallow'),
('Topping');

INSERT INTO campus_locations (location_name) VALUES
('Main Hostel'),
('Female Hostel'),
('Male Hostel'),
('Library Front'),
('Science Block'),
('Administrative Block');

INSERT INTO users (full_name, matric_no, email, phone, password, role) VALUES
('Admin', NULL, 'admin@rectemcafeteria.com', '+2348000000000', '$2y$12$Ud.mOQtVYbHzYqNI0FZ5n.rUbz.2dZBx6W9503fWzla91J2QMe3GW', 'admin'),
('Kitchen Staff', NULL, 'staff@rectemcafeteria.com', '+2348000000001', '$2y$12$Ud.mOQtVYbHzYqNI0FZ5n.rUbz.2dZBx6W9503fWzla91J2QMe3GW', 'staff'),
('Demo Student', 'RECTEM/24/001', 'student@rectemcafeteria.com', '+2348000000002', '$2y$12$Ud.mOQtVYbHzYqNI0FZ5n.rUbz.2dZBx6W9503fWzla91J2QMe3GW', 'user');

INSERT INTO menu_items (category_id, item_name, description, price, image, availability_status) VALUES
(1, 'Akara & Pap', 'Golden fried bean cakes served with warm pap - classic Nigerian breakfast.', 2200.00, 'akara_pap_1779140136176.png', 'Available'),
(1, 'Yam Porridge', 'Creamy yam porridge cooked to perfection - filling breakfast delight.', 2500.00, 'yam_porridge_1779140243559.png', 'Available'),
(1, 'Moi Moi', 'Steamed bean pudding - protein-rich breakfast served warm.', 2300.00, 'moi_moi_1779140107192.png', 'Available'),
(1, 'Egg & Plantain', 'Fried eggs served with crispy plantain - quick morning favorite.', 2400.00, 'egg_real.jpg', 'Available'),

(2, 'Jollof Rice with Chicken', 'Aromatic jollof rice paired with seasoned chicken - campus favorite.', 4000.00, 'jollof_rice_1779140026555.png', 'Available'),
(2, 'Fried Rice Deluxe', 'Yellow fried rice with vegetables, eggs, and protein choice.', 3800.00, 'fried_rice_1779140063860.png', 'Available'),
(2, 'Ofada Rice with Sauce', 'Local parboiled rice served with specially prepared sauce.', 3600.00, 'ofada_rice_1779140122342.png', 'Available'),
(2, 'Pounded Yam & Egusi', 'Smooth pounded yam served with rich egusi soup - pure comfort.', 4200.00, 'pounded_yam_egusi_1779140040298.png', 'Available'),
(2, 'Efo Riro with Rice', 'Spinach stew cooked traditional style, served with rice.', 3500.00, 'efo_riro_1779140256562.png', 'Available'),
(2, 'Edikang Ikong with Rice', 'Leafy green soup with assorted meats and rice.', 3700.00, 'edikang_ikong_1779140197474.png', 'Available'),
(2, 'Oha Soup with Fufu', 'Traditional oha leaf soup with pounded yam or fufu.', 3900.00, 'oha_soup_1779140231174.png', 'Available'),
(2, 'Banga Soup with Rice', 'Rich palm nut soup served with fluffy white rice.', 3600.00, 'banga_soup_1779140178947.png', 'Available'),

(3, 'Pepper Soup', 'Spicy traditional pepper soup with meat or fish - warming and delicious.', 2500.00, 'pepper_soup_1779140164836.png', 'Available'),
(3, 'Suya Skewers', 'Grilled meat skewers with spicy peanut coating.', 3200.00, 'suya_1779140078763.png', 'Available'),
(3, 'Chicken & Chips', 'Crispy fried chicken served with golden chips.', 4200.00, 'chicken.jpg', 'Available'),
(3, 'Shawarma Wrap', 'Savory meat wrap with fresh vegetables and special sauce.', 2800.00, 'shawarma.jpg', 'Available'),
(3, 'Beef Burger', 'Juicy beef burger with all the trimmings.', 2600.00, 'beef_real.jpg', 'Available'),
(3, 'Nkwobi', 'Spicy cow leg delicacy with groundnuts - for the adventurous palate.', 3000.00, 'nkwobi_1779140150190.png', 'Available'),
(3, 'Assorted Pepper Meat', 'Selection of peppered meats - party style!', 4500.00, '1779144763_Nigerian_Assorted_Peppered_Meats_-_Party_Style.jfif', 'Available'),

(4, 'Soft Drink', 'Chilled bottled drink to complete your meal.', 700.00, 'drink.jpg', 'Available'),

(5, 'French Fries', 'Crispy golden fries - perfect snack or side.', 1800.00, 'french-fries.jpg', 'Available'),
(5, 'Plantain Chips', 'Sliced and fried plantain chips - addictive snack.', 1600.00, 'plantain_real.jpg', 'Available'),
(5, 'Abacha & Ugba', 'Traditional tapioca pudding with African oil bean - authentic delicacy.', 2000.00, 'abacha_real.jpg', 'Available'),
(5, 'Okpa', 'Steamed corn pudding - savory afternoon snack.', 1500.00, 'okpa_real.jpg', 'Available'),

(6, 'Fufu with Soup', 'Smooth fufu served with hot soup of your choice.', 3500.00, 'pounded_yam_egusi_1779140040298.png', 'Available'),
(6, 'Ewa Agoyin', 'Mashed beans in rich and spicy sauce - comfort food classic.', 2800.00, 'ewa_agoyin_1779140094193.png', 'Available'),
(6, 'Amala & Ewedu', 'Smooth amala served with silky okra soup.', 3200.00, 'amala_ewedu_1779140052113.png', 'Available'),
(6, 'Tuwo', 'Smooth corn pudding - traditional Yoruba dish.', 2600.00, 'tuwo_real.jpg', 'Available');
