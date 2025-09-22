# CLAUDE_CONTEXT.md
## DEFINITIVE REFERENCE FOR KIIT SEVA PROJECT - AI CODE GENERATION

---

## 🎓 PROJECT OVERVIEW

### **Project Identity**
- **Name:** KIIT SEVA (Student Services Platform)
- **Institution:** KIIT University, Bhubaneswar, India
- **Type:** Web-based University Management System
- **Grade:** Professional-grade enterprise application
- **Timeline:** 2-3 hours with AI assistance
- **Deployment:** Production-ready university platform

### **Problem Statement**
Current KIIT University services suffer from:
- Manual teacher appointment scheduling causing conflicts
- No real-time campus transportation tracking
- Fragmented feedback collection systems
- Paper-based processes leading to inefficiency
- No centralized role-based service access

### **Solution Overview**
KIIT SEVA provides unified digital platform with:
- **Real-time teacher booking** with conflict prevention
- **Live GPS vehicle tracking** for campus routes
- **Centralized feedback system** with analytics
- **Role-based access control** (Student/Teacher/Staff)
- **Mobile-first responsive design** for universal access

### **Target Users & Needs**
1. **Students (Primary Users):**
   - Book teacher appointments efficiently
   - Track campus bus locations and ETAs
   - Provide service feedback and ratings
   - Access personalized dashboard

2. **Teachers (Service Providers):**
   - Manage availability and appointment slots
   - View and confirm booking requests
   - Update chamber and profile information
   - Review student feedback

3. **Staff (System Operators):**
   - Update vehicle locations and duty status
   - Moderate feedback and respond
   - Access analytics and system reports
   - Manage operational parameters

### **Project Scope**
- **Core Features:** Authentication, Booking, Tracking, Feedback
- **Security:** Enterprise-grade with role-based access
- **Performance:** < 3 second page loads, 100+ concurrent users
- **Accessibility:** WCAG 2.1 AA compliance
- **Compatibility:** All modern browsers, mobile-optimized

---

## 💻 TECHNICAL ARCHITECTURE

### **Technology Stack Specifications**

#### **Backend Framework**
- **PHP 7.4+** (Required minimum version)
  - Pure PHP implementation - NO external frameworks
  - Object-oriented programming patterns
  - PSR-4 autoloading standards
  - Built-in security functions (password_hash, htmlspecialchars)

#### **Database System**
- **MySQL 5.7+** or **MariaDB 10.3+**
  - InnoDB storage engine for ACID compliance
  - JSON data type support for flexible storage
  - Full-text search capabilities
  - Proper indexing strategy for performance

#### **Data Access Layer**
- **PDO (PHP Data Objects)** - EXCLUSIVE database access
  - Prepared statements ONLY - no direct SQL
  - Transaction support for data integrity
  - Proper error handling and exception management
  - Connection pooling and timeout management

#### **Frontend Technologies**
- **HTML5** with semantic markup
  - Form validation attributes
  - ARIA labels for accessibility
  - Progressive enhancement support
  - SEO-friendly structure

- **CSS3** for styling and layout
  - NO CSS frameworks (Bootstrap, Tailwind, etc.)
  - Custom CSS with CSS Grid and Flexbox
  - CSS Custom Properties for theming
  - Media queries for responsive design

- **Vanilla JavaScript (ES6+)**
  - NO external JS frameworks (jQuery, React, etc.)
  - Modern ES6+ features (arrow functions, async/await)
  - DOM manipulation and event handling
  - AJAX for asynchronous operations

### **MVC Architecture Pattern**
```
Model (Data Layer):
├── Database interaction through PDO
├── Business logic and validation
├── Data sanitization and security
└── Relationship management

View (Presentation Layer):
├── HTML templates with embedded PHP
├── Clean separation of logic and presentation
├── Reusable template components
└── Mobile-first responsive layouts

Controller (Business Logic Layer):
├── Request handling and routing
├── Input validation and authentication
├── Model coordination and view rendering
└── Session and security management
```

### **Development Environment**
- **Local Stack:** XAMPP 7.4+ / WAMP / MAMP
  - Apache 2.4+ web server
  - MySQL 5.7+ database server
  - PHP 7.4+ runtime with extensions
  - phpMyAdmin for database management

### **File Organization & Naming Conventions**
- **Classes:** PascalCase (UserController, BookingModel)
- **Methods/Functions:** camelCase (getUserById, validateInput)
- **Variables:** camelCase ($userData, $isValid)
- **Constants:** UPPER_SNAKE_CASE (DB_HOST, MAX_SLOTS)
- **Files:** lowercase with hyphens (user-profile.php, booking-history.php)
- **Database:** snake_case (user_id, created_at, booking_date)

---

## ⚡ CORE FEATURES SPECIFICATION

### **1. TEACHER BOOKING SYSTEM**

#### **User Workflow:**
```
Student Journey:
1. Login → Dashboard → "Teacher Booking"
2. Browse teachers by department/search
3. Select teacher → View availability calendar
4. Choose time slot → Enter meeting purpose
5. Submit booking → Receive confirmation
6. Track status → Attend meeting → Complete

Teacher Journey:
1. Login → Dashboard → "My Bookings"
2. View pending booking requests
3. Confirm/Decline with reason
4. Update availability slots
5. Mark meetings as completed
6. View booking history and analytics
```

#### **Technical Implementation:**
- **Time Slot Format:** "HH:MM-HH:MM" (24-hour format, 1-hour slots)
- **Availability Storage:** JSON in teachers table
  ```json
  {
    "monday": ["09:00-10:00", "14:00-15:00", "16:00-17:00"],
    "tuesday": ["10:00-11:00", "15:00-16:00"],
    "wednesday": ["09:00-10:00", "14:00-15:00"],
    "thursday": ["11:00-12:00", "16:00-17:00"],
    "friday": ["09:00-10:00", "14:00-15:00"]
  }
  ```

#### **Status Management:**
- **booked:** Initial booking by student
- **confirmed:** Teacher approved the booking
- **completed:** Meeting finished successfully
- **cancelled:** Booking cancelled by either party

#### **Conflict Prevention:**
- Database UNIQUE constraint on (teacher_id, booking_date, time_slot)
- Real-time AJAX availability checking
- Pessimistic locking during booking process
- Double-booking validation at application level

#### **Email Notifications:**
- Booking confirmation to both parties
- Reminder 1 hour before meeting
- Cancellation notifications
- Status change updates

### **2. VEHICLE TRACKING SYSTEM**

#### **GPS Integration Approach:**
- **Location Storage:** DECIMAL(10,8) for latitude, DECIMAL(11,8) for longitude
- **Update Frequency:** Every 30 seconds during active duty
- **Accuracy Requirement:** Within 10 meters of actual position
- **Offline Handling:** Store locations locally, sync when online

#### **Route Management:**
- **Route Names:** "Campus-15", "Campus-17", "Campus-25"
- **Route Data Structure:**
  ```json
  {
    "route_id": "campus-15",
    "stops": [
      {"name": "Main Gate", "lat": 20.2499, "lng": 85.8131},
      {"name": "Academic Block", "lat": 20.2509, "lng": 85.8141},
      {"name": "Hostel Complex", "lat": 20.2519, "lng": 85.8151}
    ],
    "total_distance": 5.2,
    "estimated_duration": 15
  }
  ```

#### **Staff Duty Management:**
- **Duty Status:** ON_DUTY, OFF_DUTY, BREAK, MAINTENANCE
- **Location Updates:** Manual override + automatic GPS
- **Shift Management:** Start/End duty with timestamp logging
- **Vehicle Assignment:** One driver per vehicle per shift

#### **ETA Calculation:**
- **Algorithm:** Distance-based with traffic factor
- **Update Frequency:** Every location update (30 seconds)
- **Factors:** Current location, next stop distance, average speed
- **Display Format:** "Arriving in 5-8 minutes"

### **3. FEEDBACK SYSTEM**

#### **Star Rating System:**
- **Scale:** 1-5 stars (integer validation)
- **Visual:** CSS-based star display with hover effects
- **Validation:** Database CHECK constraint (rating >= 1 AND rating <= 5)
- **Aggregation:** Average rating with total count display

#### **Category-Based Collection:**
- **Categories:** 'service', 'food', 'transport', 'faculty', 'infrastructure'
- **Category Icons:** Font-awesome or custom SVG icons
- **Filtering:** Admin dashboard filtering by category
- **Analytics:** Category-wise rating trends

#### **Anonymous Feedback:**
- **Option:** Checkbox for anonymous submission
- **Storage:** is_anonymous BOOLEAN flag
- **Display:** "Anonymous User" instead of name
- **Moderation:** Same review process as non-anonymous

#### **Admin Moderation Workflow:**
```
Feedback Submission → Pending Review → Admin Review →
(Approve → Published) OR (Reject → Deleted) OR (Request Changes → Back to User)
```

#### **Response System:**
- **Admin Response:** Text response to feedback
- **User Notification:** Email notification of admin response
- **Thread Display:** Feedback + Admin response displayed together
- **Resolution Status:** Open, In Progress, Resolved

---

## 🗄️ DATABASE ARCHITECTURE

### **Complete Table Specifications**

#### **users Table**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- bcrypt hashed
    role ENUM('student', 'teacher', 'staff') NOT NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
);
```

#### **teachers Table**
```sql
CREATE TABLE teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    department VARCHAR(100) NOT NULL,
    chamber_no VARCHAR(50),
    phone VARCHAR(15),
    profile_image VARCHAR(255),
    bio TEXT,
    specialization VARCHAR(200),
    available_slots JSON, -- Weekly availability
    max_bookings_per_day INT DEFAULT 8,
    advance_booking_days INT DEFAULT 7,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_department (department),
    INDEX idx_user_id (user_id)
);
```

#### **bookings Table**
```sql
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,
    booking_date DATE NOT NULL,
    time_slot VARCHAR(20) NOT NULL, -- "09:00-10:00"
    purpose TEXT NOT NULL,
    status ENUM('booked', 'confirmed', 'completed', 'cancelled') DEFAULT 'booked',
    teacher_notes TEXT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking (teacher_id, booking_date, time_slot),
    INDEX idx_student_bookings (student_id, booking_date),
    INDEX idx_teacher_bookings (teacher_id, booking_date),
    INDEX idx_booking_status (status)
);
```

#### **vehicles Table**
```sql
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_number VARCHAR(50) UNIQUE NOT NULL,
    route ENUM('Campus-15', 'Campus-17', 'Campus-25') NOT NULL,
    driver_name VARCHAR(100) NOT NULL,
    driver_id INT,
    driver_phone VARCHAR(15),
    current_lat DECIMAL(10, 8),
    current_lng DECIMAL(11, 8),
    is_active BOOLEAN DEFAULT FALSE,
    duty_status ENUM('ON_DUTY', 'OFF_DUTY', 'BREAK', 'MAINTENANCE') DEFAULT 'OFF_DUTY',
    capacity INT DEFAULT 40,
    current_load INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_route (route),
    INDEX idx_active (is_active),
    INDEX idx_location (current_lat, current_lng)
);
```

#### **feedback Table**
```sql
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    category ENUM('service', 'food', 'transport', 'faculty', 'infrastructure') DEFAULT 'service',
    is_anonymous BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'approved', 'rejected', 'resolved') DEFAULT 'pending',
    admin_response TEXT,
    admin_id INT,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at)
);
```

### **Relationship Mapping**
```
users (1) ←→ (1) teachers [user_id]
users (1) ←→ (∞) bookings [student_id, teacher_id]
users (1) ←→ (∞) vehicles [driver_id]
users (1) ←→ (∞) feedback [user_id, admin_id]
teachers (1) ←→ (∞) bookings [teacher_id]
```

### **Indexing Strategy**
- **Primary Keys:** Auto-increment integers for performance
- **Foreign Keys:** Always indexed for join optimization
- **Search Fields:** email, department, route, status fields
- **Date Fields:** created_at, booking_date, last_updated
- **Composite Indexes:** (teacher_id, booking_date), (current_lat, current_lng)

### **Sample Data Requirements**
- **Users:** 50 students, 20 teachers, 10 staff members
- **Teachers:** Various departments (CS, ECE, Mech, Civil, etc.)
- **Vehicles:** 15 buses across 3 routes
- **Bookings:** 100+ historical bookings with different statuses
- **Feedback:** 200+ entries across all categories

---

## 🎨 UI/UX DESIGN SYSTEM

### **Complete Color Palette**
```css
:root {
  /* Primary Colors */
  --primary-blue: #4a90e2;
  --primary-blue-dark: #357abd;
  --primary-blue-light: #6ba3e8;

  /* Secondary Colors */
  --secondary-gray: #6c757d;
  --secondary-gray-dark: #545b62;
  --secondary-gray-light: #868e96;

  /* Status Colors */
  --success-green: #28a745;
  --success-green-dark: #1e7e34;
  --success-green-light: #34ce57;

  --warning-yellow: #ffc107;
  --warning-yellow-dark: #d39e00;
  --warning-yellow-light: #ffcd39;

  --danger-red: #dc3545;
  --danger-red-dark: #bd2130;
  --danger-red-light: #e15765;

  /* Neutral Colors */
  --white: #ffffff;
  --light-gray: #f8f9fa;
  --medium-gray: #e9ecef;
  --dark-gray: #333333;
  --black: #000000;

  /* Background Colors */
  --bg-primary: #f8f9fa;
  --bg-secondary: #ffffff;
  --bg-accent: #e8f4f8;

  /* Text Colors */
  --text-primary: #333333;
  --text-secondary: #6c757d;
  --text-muted: #999999;
  --text-white: #ffffff;
}
```

### **Typography Specifications**
```css
/* Font Family Stack */
font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Tahoma, Geneva, Verdana, sans-serif;

/* Font Sizes & Weights */
--font-size-xs: 0.75rem;    /* 12px */
--font-size-sm: 0.875rem;   /* 14px */
--font-size-base: 1rem;     /* 16px */
--font-size-lg: 1.125rem;   /* 18px */
--font-size-xl: 1.25rem;    /* 20px */
--font-size-2xl: 1.5rem;    /* 24px */
--font-size-3xl: 2rem;      /* 32px */
--font-size-4xl: 2.5rem;    /* 40px */

/* Headings */
h1 { font-size: var(--font-size-4xl); font-weight: 600; }
h2 { font-size: var(--font-size-3xl); font-weight: 600; }
h3 { font-size: var(--font-size-2xl); font-weight: 600; }
h4 { font-size: var(--font-size-xl); font-weight: 500; }
h5 { font-size: var(--font-size-lg); font-weight: 500; }
h6 { font-size: var(--font-size-base); font-weight: 500; }

/* Body Text */
body { font-size: var(--font-size-base); font-weight: 400; line-height: 1.6; }
```

### **Component Design Patterns**

#### **Navigation Bar**
```css
.navbar {
  height: 70px;
  background: var(--white);
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.navbar-brand {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--primary-blue);
}

.navbar-menu {
  display: flex;
  gap: 2rem;
}

@media (max-width: 768px) {
  .navbar-menu {
    display: none; /* Show hamburger menu */
  }
}
```

#### **Card Components**
```css
.card {
  background: var(--white);
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  padding: 2rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.card-header {
  border-bottom: 1px solid var(--medium-gray);
  padding-bottom: 1rem;
  margin-bottom: 1.5rem;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}
```

#### **Button Styles**
```css
.btn {
  display: inline-flex;
  align-items: center;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  cursor: pointer;
  border: none;
  font-size: 1rem;
}

.btn-primary {
  background: var(--primary-blue);
  color: var(--white);
}

.btn-primary:hover {
  background: var(--primary-blue-dark);
  transform: translateY(-2px);
}

.btn-secondary {
  background: transparent;
  color: var(--primary-blue);
  border: 2px solid var(--primary-blue);
}

.btn-secondary:hover {
  background: var(--primary-blue);
  color: var(--white);
}
```

#### **Form Elements**
```css
.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: var(--text-primary);
}

.form-control {
  width: 100%;
  padding: 15px;
  border: 1px solid var(--medium-gray);
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-blue);
  box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.form-control.is-invalid {
  border-color: var(--danger-red);
}

.form-control.is-valid {
  border-color: var(--success-green);
}

.invalid-feedback {
  color: var(--danger-red);
  font-size: 0.875rem;
  margin-top: 0.25rem;
}
```

### **Responsive Design Breakpoints**
```css
/* Mobile First Approach */
/* Base styles for mobile (< 576px) */

/* Small devices (tablets, 576px and up) */
@media (min-width: 576px) { }

/* Medium devices (desktops, 768px and up) */
@media (min-width: 768px) { }

/* Large devices (large desktops, 1200px and up) */
@media (min-width: 1200px) { }

/* Extra large devices (1400px and up) */
@media (min-width: 1400px) { }
```

### **Page Layout Specifications**

#### **Dashboard Layout**
```css
.dashboard-container {
  display: grid;
  grid-template-columns: 250px 1fr;
  min-height: 100vh;
}

.sidebar {
  background: var(--white);
  border-right: 1px solid var(--medium-gray);
  padding: 2rem 0;
}

.main-content {
  padding: 2rem;
  background: var(--bg-primary);
}

@media (max-width: 768px) {
  .dashboard-container {
    grid-template-columns: 1fr;
  }

  .sidebar {
    display: none; /* Toggle with mobile menu */
  }
}
```

---

## 🔒 SECURITY ARCHITECTURE

### **Authentication & Authorization**

#### **Password Security**
```php
// Password Requirements
$password_requirements = [
    'min_length' => 8,
    'require_uppercase' => true,
    'require_lowercase' => true,
    'require_numbers' => true,
    'require_special_chars' => false, // Optional for user experience
    'max_age_days' => 90, // Force password change
];

// Password Hashing
$hashed_password = password_hash($password, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536, // 64 MB
    'time_cost' => 4,       // 4 iterations
    'threads' => 3,         // 3 threads
]);

// Password Verification
$is_valid = password_verify($password, $hashed_password);
```

#### **Session Management**
```php
// Secure Session Configuration
session_set_cookie_params([
    'lifetime' => 1800, // 30 minutes
    'path' => '/',
    'domain' => '', // Current domain
    'secure' => true, // HTTPS only
    'httponly' => true, // No JavaScript access
    'samesite' => 'Strict' // CSRF protection
]);

// Session Regeneration
if (isset($_SESSION['user_id'])) {
    if (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
```

#### **CSRF Protection**
```php
// Generate CSRF Token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF Token
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}
```

### **Data Protection**

#### **Input Validation**
```php
class InputValidator {
    public static function sanitizeString($input) {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }

    public static function validateEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateInteger($value, $min = null, $max = null) {
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if ($value === false) return false;

        if ($min !== null && $value < $min) return false;
        if ($max !== null && $value > $max) return false;

        return $value;
    }

    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
```

#### **SQL Injection Prevention**
```php
// ALWAYS use prepared statements
class SecureDatabase {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createUser($name, $email, $password, $role) {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, password, role)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$name, $email, $password, $role]);
    }
}
```

#### **XSS Prevention**
```php
// Output Encoding
function safeOutput($data) {
    if (is_array($data)) {
        return array_map('safeOutput', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Template Helper
function e($data) {
    return safeOutput($data);
}
```

### **Role-Based Access Control**
```php
class RoleMiddleware {
    private static $rolePermissions = [
        'student' => [
            'booking.create', 'booking.view', 'booking.cancel',
            'tracking.view', 'feedback.create'
        ],
        'teacher' => [
            'booking.manage', 'booking.confirm', 'profile.edit',
            'feedback.view'
        ],
        'staff' => [
            'vehicle.update', 'tracking.manage', 'feedback.moderate',
            'admin.dashboard'
        ]
    ];

    public static function checkPermission($role, $permission) {
        return in_array($permission, self::$rolePermissions[$role] ?? []);
    }

    public static function requireRole($role) {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
            http_response_code(403);
            header('Location: /unauthorized');
            exit();
        }
    }
}
```

---

## 📁 PROJECT STRUCTURE

### **Complete MVC Directory Organization**
```
kiit-seva/
├── 📄 .env                           # Environment configuration
├── 📄 .env.example                   # Environment template
├── 📄 .gitignore                     # Git ignore rules
├── 📄 .htaccess                      # Apache URL rewriting
├── 📄 composer.json                  # PHP dependencies (optional)
├── 📄 README.md                      # Project documentation
├── 📄 CLAUDE_CONTEXT.md              # This file - AI reference
│
├── 📁 public/                        # 🌐 Web-accessible entry point
│   ├── 📄 index.php                  # Main entry point and router
│   ├── 📄 .htaccess                  # Apache configuration
│   ├── 📄 robots.txt                 # SEO configuration
│   └── 📄 favicon.ico                # Site favicon
│
├── 📁 app/                           # 🏗️ Core application logic
│   ├── 📁 config/                    # ⚙️ Configuration files
│   │   ├── 📄 app.php                # Application configuration
│   │   ├── 📄 database.php           # Database connection settings
│   │   └── 📄 routes.php             # URL routing definitions
│   │
│   ├── 📁 controllers/               # 🎮 Business logic controllers
│   │   ├── 📄 BaseController.php     # Common controller functionality
│   │   ├── 📄 AuthController.php     # Authentication handling
│   │   ├── 📄 DashboardController.php # Role-based dashboards
│   │   ├── 📄 BookingController.php  # Teacher booking system
│   │   ├── 📄 VehicleController.php  # Vehicle tracking logic
│   │   ├── 📄 FeedbackController.php # Feedback management
│   │   └── 📄 AdminController.php    # Administrative functions
│   │
│   ├── 📁 models/                    # 📊 Data access layer
│   │   ├── 📄 BaseModel.php          # Common database operations
│   │   ├── 📄 User.php               # User authentication and management
│   │   ├── 📄 Teacher.php            # Teacher profile operations
│   │   ├── 📄 Booking.php            # Booking CRUD operations
│   │   ├── 📄 Vehicle.php            # Vehicle tracking operations
│   │   └── 📄 Feedback.php           # Feedback system operations
│   │
│   ├── 📁 views/                     # 🖼️ Presentation templates
│   │   ├── 📁 layouts/               # 🧩 Reusable layout components
│   │   │   ├── 📄 header.php         # Common header template
│   │   │   ├── 📄 footer.php         # Common footer template
│   │   │   ├── 📄 nav.php            # Navigation component
│   │   │   ├── 📄 sidebar.php        # Dashboard sidebar
│   │   │   └── 📄 master.php         # Master layout template
│   │   │
│   │   ├── 📁 auth/                  # 🔐 Authentication views
│   │   │   ├── 📄 login.php          # Login form
│   │   │   ├── 📄 register.php       # Registration form
│   │   │   ├── 📄 forgot-password.php # Password reset
│   │   │   └── 📄 reset-password.php # Password reset form
│   │   │
│   │   ├── 📁 dashboard/             # 📈 Role-specific dashboards
│   │   │   ├── 📄 student.php        # Student dashboard
│   │   │   ├── 📄 teacher.php        # Teacher dashboard
│   │   │   └── 📄 staff.php          # Staff dashboard
│   │   │
│   │   ├── 📁 booking/               # 📅 Teacher booking views
│   │   │   ├── 📄 index.php          # Teacher list and selection
│   │   │   ├── 📄 teacher-profile.php # Individual teacher profile
│   │   │   ├── 📄 calendar.php       # Booking calendar interface
│   │   │   ├── 📄 confirmation.php   # Booking confirmation
│   │   │   ├── 📄 history.php        # Booking history
│   │   │   └── 📄 manage.php         # Teacher booking management
│   │   │
│   │   ├── 📁 tracking/              # 🚌 Vehicle tracking views
│   │   │   ├── 📄 index.php          # Main tracking interface
│   │   │   ├── 📄 map.php            # Map display component
│   │   │   ├── 📄 vehicles.php       # Vehicle list component
│   │   │   └── 📄 route-details.php  # Detailed route information
│   │   │
│   │   ├── 📁 feedback/              # 💬 Feedback system views
│   │   │   ├── 📄 index.php          # Feedback list and submission
│   │   │   ├── 📄 submit.php         # Feedback submission form
│   │   │   ├── 📄 view.php           # Individual feedback view
│   │   │   └── 📄 admin.php          # Admin feedback management
│   │   │
│   │   ├── 📁 errors/                # ❌ Error pages
│   │   │   ├── 📄 404.php            # Page not found
│   │   │   ├── 📄 403.php            # Access forbidden
│   │   │   └── 📄 500.php            # Server error
│   │   │
│   │   └── 📄 homepage.php           # 🏠 Homepage template
│   │
│   ├── 📁 middleware/                # 🛡️ Request filtering
│   │   ├── 📄 AuthMiddleware.php     # Authentication verification
│   │   ├── 📄 RoleMiddleware.php     # Role-based access control
│   │   ├── 📄 SecurityMiddleware.php # Security headers and validation
│   │   └── 📄 CSRFMiddleware.php     # CSRF token validation
│   │
│   └── 📁 helpers/                   # 🔧 Utility functions
│       ├── 📄 functions.php          # Common utility functions
│       ├── 📄 validation.php         # Input validation helpers
│       ├── 📄 security.php           # Security utility functions
│       ├── 📄 constants.php          # Application constants
│       └── 📄 email.php              # Email sending utilities
│
├── 📁 assets/                        # 🎨 Static resources
│   ├── 📁 css/                       # 🎨 Stylesheets
│   │   ├── 📄 app.css                # Main application styles
│   │   ├── 📄 components.css         # Reusable component styles
│   │   ├── 📄 responsive.css         # Mobile responsive styles
│   │   ├── 📄 dashboard.css          # Dashboard-specific styles
│   │   ├── 📄 forms.css              # Form styling
│   │   └── 📄 utilities.css          # Utility classes
│   │
│   ├── 📁 js/                        # ⚡ JavaScript files
│   │   ├── 📄 app.js                 # Main application JavaScript
│   │   ├── 📄 booking.js             # Booking system interactions
│   │   ├── 📄 tracking.js            # Vehicle tracking functions
│   │   ├── 📄 feedback.js            # Feedback form handling
│   │   ├── 📄 validation.js          # Client-side validation
│   │   └── 📄 utils.js               # JavaScript utilities
│   │
│   ├── 📁 images/                    # 🖼️ Image assets
│   │   ├── 📁 logos/                 # 🏛️ Application logos
│   │   ├── 📁 icons/                 # 🔣 UI icons and symbols
│   │   ├── 📁 avatars/               # 👤 Default user avatars
│   │   └── 📁 uploads/               # 📤 User uploaded images
│   │
│   └── 📁 ui-references/             # 📐 UI design references
│       ├── 📄 homepage-design.png    # Homepage UI reference
│       ├── 📄 login-design.png       # Login page UI reference
│       ├── 📄 dashboard-design.png   # Dashboard UI reference
│       ├── 📄 booking-design.png     # Booking page UI reference
│       ├── 📄 tracking-design.png    # Tracking page UI reference
│       └── 📄 feedback-design.png    # Feedback page UI reference
│
├── 📁 database/                      # 🗃️ Database management
│   ├── 📄 schema.sql                 # Complete database structure
│   ├── 📄 seeds.sql                  # Sample data for testing
│   ├── 📄 test-connection.php        # Database connectivity testing
│   ├── 📁 migrations/                # 📈 Database version management
│   │   ├── 📄 001_create_users_table.sql
│   │   ├── 📄 002_create_teachers_table.sql
│   │   ├── 📄 003_create_bookings_table.sql
│   │   ├── 📄 004_create_vehicles_table.sql
│   │   └── 📄 005_create_feedback_table.sql
│   └── 📁 backups/                   # 💾 Database backups
│
├── 📁 storage/                       # 📦 Application storage
│   ├── 📁 logs/                      # 📝 Application logs
│   │   ├── 📄 app.log                # General application logs
│   │   ├── 📄 error.log              # Error logs
│   │   ├── 📄 security.log           # Security event logs
│   │   └── 📄 query.log              # Database query logs
│   │
│   ├── 📁 cache/                     # ⚡ Temporary cache files
│   ├── 📁 sessions/                  # 🔐 Session storage
│   ├── 📁 uploads/                   # 📤 User file uploads
│   └── 📁 temp/                      # 🗂️ Temporary files
│
├── 📁 docs/                          # 📚 Project documentation
│   ├── 📄 INSTALLATION.md            # Setup instructions
│   ├── 📄 API.md                     # API documentation
│   ├── 📄 DEPLOYMENT.md              # Deployment guide
│   ├── 📄 SECURITY.md                # Security guidelines
│   ├── 📄 TROUBLESHOOTING.md         # Common issues and solutions
│   └── 📄 CHANGELOG.md               # Version history
│
└── 📁 tests/                         # 🧪 Testing framework
    ├── 📁 unit/                      # Unit tests
    ├── 📁 integration/               # Integration tests
    ├── 📄 TestCase.php               # Base test class
    └── 📄 bootstrap.php              # Test bootstrap
```

---

## 🔄 DEVELOPMENT WORKFLOW

### **Code Generation Standards**
```php
<?php
/**
 * File: app/controllers/ExampleController.php
 * Purpose: [Brief description of the controller's responsibility]
 * Author: AI Generated for KIIT SEVA
 * Created: [Date]
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/models/BaseModel.php';
require_once dirname(__DIR__) . '/helpers/validation.php';

class ExampleController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new ExampleModel();
    }

    /**
     * Handle GET request to display resource
     */
    public function index(): void {
        try {
            $this->requireAuthentication();
            $this->checkRole(['student', 'teacher']);

            $data = $this->model->getAllWithPagination(
                $this->getPage(),
                $this->getPerPage()
            );

            $this->render('example/index', [
                'data' => $data,
                'title' => 'Example Page'
            ]);

        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Handle POST request to create resource
     */
    public function store(): void {
        try {
            $this->requireAuthentication();
            $this->validateCSRFToken();

            $input = $this->validateInput([
                'name' => 'required|string|max:100',
                'email' => 'required|email',
                'description' => 'optional|string|max:500'
            ]);

            $id = $this->model->create($input);

            $this->flashMessage('success', 'Record created successfully');
            $this->redirect('/example/' . $id);

        } catch (ValidationException $e) {
            $this->flashMessage('error', $e->getMessage());
            $this->redirectBack();
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
}
```

### **Naming Conventions**

#### **PHP Classes & Methods**
```php
// Controllers: [Resource]Controller
class BookingController extends BaseController { }
class UserController extends BaseController { }

// Models: Singular noun
class User extends BaseModel { }
class Booking extends BaseModel { }

// Methods: camelCase verbs
public function getUserById(int $id): ?array { }
public function createBooking(array $data): int { }
public function updateVehicleLocation(int $id, float $lat, float $lng): bool { }
```

#### **Database Conventions**
```sql
-- Tables: lowercase plural
users, teachers, bookings, vehicles, feedback

-- Columns: lowercase snake_case
user_id, created_at, is_active, booking_date

-- Foreign Keys: [table]_id
user_id, teacher_id, student_id

-- Indexes: idx_[purpose]
idx_email, idx_user_role, idx_booking_date
```

#### **File & Directory Names**
```
// PHP Files: PascalCase for classes, lowercase for views
UserController.php
BookingModel.php
user-profile.php
booking-history.php

// CSS/JS Files: lowercase with hyphens
app.css, booking-system.js, form-validation.css

// View Templates: lowercase with hyphens
login.php, teacher-profile.php, vehicle-tracking.php
```

### **Error Handling Patterns**
```php
class ErrorHandler {
    public static function handleException(Throwable $e): void {
        // Log the error
        error_log("Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

        // Development vs Production response
        if (Config::get('app.debug')) {
            self::showDebugError($e);
        } else {
            self::showGenericError();
        }
    }

    private static function showDebugError(Throwable $e): void {
        header('Content-Type: text/html');
        echo "<h1>Application Error</h1>";
        echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }

    private static function showGenericError(): void {
        header('HTTP/1.1 500 Internal Server Error');
        require_once '../app/views/errors/500.php';
    }
}
```

---

## 🎯 QUALITY STANDARDS

### **Code Quality Requirements**

#### **PSR Standards Compliance**
- **PSR-1:** Basic Coding Standard
- **PSR-4:** Autoloading Standard
- **PSR-12:** Extended Coding Style Guide

```php
<?php
declare(strict_types=1);

namespace KiitSeva\Controllers;

use KiitSeva\Models\User;
use KiitSeva\Helpers\Validator;

class AuthController
{
    private User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function login(): void
    {
        // Implementation
    }
}
```

#### **Performance Benchmarks**
- **Page Load Time:** < 3 seconds on 3G connection
- **Database Query Time:** < 100ms for standard operations
- **Memory Usage:** < 64MB per request
- **Concurrent Users:** Support 100+ simultaneous users
- **Mobile Performance:** < 2 seconds load time on mobile

#### **Browser Compatibility**
- **Chrome:** 90+
- **Firefox:** 88+
- **Safari:** 14+
- **Edge:** 90+
- **Mobile Safari:** iOS 14+
- **Chrome Mobile:** Android 10+

### **Accessibility Standards (WCAG 2.1 AA)**
```html
<!-- Semantic HTML -->
<main role="main">
  <section aria-labelledby="booking-heading">
    <h2 id="booking-heading">Teacher Booking</h2>

    <!-- Form with proper labels -->
    <form aria-describedby="booking-help">
      <label for="teacher-select">Select Teacher</label>
      <select id="teacher-select" aria-required="true">
        <option value="">Choose a teacher</option>
      </select>

      <div id="booking-help" class="help-text">
        Select your preferred teacher and time slot
      </div>
    </form>
  </section>
</main>

<!-- Focus management -->
<button type="button" aria-expanded="false" aria-controls="mobile-menu">
  Menu
</button>
```

### **Mobile Responsiveness**
```css
/* Mobile-first approach */
.container {
  width: 100%;
  padding: 1rem;
}

/* Touch-friendly interface */
.btn-mobile {
  min-height: 44px; /* iOS touch target */
  min-width: 44px;
}

.form-control-mobile {
  padding: 16px; /* Prevents iOS zoom */
  font-size: 16px;
}

/* Responsive navigation */
@media (max-width: 768px) {
  .nav-desktop { display: none; }
  .nav-mobile { display: block; }
}
```

---

## 🚀 DEPLOYMENT SPECIFICATIONS

### **Production Server Requirements**
- **Operating System:** Ubuntu 20.04 LTS or CentOS 8+
- **Web Server:** Apache 2.4.41+ or Nginx 1.18+
- **PHP Version:** 7.4+ with required extensions
- **Database:** MySQL 8.0+ or MariaDB 10.5+
- **SSL Certificate:** Required (Let's Encrypt recommended)
- **Memory:** 2GB RAM minimum, 4GB recommended
- **Storage:** 50GB SSD minimum

### **Required PHP Extensions**
```bash
# Core extensions
php-pdo
php-pdo-mysql
php-mbstring
php-openssl
php-json
php-session
php-filter
php-curl
php-gd
php-zip
php-xml
```

### **Environment Configuration**
```bash
# .env.production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seva.kiit.ac.in

DB_HOST=localhost
DB_NAME=kiit_seva_prod
DB_USER=seva_user
DB_PASS=secure_password_here

MAIL_DRIVER=smtp
MAIL_HOST=smtp.kiit.ac.in
MAIL_PORT=587
MAIL_USERNAME=noreply@kiit.ac.in
MAIL_PASSWORD=mail_password_here

LOG_LEVEL=warning
SESSION_LIFETIME=1800
CSRF_TOKEN_EXPIRE=3600
```

### **Security Hardening Checklist**
```apache
# .htaccess security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'"
</IfModule>

# Disable server signature
ServerTokens Prod
ServerSignature Off

# Hide PHP version
expose_php = Off
```

---

## 💡 AI CODE GENERATION GUIDELINES

### **Prompting Patterns for Claude**

#### **Standard File Generation Prompt**
```
Generate [FILE_TYPE] for KIIT SEVA project:

File: [EXACT_FILE_PATH]
Purpose: [SPECIFIC_FUNCTIONALITY]
Dependencies: [REQUIRED_INCLUDES]
Security: [SECURITY_REQUIREMENTS]
UI Requirements: [DESIGN_SPECIFICATIONS]

Requirements:
- Follow MVC architecture pattern
- Use PDO prepared statements exclusively
- Implement proper input validation
- Include CSRF protection
- Ensure mobile responsiveness
- Add comprehensive error handling
- Use blue theme (#4a90e2) consistently
- Include proper documentation
```

#### **Feature Development Prompt**
```
Implement [FEATURE_NAME] for KIIT SEVA:

Context: [REFERENCE_CLAUDE_CONTEXT.md]
User Story: As a [USER_TYPE], I want to [ACTION] so that [BENEFIT]
Acceptance Criteria:
- [CRITERIA_1]
- [CRITERIA_2]
- [CRITERIA_3]

Technical Requirements:
- Database operations using PDO
- Input validation and sanitization
- Role-based access control
- Mobile-first responsive design
- Error handling and user feedback
```

### **Code Generation Preferences**
1. **Security First:** Always include validation, sanitization, CSRF protection
2. **Mobile First:** Responsive design from the start
3. **Error Handling:** Comprehensive try-catch blocks with user-friendly messages
4. **Documentation:** Clear comments explaining complex logic
5. **Consistency:** Follow established patterns and naming conventions
6. **Performance:** Efficient database queries with proper indexing
7. **Accessibility:** ARIA labels and semantic HTML

### **Testing Requirements**
```php
// Always include basic testing methods
class ExampleControllerTest extends TestCase {
    public function testUserCanAccessIndex(): void {
        $response = $this->get('/example');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUserCannotAccessWithoutAuth(): void {
        $response = $this->get('/example/create');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testValidationWorksCorrectly(): void {
        $response = $this->post('/example', []);
        $this->assertContains('validation error', $response->getContent());
    }
}
```

---

## 🎓 PROJECT-SPECIFIC CONVENTIONS

### **KIIT University Branding**
- **Primary Color:** #4a90e2 (KIIT Blue)
- **Logo Usage:** Always include KIIT logo with proper sizing
- **Typography:** Professional, clean fonts (Segoe UI family)
- **Tone:** Professional, helpful, student-focused

### **Campus-Specific Terminology**
- **Routes:** "Campus-15", "Campus-17", "Campus-25" (with hyphen)
- **Users:** "Students", "Faculty" (not teachers in branding), "Staff"
- **Locations:** "Chamber" (teacher office), "Academic Block", "Hostel Complex"
- **Time:** 24-hour format (09:00-10:00, 14:00-15:00)

### **Email Standards**
- **Domain:** @kiit.ac.in for all university emails
- **Format:** firstname.lastname@kiit.ac.in
- **System Email:** noreply@kiit.ac.in
- **Support Email:** support@kiit.ac.in

### **Status Workflow Definitions**

#### **Booking Status Flow**
```
booked → confirmed → completed
   ↓         ↓
cancelled  cancelled
```

#### **Feedback Status Flow**
```
pending → approved → resolved
   ↓
rejected (with reason)
```

#### **Vehicle Status Options**
- **ON_DUTY:** Active and tracking
- **OFF_DUTY:** Parked/not in service
- **BREAK:** Temporary stop
- **MAINTENANCE:** Under repair

### **Time Slot Standards**
- **Format:** "HH:MM-HH:MM" (e.g., "09:00-10:00")
- **Duration:** 1-hour slots only
- **Working Hours:** 09:00-17:00 (Monday-Friday)
- **Booking Window:** 7 days advance maximum
- **Cancellation:** Up to 2 hours before slot

---

## ✅ QUALITY CHECKLIST FOR AI GENERATED CODE

### **Before Submitting Code**
- [ ] **Security:** PDO prepared statements, input validation, CSRF protection
- [ ] **Mobile Responsive:** Works on all screen sizes
- [ ] **Accessibility:** ARIA labels, semantic HTML, keyboard navigation
- [ ] **Error Handling:** Try-catch blocks, user-friendly messages
- [ ] **Code Style:** PSR standards, proper indentation, meaningful names
- [ ] **Database:** Proper foreign keys, indexes, constraints
- [ ] **UI Consistency:** Blue theme (#4a90e2), consistent components
- [ ] **Documentation:** Clear comments for complex logic
- [ ] **Testing:** Basic test cases included
- [ ] **Performance:** Optimized queries, minimal dependencies

### **File Creation Verification**
- [ ] Proper file path according to MVC structure
- [ ] Required includes and dependencies
- [ ] Namespace declarations (if using)
- [ ] Class and method documentation
- [ ] Input validation for all user inputs
- [ ] Output encoding for all displayed data
- [ ] Role-based access control where needed
- [ ] Mobile-first responsive design
- [ ] Proper error handling and logging

---

This comprehensive context file serves as the definitive reference for all KIIT SEVA development. Every code generation should reference this document to ensure consistency, security, and quality across the entire project.

**🎯 Remember:** This is a professional university platform - prioritize security, accessibility, and user experience in every component.