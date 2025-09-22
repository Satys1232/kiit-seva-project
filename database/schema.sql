-- ============================================================================
-- KIIT SEVA - University Platform Database Schema
-- Complete MySQL database structure with sample data
-- Created for: KIIT University, Bhubaneswar, India
-- Version: 1.0
-- Character Set: UTF8MB4 for full Unicode support
-- ============================================================================

-- Database Creation and Configuration
DROP DATABASE IF EXISTS kiit_seva;
CREATE DATABASE kiit_seva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kiit_seva;

-- Set SQL mode for strict data integrity
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- ============================================================================
-- TABLE: users
-- Purpose: Core user authentication and profile management for all system users
-- Stores login credentials and basic information for students, teachers, and staff
-- ============================================================================

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique user identifier',
    name VARCHAR(100) NOT NULL COMMENT 'Full name of the user',
    email VARCHAR(100) UNIQUE NOT NULL COMMENT 'University email address (unique login identifier)',
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password using PHP password_hash()',
    role ENUM('student', 'teacher', 'staff') NOT NULL COMMENT 'User role determining access permissions',
    phone VARCHAR(20) COMMENT 'Contact phone number (optional)',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Account status - TRUE for active accounts',
    email_verified BOOLEAN DEFAULT FALSE COMMENT 'Email verification status',
    last_login TIMESTAMP NULL COMMENT 'Last successful login timestamp',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last profile update timestamp',

    -- Indexes for performance optimization
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB COMMENT='Central user management table for authentication and profiles';

-- ============================================================================
-- TABLE: teachers
-- Purpose: Extended profile information specific to teaching faculty
-- Stores academic details, availability, and chamber information
-- ============================================================================

DROP TABLE IF EXISTS teachers;
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique teacher profile identifier',
    user_id INT NOT NULL COMMENT 'Reference to users table for login credentials',
    department VARCHAR(100) NOT NULL COMMENT 'Academic department (CS, Math, Physics, etc.)',
    chamber_no VARCHAR(50) COMMENT 'Office/chamber number for student visits',
    qualification VARCHAR(200) COMMENT 'Educational qualifications and degrees',
    specialization VARCHAR(200) COMMENT 'Areas of expertise and research interests',
    bio TEXT COMMENT 'Professional biography and teaching philosophy',
    profile_image VARCHAR(255) COMMENT 'Path to profile photo file',
    available_slots JSON COMMENT 'Weekly availability schedule in JSON format',
    max_bookings_per_day INT DEFAULT 8 COMMENT 'Maximum student appointments per day',
    advance_booking_days INT DEFAULT 7 COMMENT 'How many days in advance students can book',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Teacher availability status',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Profile creation timestamp',

    -- Foreign key constraints
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    -- Indexes for efficient queries
    INDEX idx_user_id (user_id),
    INDEX idx_department (department),
    INDEX idx_active (is_active)
) ENGINE=InnoDB COMMENT='Teacher profile information and availability management';

-- ============================================================================
-- TABLE: bookings
-- Purpose: Manage student-teacher appointment scheduling system
-- Handles booking requests, confirmations, and appointment tracking
-- ============================================================================

DROP TABLE IF EXISTS bookings;
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique booking identifier',
    student_id INT NOT NULL COMMENT 'Student making the appointment (references users.id)',
    teacher_id INT NOT NULL COMMENT 'Teacher being booked (references users.id)',
    booking_date DATE NOT NULL COMMENT 'Date of the scheduled appointment',
    time_slot VARCHAR(50) NOT NULL COMMENT 'Time slot in format "09:00-10:00" (24-hour)',
    purpose TEXT NOT NULL COMMENT 'Reason/purpose for the meeting (required)',
    status ENUM('booked', 'confirmed', 'completed', 'cancelled') DEFAULT 'booked' COMMENT 'Appointment status workflow tracking',
    teacher_notes TEXT COMMENT 'Teacher notes about the appointment',
    student_rating INT COMMENT 'Post-meeting rating by student (1-5 stars)',
    student_feedback TEXT COMMENT 'Student feedback about the meeting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Booking creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last status update timestamp',

    -- Foreign key constraints
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,

    -- Unique constraint to prevent double booking
    UNIQUE KEY unique_booking (teacher_id, booking_date, time_slot),

    -- Check constraint for rating validation
    CONSTRAINT chk_rating CHECK (student_rating IS NULL OR (student_rating >= 1 AND student_rating <= 5)),

    -- Indexes for performance
    INDEX idx_student_bookings (student_id, booking_date),
    INDEX idx_teacher_bookings (teacher_id, booking_date),
    INDEX idx_booking_status (status),
    INDEX idx_booking_date (booking_date)
) ENGINE=InnoDB COMMENT='Student-teacher appointment booking and management system';

-- ============================================================================
-- TABLE: vehicles
-- Purpose: Campus transportation fleet tracking and management
-- Stores vehicle information, routes, and real-time location data
-- ============================================================================

DROP TABLE IF EXISTS vehicles;
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique vehicle identifier',
    vehicle_number VARCHAR(50) UNIQUE NOT NULL COMMENT 'Vehicle registration number (must be unique)',
    route ENUM('Campus-15', 'Campus-17', 'Campus-25') NOT NULL COMMENT 'Assigned campus route (Campus-15, Campus-17, Campus-25)',
    driver_name VARCHAR(100) NOT NULL COMMENT 'Assigned driver full name',
    driver_id INT COMMENT 'Reference to driver user account (optional)',
    driver_phone VARCHAR(20) COMMENT 'Driver contact number for emergencies',
    capacity INT DEFAULT 40 COMMENT 'Maximum passenger capacity',
    current_load INT DEFAULT 0 COMMENT 'Current number of passengers',
    current_lat DECIMAL(10, 8) COMMENT 'Current GPS latitude coordinate',
    current_lng DECIMAL(11, 8) COMMENT 'Current GPS longitude coordinate',
    is_active BOOLEAN DEFAULT FALSE COMMENT 'Vehicle operational status',
    duty_status ENUM('ON_DUTY', 'OFF_DUTY', 'BREAK', 'MAINTENANCE') DEFAULT 'OFF_DUTY' COMMENT 'Current duty status of the vehicle',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last GPS location update timestamp',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Vehicle registration timestamp',

    -- Foreign key constraints
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL,

    -- Indexes for efficient tracking queries
    INDEX idx_route (route),
    INDEX idx_active (is_active),
    INDEX idx_location (current_lat, current_lng),
    INDEX idx_vehicle_number (vehicle_number)
) ENGINE=InnoDB COMMENT='Campus vehicle fleet management and GPS tracking';

-- ============================================================================
-- TABLE: feedback
-- Purpose: User feedback collection and management system
-- Handles service ratings, suggestions, and complaint tracking
-- ============================================================================

DROP TABLE IF EXISTS feedback;
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique feedback identifier',
    user_id INT NOT NULL COMMENT 'User submitting feedback (references users.id)',
    category ENUM('service', 'food', 'transport', 'faculty', 'infrastructure') DEFAULT 'service' COMMENT 'Feedback category for classification and routing',
    subject VARCHAR(200) NOT NULL COMMENT 'Brief summary/title of the feedback',
    message TEXT NOT NULL COMMENT 'Detailed feedback message or description',
    rating INT NOT NULL COMMENT 'Service rating from 1-5 stars (required)',
    is_anonymous BOOLEAN DEFAULT FALSE COMMENT 'Whether feedback should be displayed anonymously',
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending' COMMENT 'Admin review and resolution status',
    admin_response TEXT COMMENT 'Administrative response to the feedback',
    admin_id INT COMMENT 'Admin user who responded to feedback',
    resolved_at TIMESTAMP NULL COMMENT 'Timestamp when feedback was marked resolved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Feedback submission timestamp',

    -- Foreign key constraints
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,

    -- Check constraint for rating validation
    CONSTRAINT chk_feedback_rating CHECK (rating >= 1 AND rating <= 5),

    -- Indexes for feedback management
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at),
    INDEX idx_user_feedback (user_id)
) ENGINE=InnoDB COMMENT='User feedback collection and administrative response system';

-- ============================================================================
-- SAMPLE DATA INSERTION
-- Realistic university data for testing and development
-- All passwords are hashed using PHP password_hash() equivalent
-- ============================================================================

-- Insert sample users with hashed passwords
-- Note: In production, passwords should be hashed using PHP password_hash()
-- For development, these are bcrypt hashes of simple passwords
INSERT INTO users (name, email, password, role, phone, is_active, email_verified) VALUES
('Rahul Sharma', 'rahul.sharma@kiit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '+91-9876543210', TRUE, TRUE),
('Dr. Priya Patel', 'priya.patel@kiit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '+91-9876543211', TRUE, TRUE),
('Amit Kumar', 'amit.kumar@kiit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', '+91-9876543212', TRUE, TRUE),
('Sneha Singh', 'sneha.singh@kiit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '+91-9876543213', TRUE, TRUE),
('Prof. Rajesh Gupta', 'rajesh.gupta@kiit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '+91-9876543214', TRUE, TRUE);

-- Insert teacher profiles with availability schedules
INSERT INTO teachers (user_id, department, chamber_no, qualification, specialization, available_slots, is_active) VALUES
(2, 'Computer Science', 'CS-204', 'Ph.D in Computer Science, M.Tech in AI', 'Artificial Intelligence, Machine Learning, Data Science',
 '{"monday":["09:00-10:00","14:00-15:00","16:00-17:00"],"tuesday":["10:00-11:00","15:00-16:00"],"wednesday":["09:00-10:00","14:00-15:00"],"thursday":["11:00-12:00","16:00-17:00"],"friday":["09:00-10:00","14:00-15:00"]}',
 TRUE),
(5, 'Mathematics', 'MATH-101', 'Ph.D in Applied Mathematics, M.Sc in Statistics', 'Applied Mathematics, Statistical Analysis, Numerical Methods',
 '{"monday":["10:00-11:00","15:00-16:00"],"tuesday":["09:00-10:00","14:00-15:00","16:00-17:00"],"wednesday":["11:00-12:00","15:00-16:00"],"thursday":["09:00-10:00","14:00-15:00"],"friday":["10:00-11:00","16:00-17:00"]}',
 TRUE);

-- Insert campus vehicles with route assignments
INSERT INTO vehicles (vehicle_number, route, driver_name, driver_id, driver_phone, capacity, is_active, duty_status) VALUES
('KU-2501', 'Campus-15', 'Suresh Das', 3, '+91-9876543220', 40, TRUE, 'ON_DUTY'),
('KU-1702', 'Campus-17', 'Ravi Yadav', NULL, '+91-9876543221', 35, TRUE, 'ON_DUTY'),
('KU-2503', 'Campus-25', 'Manoj Singh', NULL, '+91-9876543222', 42, FALSE, 'MAINTENANCE');

-- Insert sample bookings for demonstration
INSERT INTO bookings (student_id, teacher_id, booking_date, time_slot, purpose, status) VALUES
(1, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00-15:00', 'Discuss final year project proposal on Machine Learning applications in healthcare', 'booked'),
(4, 5, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '10:00-11:00', 'Clarification on Statistical Methods assignment and exam preparation', 'confirmed');

-- Insert sample feedback entries
INSERT INTO feedback (user_id, category, subject, message, rating, is_anonymous, status) VALUES
(1, 'service', 'Excellent Teacher Booking System', 'The new teacher booking system is very user-friendly and efficient. I was able to book an appointment with Dr. Priya easily and got confirmation immediately. The calendar interface is intuitive and shows real-time availability. Great work by the development team!', 5, FALSE, 'reviewed'),
(4, 'transport', 'Vehicle Tracking Needs Improvement', 'The vehicle tracking feature is helpful but could be more accurate. Sometimes the bus location shown on the map is not updated in real-time, causing students to miss buses. Suggest implementing more frequent GPS updates and better route optimization.', 4, FALSE, 'pending');

-- ============================================================================
-- DATABASE VIEWS FOR COMMON QUERIES
-- Pre-defined views for frequently accessed data combinations
-- ============================================================================

-- View: Active Teachers with Department Info
CREATE VIEW active_teachers AS
SELECT
    u.id as user_id,
    u.name,
    u.email,
    t.department,
    t.chamber_no,
    t.qualification,
    t.specialization,
    t.available_slots
FROM users u
INNER JOIN teachers t ON u.id = t.user_id
WHERE u.is_active = TRUE AND t.is_active = TRUE;

-- View: Current Active Vehicles
CREATE VIEW active_vehicles AS
SELECT
    id,
    vehicle_number,
    route,
    driver_name,
    driver_phone,
    capacity,
    current_load,
    current_lat,
    current_lng,
    duty_status,
    last_updated
FROM vehicles
WHERE is_active = TRUE;

-- View: Recent Bookings with User Details
CREATE VIEW booking_details AS
SELECT
    b.id,
    s.name as student_name,
    s.email as student_email,
    t.name as teacher_name,
    t.email as teacher_email,
    b.booking_date,
    b.time_slot,
    b.purpose,
    b.status,
    b.created_at
FROM bookings b
INNER JOIN users s ON b.student_id = s.id
INNER JOIN users t ON b.teacher_id = t.id
ORDER BY b.created_at DESC;

-- ============================================================================
-- STORED PROCEDURES FOR COMMON OPERATIONS
-- Encapsulated business logic for complex operations
-- ============================================================================

DELIMITER //

-- Procedure: Check Teacher Availability
CREATE PROCEDURE CheckTeacherAvailability(
    IN p_teacher_id INT,
    IN p_booking_date DATE,
    IN p_time_slot VARCHAR(50)
)
BEGIN
    SELECT COUNT(*) as booking_exists
    FROM bookings
    WHERE teacher_id = p_teacher_id
      AND booking_date = p_booking_date
      AND time_slot = p_time_slot
      AND status NOT IN ('cancelled');
END //

-- Procedure: Get Teacher Bookings for Date Range
CREATE PROCEDURE GetTeacherBookings(
    IN p_teacher_id INT,
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT
        b.*,
        s.name as student_name,
        s.email as student_email
    FROM bookings b
    INNER JOIN users s ON b.student_id = s.id
    WHERE b.teacher_id = p_teacher_id
      AND b.booking_date BETWEEN p_start_date AND p_end_date
    ORDER BY b.booking_date, b.time_slot;
END //

DELIMITER ;

-- ============================================================================
-- PERFORMANCE OPTIMIZATION NOTES
-- ============================================================================

-- Additional indexes for complex queries (uncomment as needed)
-- CREATE INDEX idx_bookings_date_status ON bookings(booking_date, status);
-- CREATE INDEX idx_feedback_category_rating ON feedback(category, rating);
-- CREATE INDEX idx_users_role_active ON users(role, is_active);

-- ============================================================================
-- SECURITY AND MAINTENANCE RECOMMENDATIONS
-- ============================================================================

/*
SECURITY BEST PRACTICES:
1. Always use prepared statements in application code
2. Regularly update passwords and use strong hashing algorithms
3. Implement proper session management
4. Use HTTPS for all database connections in production
5. Regular security audits and penetration testing

MAINTENANCE RECOMMENDATIONS:
1. Regular backup schedule (daily recommended)
2. Monitor slow queries and optimize indexes
3. Archive old booking records (older than 2 years)
4. Clean up inactive user accounts periodically
5. Monitor database size and implement partitioning if needed

PERFORMANCE MONITORING:
- Monitor query execution times
- Check index usage statistics
- Implement query caching for read-heavy operations
- Consider read replicas for reporting queries
*/

-- ============================================================================
-- END OF SCHEMA
-- Database initialization complete
-- Total tables: 5 (users, teachers, bookings, vehicles, feedback)
-- Total views: 3 (active_teachers, active_vehicles, booking_details)
-- Total procedures: 2 (CheckTeacherAvailability, GetTeacherBookings)
-- Sample records: 5 users, 2 teachers, 3 vehicles, 2 bookings, 2 feedback
-- ============================================================================

-- Display success message
SELECT 'KIIT SEVA database schema created successfully!' as status,
       (SELECT COUNT(*) FROM users) as total_users,
       (SELECT COUNT(*) FROM teachers) as total_teachers,
       (SELECT COUNT(*) FROM vehicles) as total_vehicles,
       (SELECT COUNT(*) FROM bookings) as total_bookings,
       (SELECT COUNT(*) FROM feedback) as total_feedback;