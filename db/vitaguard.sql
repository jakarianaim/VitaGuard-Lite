CREATE DATABASE IF NOT EXISTS vitaguard_db;
USE vitaguard_db;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor', 'pharmacist', 'admin') NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    status ENUM('Pending', 'Approved', 'Cancelled') DEFAULT 'Pending',
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS health_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    blood_pressure VARCHAR(20),
    blood_sugar VARCHAR(20),
    pulse_rate VARCHAR(20),
    temperature VARCHAR(20),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS medicines (
    medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    trade_name VARCHAR(100) NOT NULL,
    generic_name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    unit_price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT,
    doctor_id INT NOT NULL,
    patient_id INT NOT NULL,
    instructions TEXT,
    status ENUM('Active', 'Completed') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL,
    FOREIGN KEY (doctor_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS prescription_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL,
    medicine_id INT NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    frequency VARCHAR(50),
    duration_days INT NOT NULL,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS system_notices (
    notice_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    target_role ENUM('all', 'patient', 'doctor', 'pharmacist') DEFAULT 'all',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE
);

INSERT INTO users (user_id, name, email, password, role, phone) VALUES
(1, 'System Admin', 'admin@vitaguard.com', '123456', 'admin', '01711000000'),
(2, 'Dr. Sadman Sakib', 'doctor@vitaguard.com', '123456', 'doctor', '01711000001'),
(3, 'Tanvir Patient', 'patient@vitaguard.com', '123456', 'patient', '01711000002'),
(4, 'Nahid Pharmacist', 'pharmacist@vitaguard.com', '123456', 'pharmacist', '01711000003')
ON DUPLICATE KEY UPDATE name=VALUES(name), password=VALUES(password);

INSERT INTO medicines (medicine_id, trade_name, generic_name, category, unit_price, stock_quantity, expiry_date) VALUES
(1, 'Napa Extra 500mg', 'Paracetamol + Caffeine', 'Tablet', 3.00, 150, '2027-12-31'),
(2, 'Ace Plus', 'Paracetamol', 'Tablet', 2.50, 18, '2027-08-15'),
(3, 'Seclo 20mg', 'Omeprazole', 'Capsule', 7.00, 85, '2026-11-20'),
(4, 'Ciprocin 500mg', 'Ciprofloxacin', 'Tablet', 15.00, 45, '2027-05-10'),
(5, 'Fexo 120mg', 'Fexofenadine HCl', 'Tablet', 9.50, 12, '2026-09-30'),
(6, 'Tofen Syrup 100ml', 'Ketotifen', 'Syrup', 75.00, 30, '2027-03-25'),
(7, 'Azithromycin 500mg', 'Azithromycin', 'Tablet', 35.00, 60, '2027-07-14'),
(8, 'Insulatard 100IU', 'Human Insulin', 'Injection', 450.00, 15, '2026-12-10')
ON DUPLICATE KEY UPDATE trade_name=VALUES(trade_name);

INSERT INTO system_notices (notice_id, admin_id, title, description, target_role) VALUES
(1, 1, 'Scheduled Server Maintenance', 'VitaGuard portal will undergo maintenance this Sunday from 2 AM to 4 AM.', 'all'),
(2, 1, 'Prescription Protocol Update', 'Doctors are requested to include frequency and duration for all antibiotics.', 'doctor'),
(3, 1, 'Inventory Audit Notice', 'Please verify expiration dates and flag low stock items before month end.', 'pharmacist'),
(4, 1, 'Winter Health Checkup Camp', 'Free consultations for cardiovascular and blood sugar screening this Friday.', 'patient')
ON DUPLICATE KEY UPDATE title=VALUES(title);

INSERT INTO appointments (appointment_id, patient_id, doctor_id, appointment_date, time_slot, status, reason) VALUES
(1, 3, 2, '2026-09-20', '10:30 AM', 'Approved', 'Seasonal allergy follow-up and blood pressure check'),
(2, 3, 2, '2026-09-25', '04:00 PM', 'Pending', 'Persistent dry cough and mild fever')
ON DUPLICATE KEY UPDATE status=VALUES(status);

INSERT INTO health_records (record_id, patient_id, blood_pressure, blood_sugar, pulse_rate, temperature) VALUES
(1, 3, '120/80', '95.5', '72', '98.6'),
(2, 3, '125/82', '102.0', '76', '99.1')
ON DUPLICATE KEY UPDATE blood_pressure=VALUES(blood_pressure);

INSERT INTO prescriptions (prescription_id, appointment_id, doctor_id, patient_id, instructions, status) VALUES
(1, 1, 2, 3, 'Take medicines after meals. Drink at least 2.5L of water daily and avoid cold drinks.', 'Active')
ON DUPLICATE KEY UPDATE status=VALUES(status);

INSERT INTO prescription_items (item_id, prescription_id, medicine_id, dosage, frequency, duration_days) VALUES
(1, 1, 1, '500mg', '1+0+1', 5),
(2, 1, 3, '20mg', '1+0+0', 14),
(3, 1, 5, '120mg', '0+0+1', 7)
ON DUPLICATE KEY UPDATE dosage=VALUES(dosage);