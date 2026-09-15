-- MediVerse Database Schema
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB, and SQLite

CREATE DATABASE IF NOT EXISTS mediverse_db;
USE mediverse_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Appointments Table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hospital VARCHAR(255) NOT NULL,
    department VARCHAR(100) NOT NULL,
    doctor_name VARCHAR(100) NOT NULL,
    appointment_date VARCHAR(50) NOT NULL,
    appointment_time VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Health Records Table
CREATE TABLE IF NOT EXISTS health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Medicine Reminders Table
CREATE TABLE IF NOT EXISTS medicine_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_name VARCHAR(255) NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Medicines Catalog Table
CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    store_location VARCHAR(255) NOT NULL,
    requires_prescription INT DEFAULT 0
);

-- 6. Medicine Orders Table
CREATE TABLE IF NOT EXISTS medicine_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    store_name VARCHAR(255) NOT NULL,
    payment_method VARCHAR(100) NOT NULL,
    prescription_required INT DEFAULT 0,
    prescription_file VARCHAR(255) DEFAULT NULL,
    order_status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Symptom Checks Table
CREATE TABLE IF NOT EXISTS symptom_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    symptoms TEXT NOT NULL,
    duration VARCHAR(100) NOT NULL,
    severity VARCHAR(100) NOT NULL,
    possible_issue TEXT,
    suggested_department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. Insurance Policies Table
CREATE TABLE IF NOT EXISTS insurance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider VARCHAR(255) NOT NULL,
    policy_number VARCHAR(100) NOT NULL,
    coverage_amount DECIMAL(12,2) NOT NULL,
    valid_till VARCHAR(50) NOT NULL,
    document VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Family Members Table
CREATE TABLE IF NOT EXISTS family_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    relation VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    availability VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 10. Emergency Requests Table
CREATE TABLE IF NOT EXISTS emergency_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    driver_name VARCHAR(255) NOT NULL,
    ambulance_no VARCHAR(100) NOT NULL,
    hospital VARCHAR(255) NOT NULL,
    status VARCHAR(100) DEFAULT 'On the way',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial Seed Data: Medicines
INSERT INTO medicines (name, price, store_location, requires_prescription) VALUES
('Paracetamol 650mg', 30.00, 'City Care Pharmacy', 0),
('Amoxicillin 500mg', 120.00, 'MediLife Chemist', 1),
('Cetirizine 10mg', 45.00, 'Apollo Healthcare', 0),
('Pantoprazole 40mg', 90.00, 'CarePlus Meds', 0),
('Azithromycin 500mg', 150.00, 'Sunrise Pharmacy', 1),
('Cough Syrup 100ml', 85.00, 'Wellness Hub', 0),
('Metformin 500mg', 65.00, 'City Care Pharmacy', 1),
('Vitamin C 500mg', 50.00, 'HealthFirst', 0);
