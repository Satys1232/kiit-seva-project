<?php
/**
<<<<<<< HEAD
 * KIIT SEVA - Helper Functions
 * Common utility functions for the application
 */

/**
 * Sanitize input data
 */
function sanitizeInput($data, $type = 'string') {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value, $type);
        }
        return $data;
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'url':
            return filter_var($data, FILTER_SANITIZE_URL);
        default:
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
=======
 * KIIT SEVA - Common Helper Functions
 * Utility functions used throughout the application
 */

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn(): bool
{
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
<<<<<<< HEAD
 * Get current logged-in user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $db = getKiitDatabase();
        $stmt = $db->query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        return $stmt ? $stmt->fetch() : null;
=======
 * Get current logged-in user information
 * @return array|null
 */
function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    // Include database config
    require_once dirname(__DIR__) . '/config/database.php';
    
    try {
        $db = new KiitSevaDatabase();
        $pdo = $db->connect();
        
        $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
    } catch (Exception $e) {
        error_log("Error getting current user: " . $e->getMessage());
        return null;
    }
}

/**
<<<<<<< HEAD
 * Require specific role(s)
 */
function requireRole($roles) {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
    
    $user = getCurrentUser();
    if (!$user) {
        header('Location: /login');
        exit;
    }
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    if (!in_array($user['role'], $roles)) {
        http_response_code(403);
        die('Access denied');
=======
 * Require user to be logged in
 * @param string $redirectUrl
 */
function requireLogin(string $redirectUrl = '/app/views/auth/login.php'): void
{
    if (!isLoggedIn()) {
        header("Location: $redirectUrl");
        exit;
    }
}

/**
 * Require specific user role
 * @param array|string $roles
 */
function requireRole($roles): void
{
    requireLogin();
    
    $user = getCurrentUser();
    if (!$user) {
        header('Location: /app/views/auth/login.php');
        exit;
    }

    $allowedRoles = is_array($roles) ? $roles : [$roles];
    
    if (!in_array($user['role'], $allowedRoles)) {
        header('Location: /app/views/errors/403.php');
        exit;
    }
}

/**
 * Sanitize input data
 * @param mixed $data
 * @param string $type
 * @return mixed
 */
function sanitizeInput($data, string $type = 'string')
{
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value, $type);
        }
        return $data;
    }

    switch ($type) {
        case 'email':
            return filter_var(trim($data), FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'url':
            return filter_var(trim($data), FILTER_SANITIZE_URL);
        case 'string':
        default:
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
    }
}

/**
 * Format date for display
<<<<<<< HEAD
 */
function formatDate($date, $format = 'readable') {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = is_string($date) ? strtotime($date) : $date;
    
    switch ($format) {
        case 'readable':
            return date('M j, Y g:i A', $timestamp);
        case 'short':
            return date('M j, Y', $timestamp);
        case 'time':
            return date('g:i A', $timestamp);
        case 'mysql':
            return date('Y-m-d H:i:s', $timestamp);
        case 'date-only':
            return date('Y-m-d', $timestamp);
        default:
            return date($format, $timestamp);
=======
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate(string $date, string $format = 'readable'): string
{
    $dateTime = new DateTime($date);
    
    switch ($format) {
        case 'readable':
            return $dateTime->format('M j, Y g:i A');
        case 'short':
            return $dateTime->format('M j, Y');
        case 'time':
            return $dateTime->format('g:i A');
        case 'mysql':
            return $dateTime->format('Y-m-d H:i:s');
        default:
            return $dateTime->format($format);
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
    }
}

/**
<<<<<<< HEAD
 * Generate time slots
 */
function generateSlots($startTime, $endTime, $duration = 60) {
    $slots = [];
    $start = strtotime($startTime);
    $end = strtotime($endTime);
    
    while ($start < $end) {
        $slotEnd = $start + ($duration * 60);
        if ($slotEnd <= $end) {
            $slots[] = date('H:i', $start) . '-' . date('H:i', $slotEnd);
        }
        $start = $slotEnd;
=======
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 * @param string $type
 * @param string $message
 */
function setFlashMessage(string $type, string $message): void
{
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][$type][] = $message;
}

/**
 * Get and clear flash messages
 * @param string|null $type
 * @return array
 */
function getFlashMessages(?string $type = null): array
{
    if (!isset($_SESSION['flash_messages'])) {
        return [];
    }

    if ($type) {
        $messages = $_SESSION['flash_messages'][$type] ?? [];
        unset($_SESSION['flash_messages'][$type]);
        return $messages;
    }

    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Generate available time slots
 * @param string $startTime
 * @param string $endTime
 * @param int $duration
 * @return array
 */
function generateTimeSlots(string $startTime, string $endTime, int $duration = 60): array
{
    $slots = [];
    $start = new DateTime($startTime);
    $end = new DateTime($endTime);
    
    while ($start < $end) {
        $slotEnd = clone $start;
        $slotEnd->add(new DateInterval("PT{$duration}M"));
        
        if ($slotEnd <= $end) {
            $slots[] = $start->format('H:i') . '-' . $slotEnd->format('H:i');
        }
        
        $start->add(new DateInterval("PT{$duration}M"));
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
    }
    
    return $slots;
}

/**
<<<<<<< HEAD
 * Send email (placeholder for future implementation)
 */
function sendEmail($to, $subject, $body, $template = null) {
    // For now, just log the email
    error_log("Email to {$to}: {$subject}");
    return true;
}

/**
 * Log user activity
 */
function logActivity($user_id, $action, $details = '') {
    try {
        $db = getKiitDatabase();
        $stmt = $db->query(
            "INSERT INTO activity_log (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())",
            [$user_id, $action, $details]
        );
        return true;
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate CSRF token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Validate CSRF token
 */
function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Create notification
 */
function createNotification($user_id, $message, $type = 'info') {
    try {
        $db = getKiitDatabase();
        $stmt = $db->query(
            "INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, NOW())",
            [$user_id, $message, $type]
        );
        return true;
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate email format
 */
function validateEmail($email) {
=======
 * Validate email format
 * @param string $email
 * @return bool
 */
function validateEmail(string $email): bool
{
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
<<<<<<< HEAD
 * Validate password strength
 */
function validatePassword($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Za-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Redirect with message
 */
function redirect($url, $message = '', $type = 'info') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
=======
 * Validate KIIT email domain
 * @param string $email
 * @return bool
 */
function validateKiitEmail(string $email): bool
{
    $validDomains = ['kiit.ac.in', 'ksom.ac.in', 'kiss.ac.in', 'kims.ac.in'];
    $domain = substr(strrchr($email, "@"), 1);
    return in_array($domain, $validDomains);
}

/**
 * Validate phone number (Indian format)
 * @param string $phone
 * @return bool
 */
function validatePhoneNumber(string $phone): bool
{
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

/**
 * Log activity
 * @param int $userId
 * @param string $action
 * @param array $details
 */
function logActivity(int $userId, string $action, array $details = []): void
{
    $logFile = dirname(__DIR__, 2) . '/storage/logs/activity.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $userId,
        'action' => $action,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
}

/**
 * Get base URL
 * @return string
 */
function getBaseUrl(): string
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return "$protocol://$host$path";
}

/**
 * Redirect to URL
 * @param string $url
 * @param int $statusCode
 */
function redirect(string $url, int $statusCode = 302): void
{
    header("Location: $url", true, $statusCode);
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
    exit;
}

/**
<<<<<<< HEAD
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Format time slot for display
 */
function formatTimeSlot($slot) {
    if (strpos($slot, '-') !== false) {
        list($start, $end) = explode('-', $slot);
        return date('g:i A', strtotime($start)) . ' - ' . date('g:i A', strtotime($end));
    }
    return $slot;
}

/**
 * Get booking status badge
 */
function getStatusBadge($status) {
    $badges = [
        'booked' => '<span class="badge badge-primary">Booked</span>',
        'confirmed' => '<span class="badge badge-success">Confirmed</span>',
        'completed' => '<span class="badge badge-info">Completed</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Calculate distance between two GPS coordinates
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // km
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

/**
 * Get vehicle status indicator
 */
function getVehicleStatus($isActive, $lastUpdated) {
    if (!$isActive) {
        return '<span class="status-indicator offline">🔴 OFF-DUTY</span>';
    }
    
    $timeDiff = time() - strtotime($lastUpdated);
    if ($timeDiff < 300) { // 5 minutes
        return '<span class="status-indicator online">🟢 LIVE</span>';
    } elseif ($timeDiff < 900) { // 15 minutes
        return '<span class="status-indicator warning">🟡 DELAYED</span>';
    } else {
        return '<span class="status-indicator offline">🔴 OFFLINE</span>';
    }
}

/**
 * Generate star rating HTML
 */
function generateStarRating($rating, $maxRating = 5) {
    $html = '<div class="star-rating">';
    for ($i = 1; $i <= $maxRating; $i++) {
        if ($i <= $rating) {
            $html .= '<span class="star filled">★</span>';
        } else {
            $html .= '<span class="star">☆</span>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get time ago format
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}
?>
=======
 * Check if request is AJAX
 * @return bool
 */
function isAjaxRequest(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Send JSON response
 * @param array $data
 * @param int $statusCode
 */
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
