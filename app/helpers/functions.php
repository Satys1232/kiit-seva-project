<?php
/**
 * KIIT SEVA - Common Helper Functions
 * Utility functions used throughout the application
 */

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
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
    } catch (Exception $e) {
        error_log("Error getting current user: " . $e->getMessage());
        return null;
    }
}

/**
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
    }
}

/**
 * Format date for display
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
    }
}

/**
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
    }
    
    return $slots;
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
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
    exit;
}

/**
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