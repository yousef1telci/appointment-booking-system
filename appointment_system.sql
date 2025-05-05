-- Database creation
CREATE DATABASE appointment_system;
USE appointment_system;

-- Users table (for both customers and service providers)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    user_type ENUM('customer', 'provider') NOT NULL,
    name VARCHAR(100) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Availability table (for service providers to set their available time slots)
CREATE TABLE availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    date DATE NOT NULL,
    time_start TIME NOT NULL,
    time_end TIME NOT NULL,
    is_booked BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Appointments table (for booking)
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    availability_id INT NOT NULL,
    customer_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (availability_id) REFERENCES availability(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);