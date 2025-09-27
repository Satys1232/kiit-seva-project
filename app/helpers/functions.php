<?php
/**
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
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
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
    } catch (Exception $e) {
        error_log("Error getting current user: " . $e->getMessage());
        return null;
    }
}

/**
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
    }
}

/**
 * Format date for display
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
    }
}

/**
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
    }
    
    return $slots;
}

/**
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
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
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
    exit;
}

/**
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