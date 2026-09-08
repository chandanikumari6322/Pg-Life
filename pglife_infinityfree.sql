-- ===========================================
-- PG Life - Student Accommodation Website
-- Database Schema + Sample Data
-- ===========================================
-- NOTE: If you already imported an older version of this file, importing again
-- will DROP and recreate the whole database (so you get the new 'bookings'
-- table). This wipes any test accounts/data you created - that's expected
-- for a fresh re-import during development.


-- 1. USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    college_name VARCHAR(150),
    gender ENUM('male','female') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. PROPERTIES
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    price INT NOT NULL,
    gender ENUM('male','female','unisex') NOT NULL,
    rating DECIMAL(2,1) DEFAULT 0,
    image_folder VARCHAR(50) NOT NULL,   -- matches img/properties/<id>/ folder
    description TEXT
);

-- 3. AMENITIES
CREATE TABLE amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(255) NOT NULL,          -- path under img/amenities/
    category VARCHAR(50) NOT NULL        -- Building / Common Area / Bedroom / Bathroom
);

-- 4. PROPERTY_AMENITIES (many-to-many)
CREATE TABLE property_amenities (
    property_id INT,
    amenity_id INT,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
);

-- 5. INTERESTED_USERS
CREATE TABLE interested_users (
    user_id INT,
    property_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- 6. BOOKINGS (Book Now feature)
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    notified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- ===========================================
-- SAMPLE DATA
-- ===========================================

INSERT INTO properties (name, address, city, price, gender, rating, image_folder, description) VALUES
('Navkar Paying Guest', '44, Juhu Scheme, Juhu, Mumbai, Maharashtra 400058', 'Mumbai', 9500, 'male', 4.5, '1', 'A comfortable and secure PG located near Juhu, ideal for working professionals and students.'),
('Ganpati Paying Guest', 'Police Beat, Sainath Complex, Besides SV Rd, Daulat Nagar, Borivali East, Mumbai - 400066', 'Mumbai', 8500, 'unisex', 4.8, '2', 'Modern unisex PG with fully furnished rooms and all basic amenities included.'),
('PG for Girls Borivali West', 'Plot no.258/D4, Gorai no.2, Borivali West, Mumbai, Maharashtra 400092', 'Mumbai', 8000, 'female', 3.5, '3', 'Safe and secure PG exclusively for girls with 24x7 security and home-cooked food.'),
('Sunrise Boys Hostel', 'Sector 15, Rohini, Delhi 110085', 'Delhi', 7500, 'male', 4.2, '4', 'Budget-friendly hostel for male students near Delhi University North Campus.'),
('Elite Girls Residency', 'MG Road, Indiranagar, Bangalore, Karnataka 560038', 'Bangalore', 11000, 'female', 4.6, '5', 'Premium girls residency with gym, WiFi and daily housekeeping.'),
('Urban Nest Unisex PG', 'Banjara Hills, Hyderabad, Telangana 500034', 'Hyderabad', 9000, 'unisex', 4.1, '33', 'Co-living space designed for students and young professionals in Hyderabad.');

INSERT INTO amenities (name, icon, category) VALUES
('Power backup', 'img/amenities/powerbackup.svg', 'Building'),
('Lift', 'img/amenities/lift.svg', 'Building'),
('Wifi', 'img/amenities/wifi.svg', 'Common Area'),
('TV', 'img/amenities/tv.svg', 'Common Area'),
('Water Purifier', 'img/amenities/rowater.svg', 'Common Area'),
('Dining', 'img/amenities/dining.svg', 'Common Area'),
('Washing Machine', 'img/amenities/washingmachine.svg', 'Common Area'),
('Bed with Mattress', 'img/amenities/bed.svg', 'Bedroom'),
('Air Conditioner', 'img/amenities/ac.svg', 'Bedroom');

-- Link amenities to properties (example mapping)
INSERT INTO property_amenities (property_id, amenity_id) VALUES
(1,1),(1,3),(1,6),(1,8),
(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(2,8),(2,9),
(3,3),(3,6),(3,8),
(4,1),(4,3),(4,6),(4,8),
(5,1),(5,2),(5,3),(5,4),(5,7),(5,8),(5,9),
(6,3),(6,6),(6,8),(6,9);
