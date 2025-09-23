<?php
/**
 * KIIT SEVA - Enterprise BaseController Class
 * Professional-grade foundation for all web request handling with enterprise-level security,
 * professional view management, and robust session control.
 *
 * @package     KIIT-SEVA
 * @subpackage  Controllers
 * @author      AI Generated for KIIT SEVA
 * @version     1.0.0
 * @license     Proprietary - KIIT University
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BaseModel;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * BaseController Class
 * 
 * Serves as the foundation for all web request handling with enterprise-level security,
 * professional view management, and robust session control.
 */
class BaseController
{
    /** @var array Request data container */
    protected array $request = [];
    
    /** @var array Response data container */
    protected array $response = [];
    
    /** @var array View data container */
    protected array $viewData = [];
    
    /** @var string Current view template */
    protected string $view = '';
    
    /** @var string Layout template */
    protected string $layout = 'default';
    
    /** @var bool Debug mode flag */
    protected bool $debugMode = false;
    
    /** @var string Log file path */
    protected string $logFile = '';
    
    /** @var string Current environment */
    protected string $environment = 'development';
    
    /** @var array Session data */
    protected array $session = [];
    
    /** @var array Flash messages */
    protected array $flashMessages = [];
    
    /** @var array CSRF tokens */
    protected array $csrfTokens = [];
    
    /** @var array Validation errors */
    protected array $validationErrors = [];
    
    /** @var array Request headers */
    protected array $headers = [];
    
    /** @var string HTTP method */
    protected string $method = 'GET';
    
    /** @var string Request URI */
    protected string $uri = '';
    
    /** @var array Route parameters */
    protected array $routeParams = [];
    
    /** @var array Query parameters */
    protected array $queryParams = [];
    
    /** @var array Middleware stack */
    protected array $middleware = [];
    
    /** @var array Allowed HTTP methods */
    protected array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
    
    /** @var array Content type mappings */
    protected array $contentTypes = [
        'html' => 'text/html',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'text' => 'text/plain',
        'csv' => 'text/csv',
        'pdf' => 'application/pdf'
    ];
    
    /** @var string Current content type */
    protected string $contentType = 'html';
    
    /** @var int HTTP status code */
    protected int $statusCode = 200;
    
    /** @var array HTTP status messages */
    protected array $statusMessages = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error'
    ];
    
    /** @var array KIIT-specific validation rules */
    protected array $kiitValidationRules = [
        'email' => '/^[a-zA-Z0-9._%+-]+@(kiit\.ac\.in|kiit\.edu)$/',
        'studentId' => '/^[0-9]{10}$/',
        'employeeId' => '/^(KIIT|KP)[0-9]{6}$/',
        'campusCode' => '/^(KP|KC|KS)[0-9]{2}$/',
        'mobileNumber' => '/^[6-9][0-9]{9}$/' // Indian mobile number format
    ];
    
    /**
     * Constructor
     * 
     * Initializes the controller with request data and configuration
     */
    public function __construct()
    {
        // Initialize controller
        $this->initializeController();
        
        // Process request data
        $this->processRequest();
        
        // Start or resume session
        $this->startSession();
        
        // Load flash messages from session
        $this->loadFlashMessages();
        
        // Set default headers
        $this->setDefaultHeaders();
    }
    
    /**
     * Initialize controller settings and configuration
     * 
     * @return void
     */
    protected function initializeController(): void
    {
        try {
            // Set up logging
            $this->logFile = dirname(__DIR__, 2) . '/storage/logs/application.log';
            
            // Detect environment
            $this->detectEnvironment();
            
            // Set debug mode based on environment
            $this->debugMode = $this->environment === 'development';
            
            // Set default timezone to Asia/Kolkata for KIIT
            date_default_timezone_set('Asia/Kolkata');
            
        } catch (Exception $e) {
            $this->logError('Controller initialization failed: ' . $e->getMessage());
            
            if ($this->debugMode) {
                throw $e;
            }
        }
    }
    
    /**
     * Detect current environment
     * 
     * @return void
     */
    protected function detectEnvironment(): void
    {
        // Check environment variable first
        if (isset($_ENV['APP_ENV']) && !empty($_ENV['APP_ENV'])) {
            $this->environment = strtolower(trim($_ENV['APP_ENV']));
            return;
        }
        
        // Auto-detect based on server characteristics
        $serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        if (in_array($serverName, ['localhost', '127.0.0.1', '::1']) ||
            str_contains($serverName, 'dev') ||
            str_contains($serverName, 'local')) {
            $this->environment = 'development';
        } elseif (str_contains($serverName, 'staging') ||
                  str_contains($serverName, 'test')) {
            $this->environment = 'staging';
        } elseif (str_contains($serverName, 'kiit.ac.in') ||
                  str_contains($serverName, 'seva.kiit')) {
            $this->environment = 'production';
        }
    }
    
    /**
     * Process incoming request data
     * 
     * @return void
     */
    protected function processRequest(): void
    {
        // Get HTTP method
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Get request URI
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Get query parameters
        $this->queryParams = $_GET ?? [];
        
        // Get request headers
        $this->headers = $this->getRequestHeaders();
        
        // Process request body based on content type
        $this->processRequestBody();
        
        // Sanitize all input data
        $this->sanitizeInput();
    }
    
    /**
     * Get all request headers
     * 
     * @return array
     */
    protected function getRequestHeaders(): array
    {
        $headers = [];
        
        // Use getallheaders() if available
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Manual extraction from $_SERVER
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $headerKey = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $headers[$headerKey] = $value;
                }
            }
        }
        
        return $headers;
    }
    
    /**
     * Process request body based on content type
     * 
     * @return void
     */
    protected function processRequestBody(): void
    {
        // Get content type
        $contentType = $this->headers['Content-Type'] ?? '';
        
        // Process based on method and content type
        if (in_array($this->method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (strpos($contentType, 'application/json') !== false) {
                // JSON request
                $input = file_get_contents('php://input');
                $this->request = json_decode($input, true) ?? [];
            } elseif (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
                // Form data
                $this->request = $_POST;
            } elseif (strpos($contentType, 'multipart/form-data') !== false) {
                // Multipart form data with files
                $this->request = $_POST;
                $this->processUploadedFiles();
            } else {
                // Default fallback
                $this->request = $_POST;
            }
        }
        
        // Merge with query parameters for GET requests
        if ($this->method === 'GET') {
            $this->request = $this->queryParams;
        }
    }
    
    /**
     * Process uploaded files
     * 
     * @return void
     */
    protected function processUploadedFiles(): void
    {
        if (!empty($_FILES)) {
            $this->request['files'] = [];
            
            foreach ($_FILES as $key => $file) {
                if (is_array($file['name'])) {
                    // Multiple files
                    $fileCount = count($file['name']);
                    $this->request['files'][$key] = [];
                    
                    for ($i = 0; $i < $fileCount; $i++) {
                        $this->request['files'][$key][] = [
                            'name' => $file['name'][$i],
                            'type' => $file['type'][$i],
                            'tmp_name' => $file['tmp_name'][$i],
                            'error' => $file['error'][$i],
                            'size' => $file['size'][$i]
                        ];
                    }
                } else {
                    // Single file
                    $this->request['files'][$key] = $file;
                }
            }
        }
    }
    
    /**
     * Sanitize all input data
     * 
     * @return void
     */
    protected function sanitizeInput(): void
    {
        $this->request = $this->sanitizeData($this->request);
        $this->queryParams = $this->sanitizeData($this->queryParams);
    }
    
    /**
     * Recursively sanitize data
     * 
     * @param mixed $data Data to sanitize
     * @return mixed Sanitized data
     */
    protected function sanitizeData($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitizeData($value);
            }
            return $data;
        }
        
        if (is_string($data)) {
            // Remove null bytes and strip tags
            $data = str_replace(chr(0), '', $data);
            
            // Only strip tags if not in a special field that might contain HTML
            if (!isset($this->request['_preserve_html']) || !in_array(isset($key) ? $key : '', $this->request['_preserve_html'])) {
                $data = strip_tags($data);
            }
            
            // Trim whitespace
            $data = trim($data);
            
            return $data;
        }
        
        return $data;
    }
    
    /**
     * Start or resume session
     * 
     * @return void
     */
    protected function startSession(): void
    {
        // Configure secure session settings
        $cookieParams = session_get_cookie_params();
        $cookieSecure = $this->environment !== 'development';
        
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => $cookieSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Store session data
        $this->session = &$_SESSION;
        
        // Regenerate session ID periodically for security
        if (!isset($this->session['_last_regenerated']) || 
            (time() - $this->session['_last_regenerated']) > 1800) {
            session_regenerate_id(true);
            $this->session['_last_regenerated'] = time();
        }
        
        // Initialize CSRF protection
        $this->initializeCsrf();
    }
    
    /**
     * Initialize CSRF protection
     * 
     * @return void
     */
    protected function initializeCsrf(): void
    {
        // Initialize CSRF token storage
        if (!isset($this->session['_csrf_tokens'])) {
            $this->session['_csrf_tokens'] = [];
        }
        
        $this->csrfTokens = &$this->session['_csrf_tokens'];
        
        // Clean expired tokens
        $this->cleanExpiredCsrfTokens();
        
        // Generate a new token for this request
        $this->generateCsrfToken();
    }
    
    /**
     * Generate a new CSRF token
     * 
     * @param string $key Token key (default: 'default')
     * @return string The generated token
     */
    protected function generateCsrfToken(string $key = 'default'): string
    {
        // Generate a random token
        $token = bin2hex(random_bytes(32));
        
        // Store token with expiration time (1 hour)
        $this->csrfTokens[$key] = [
            'token' => $token,
            'expires' => time() + 3600
        ];
        
        return $token;
    }
    
    /**
     * Clean expired CSRF tokens
     * 
     * @return void
     */
    protected function cleanExpiredCsrfTokens(): void
    {
        $now = time();
        
        foreach ($this->csrfTokens as $key => $data) {
            if ($data['expires'] < $now) {
                unset($this->csrfTokens[$key]);
            }
        }
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token Token to verify
     * @param string $key Token key (default: 'default')
     * @return bool True if token is valid
     */
    protected function verifyCsrfToken(string $token, string $key = 'default'): bool
    {
        // Check if token exists and is valid
        if (isset($this->csrfTokens[$key]) && 
            $this->csrfTokens[$key]['token'] === $token && 
            $this->csrfTokens[$key]['expires'] > time()) {
            
            // Remove used token for one-time use
            unset($this->csrfTokens[$key]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Load flash messages from session
     * 
     * @return void
     */
    protected function loadFlashMessages(): void
    {
        if (isset($this->session['_flash'])) {
            $this->flashMessages = $this->session['_flash'];
            unset($this->session['_flash']);
        }
    }
    
    /**
     * Set flash message
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message content
     * @return void
     */
    protected function setFlash(string $type, string $message): void
    {
        if (!isset($this->session['_flash'])) {
            $this->session['_flash'] = [];
        }
        
        $this->session['_flash'][$type][] = $message;
    }
    
    /**
     * Set default response headers
     * 
     * @return void
     */
    protected function setDefaultHeaders(): void
    {
        // Security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        
        if ($this->environment === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' *.kiit.ac.in; style-src \'self\' \'unsafe-inline\' *.kiit.ac.in; img-src \'self\' data: *.kiit.ac.in; font-src \'self\' data: *.kiit.ac.in; connect-src \'self\' *.kiit.ac.in');
        }
        
        // Cache control based on environment
        if ($this->environment === 'development') {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
    }
    
    /**
     * Set view template
     * 
     * @param string $view View template name
     * @return self
     */
    public function setView(string $view): self
    {
        $this->view = $view;
        return $this;
    }
    
    /**
     * Set layout template
     * 
     * @param string $layout Layout template name
     * @return self
     */
    public function setLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * Set view data
     * 
     * @param string $key Data key
     * @param mixed $value Data value
     * @return self
     */
    public function setViewData(string $key, $value): self
    {
        $this->viewData[$key] = $value;
        return $this;
    }
    
    /**
     * Set multiple view data values
     * 
     * @param array $data Data array
     * @return self
     */
    public function setViewDataBulk(array $data): self
    {
        $this->viewData = array_merge($this->viewData, $data);
        return $this;
    }
    
    /**
     * Get view data
     * 
     * @param string $key Data key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function getViewData(string $key, $default = null)
    {
        return $this->viewData[$key] ?? $default;
    }
    
    /**
     * Render view template
     * 
     * @param string|null $view Optional view override
     * @param array $data Optional additional data
     * @return string Rendered view content
     */
    public function render(?string $view = null, array $data = []): string
    {
        // Use provided view or fallback to instance property
        $viewTemplate = $view ?? $this->view;
        
        if (empty($viewTemplate)) {
            throw new InvalidArgumentException('No view template specified');
        }
        
        // Merge view data
        $viewData = array_merge($this->viewData, $data);
        
        // Add flash messages to view data
        $viewData['flash'] = $this->flashMessages;
        
        // Add CSRF token to view data
        $viewData['csrf_token'] = $this->csrfTokens['default']['token'] ?? $this->generateCsrfToken();
        
        // Add debug information in development mode
        if ($this->debugMode) {
            $viewData['debug'] = [
                'environment' => $this->environment,
                'request_method' => $this->method,
                'request_uri' => $this->uri,
                'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
            ];
        }
        
        // Start output buffering
        ob_start();
        
        // Extract view data to make variables available in the view
        extract($viewData);
        
        // Include view template
        $viewPath = dirname(__DIR__) . '/views/' . $viewTemplate . '.php';
        
        if (!file_exists($viewPath)) {
            throw new RuntimeException("View template not found: {$viewTemplate}");
        }
        
        include $viewPath;
        
        // Get view content
        $content = ob_get_clean();
        
        // Render with layout if specified
        if (!empty($this->layout)) {
            // Pass view content to layout
            $viewData['content'] = $content;
            
            // Start output buffering again
            ob_start();
            
            // Extract view data for layout
            extract($viewData);
            
            // Include layout template
            $layoutPath = dirname(__DIR__) . '/views/layouts/' . $this->layout . '.php';
            
            if (!file_exists($layoutPath)) {
                throw new RuntimeException("Layout template not found: {$this->layout}");
            }
            
            include $layoutPath;
            
            // Get final content with layout
            $content = ob_get_clean();
        }
        
        return $content;
    }
    
    /**
     * Display rendered view
     * 
     * @param string|null $view Optional view override
     * @param array $data Optional additional data
     * @return void
     */
    public function display(?string $view = null, array $data = []): void
    {
        // Set content type header
        header('Content-Type: ' . ($this->contentTypes[$this->contentType] ?? 'text/html') . '; charset=UTF-8');
        
        // Set HTTP status code
        http_response_code($this->statusCode);
        
        // Render and output view
        echo $this->render($view, $data);
        exit;
    }
    
    /**
     * Respond with JSON
     * 
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     * @return void
     */
    public function json(array $data, int $statusCode = 200): void
    {
        $this->contentType = 'json';
        $this->statusCode = $statusCode;
        
        // Set content type header
        header('Content-Type: application/json; charset=UTF-8');
        
        // Set HTTP status code
        http_response_code($statusCode);
        
        // Add status information
        $response = [
            'status' => $statusCode,
            'message' => $this->statusMessages[$statusCode] ?? '',
            'data' => $data
        ];
        
        // Add validation errors if any
        if (!empty($this->validationErrors)) {
            $response['errors'] = $this->validationErrors;
        }
        
        // Add debug information in development mode
        if ($this->debugMode) {
            $response['debug'] = [
                'environment' => $this->environment,
                'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
            ];
        }
        
        // Output JSON response
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Redirect to another URL
     * 
     * @param string $url Target URL
     * @param int $statusCode HTTP status code (301 or 302)
     * @return void
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        // Validate status code
        if (!in_array($statusCode, [301, 302, 303, 307, 308])) {
            $statusCode = 302;
        }
        
        // Set HTTP status code
        http_response_code($statusCode);
        
        // Set location header
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Validate request data
     * 
     * @param array $rules Validation rules
     * @return bool True if validation passes
     */
    public function validate(array $rules): bool
    {
        $this->validationErrors = [];
        
        foreach ($rules as $field => $rule) {
            // Skip if field doesn't exist and not required
            if (!isset($this->request[$field]) && !str_contains($rule, 'required')) {
                continue;
            }
            
            // Get field value
            $value = $this->request[$field] ?? null;
            
            // Split rules
            $rulesList = explode('|', $rule);
            
            foreach ($rulesList as $ruleItem) {
                // Check if rule has parameters
                if (str_contains($ruleItem, ':')) {
                    [$ruleName, $ruleParam] = explode(':', $ruleItem, 2);
                } else {
                    $ruleName = $ruleItem;
                    $ruleParam = null;
                }
                
                // Validate based on rule
                if (!$this->validateRule($field, $value, $ruleName, $ruleParam)) {
                    break; // Stop validation for this field on first error
                }
            }
        }
        
        return empty($this->validationErrors);
    }
    
    /**
     * Validate a single rule
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $rule Rule name
     * @param string|null $param Rule parameter
     * @return bool True if validation passes
     */
    protected function validateRule(string $field, $value, string $rule, ?string $param): bool
    {
        $fieldLabel = ucwords(str_replace('_', ' ', $field));
        
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0' && $value !== 0) {
                    $this->validationErrors[$field] = "{$fieldLabel} is required";
                    return false;
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->validationErrors[$field] = "{$fieldLabel} must be a valid email address";
                    return false;
                }
                break;
                
            case 'kiit_email':
                if (!empty($value) && !preg_match($this->kiitValidationRules['email'], $value)) {
                    $this->validationErrors[$field] = "{$fieldLabel} must be a valid KIIT email address";
                    return false;
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->validationErrors[$field] = "{$fieldLabel} must be numeric";
                    return false;
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->validationErrors[$field] = "{$fieldLabel} must be an integer";
                    return false;
                }
                break;
                
            case 'min':
                if (!empty($value)) {
                    if (is_string($value) && mb_strlen($value) < (int)$param) {
                        $this->validationErrors[$field] = "{$fieldLabel} must be at least {$param} characters";
                        return false;
                    } elseif (is_numeric($value) && $value < (int)$param) {
                        $this->validationErrors[$field] = "{$fieldLabel} must be at least {$param}";
                        return false;
                    }
                }
                break;
                
            case 'max':
                if (!empty($value)) {
                    if (is_string($value) && mb_strlen($value) > (int)$param) {
                        $this->validationErrors[$field] = "{$fieldLabel} must not exceed {$param} characters";
                        return false;
                    } elseif (is_numeric($value) && $value > (int)$param) {
                        $this->validationErrors[$field] = "{$fieldLabel} must not exceed {$param}";
                        return false;
                    }
                }
                break;
                
            case 'in':
                if (!empty($value)) {
                    $allowedValues = explode(',', $param);
                    if (!in_array($value, $allowedValues)) {
                        $this->validationErrors[$field] = "{$fieldLabel} must be one of: " . implode(', ', $allowedValues);
                        return false;
                    }
                }
                break;
                
            case 'matches':
                if ($value !== ($this->request[$param] ?? null)) {
                    $matchLabel = ucwords(str_replace('_', ' ', $param));
                    $this->validationErrors[$field] = "{$fieldLabel} must match {$matchLabel}";
                    return false;
                }
                break;
                
            case 'date':
                if (!empty($value)) {
                    $date = date_parse($value);
                    if ($date['error_count'] > 0 || !checkdate($date['month'], $date['day'], $date['year'])) {
                        $this->validationErrors[$field] = "{$fieldLabel} must be a valid date";
                        return false;
                    }
                }
                break;
                
            case 'student_id':
                if (!empty($value) && !preg_match($this->kiitValidationRules['studentId'], $value)) {
                    $this->validationErrors[$field] = "{$fieldLabel} must be a valid KIIT student ID";
                    return false;
                }
                break;
                
            case 'employee_id':
                if (!empty($value) && !preg_match($this->kiitValidationRules['employeeId'], $value)) {
                    $this->validationErrors[$field] = "{$fieldLabel} must be a valid KIIT employee ID";
                    return false;
                }
                break;
                
            case 'campus_code':
                if (!empty($value) && !preg_match($this->kiitValidationRules['campusCode'], $value)) {
                    $this->validationErrors[$field] = "{$fieldLabel} must be a valid KIIT campus code";
                    return false;
                }
                break;
                
            case 'mobile':
                if (!empty($value) && !preg_match($this->kiitValidationRules['mobileNumber'], $value)) {
                    $this->validationErrors[$field] = "{$fieldLabel} must be a valid Indian mobile number";
                    return false;
                }
                break;
                
            case 'regex':
                if (!empty($value) && !preg_match($param, $value)) {
                    $this->validationErrors[$field] = "{$fieldLabel} has an invalid format";
                    return false;
                }
                break;
        }
        
        return true;
    }
    
    /**
     * Get input value from request
     * 
     * @param string $key Input key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function input(string $key, $default = null)
    {
        return $this->request[$key] ?? $default;
    }
    
    /**
     * Check if input key exists
     * 
     * @param string $key Input key
     * @return bool
     */
    public function hasInput(string $key): bool
    {
        return isset($this->request[$key]);
    }
    
    /**
     * Get all input values
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->request;
    }
    
    /**
     * Get only specified input values
     * 
     * @param array $keys Keys to include
     * @return array
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->request, array_flip($keys));
    }
    
    /**
     * Get all input values except specified keys
     * 
     * @param array $keys Keys to exclude
     * @return array
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->request, array_flip($keys));
    }
    
    /**
     * Set session value
     * 
     * @param string $key Session key
     * @param mixed $value Session value
     * @return void
     */
    public function setSession(string $key, $value): void
    {
        $this->session[$key] = $value;
    }
    
    /**
     * Get session value
     * 
     * @param string $key Session key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function getSession(string $key, $default = null)
    {
        return $this->session[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     * 
     * @param string $key Session key
     * @return bool
     */
    public function hasSession(string $key): bool
    {
        return isset($this->session[$key]);
    }
    
    /**
     * Remove session value
     * 
     * @param string $key Session key
     * @return void
     */
    public function removeSession(string $key): void
    {
        if (isset($this->session[$key])) {
            unset($this->session[$key]);
        }
    }
    
    /**
     * Clear all session data
     * 
     * @return void
     */
    public function clearSession(): void
    {
        session_unset();
        session_destroy();
        
        // Reset session array
        $this->session = [];
    }
    
    /**
     * Set success flash message
     * 
     * @param string $message Message content
     * @return void
     */
    public function success(string $message): void
    {
        $this->setFlash('success', $message);
    }
    
    /**
     * Set error flash message
     * 
     * @param string $message Message content
     * @return void
     */
    public function error(string $message): void
    {
        $this->setFlash('error', $message);
    }
    
    /**
     * Set warning flash message
     * 
     * @param string $message Message content
     * @return void
     */
    public function warning(string $message): void
    {
        $this->setFlash('warning', $message);
    }
    
    /**
     * Set info flash message
     * 
     * @param string $message Message content
     * @return void
     */
    public function info(string $message): void
    {
        $this->setFlash('info', $message);
    }
    
    /**
     * Log message to file
     * 
     * @param string $level Log level
     * @param string $message Message content
     * @return void
     */
    protected function log(string $level, string $message): void
    {
        if (empty($this->logFile)) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Append to log file
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Log info message
     * 
     * @param string $message Message content
     * @return void
     */
    protected function logInfo(string $message): void
    {
        $this->log('INFO', $message);
    }
    
    /**
     * Log warning message
     * 
     * @param string $message Message content
     * @return void
     */
    protected function logWarning(string $message): void
    {
        $this->log('WARNING', $message);
    }
    
    /**
     * Log error message
     * 
     * @param string $message Message content
     * @return void
     */
    protected function logError(string $message): void
    {
        $this->log('ERROR', $message);
    }
    
    /**
     * Log debug message (only in debug mode)
     * 
     * @param string $message Message content
     * @return void
     */
    protected function logDebug(string $message): void
    {
        if ($this->debugMode) {
            $this->log('DEBUG', $message);
        }
    }
    
    /**
     * Handle not found (404) error
     * 
     * @param string $message Custom error message
     * @return void
     */
    public function notFound(string $message = 'The requested resource was not found'): void
    {
        $this->statusCode = 404;
        
        if ($this->isApiRequest()) {
            $this->json(['message' => $message], 404);
        } else {
            $this->setViewData('message', $message);
            $this->display('errors/404');
        }
    }
    
    /**
     * Handle unauthorized (401) error
     * 
     * @param string $message Custom error message
     * @return void
     */
    public function unauthorized(string $message = 'You are not authorized to access this resource'): void
    {
        $this->statusCode = 401;
        
        if ($this->isApiRequest()) {
            $this->json(['message' => $message], 401);
        } else {
            $this->setViewData('message', $message);
            $this->display('errors/401');
        }
    }
    
    /**
     * Handle forbidden (403) error
     * 
     * @param string $message Custom error message
     * @return void
     */
    public function forbidden(string $message = 'You do not have permission to access this resource'): void
    {
        $this->statusCode = 403;
        
        if ($this->isApiRequest()) {
            $this->json(['message' => $message], 403);
        } else {
            $this->setViewData('message', $message);
            $this->display('errors/403');
        }
    }
    
    /**
     * Handle server error (500)
     * 
     * @param string $message Custom error message
     * @param Exception|null $exception Exception that caused the error
     * @return void
     */
    public function serverError(string $message = 'An internal server error occurred', ?Exception $exception = null): void
    {
        $this->statusCode = 500;
        
        // Log the error
        if ($exception) {
            $this->logError($exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine());
            $this->logError($exception->getTraceAsString());
        } else {
            $this->logError($message);
        }
        
        if ($this->isApiRequest()) {
            $response = ['message' => $message];
            
            // Add exception details in debug mode
            if ($this->debugMode && $exception) {
                $response['exception'] = [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => explode("\n", $exception->getTraceAsString())
                ];
            }
            
            $this->json($response, 500);
        } else {
            $this->setViewData('message', $message);
            
            // Add exception details in debug mode
            if ($this->debugMode && $exception) {
                $this->setViewData('exception', $exception);
            }
            
            $this->display('errors/500');
        }
    }
    
    /**
     * Check if current request is an API request
     * 
     * @return bool
     */
    protected function isApiRequest(): bool
    {
        // Check Accept header
        $acceptHeader = $this->headers['Accept'] ?? '';
        if (strpos($acceptHeader, 'application/json') !== false) {
            return true;
        }
        
        // Check URL path
        if (strpos($this->uri, '/api/') === 0) {
            return true;
        }
        
        // Check content type
        return $this->contentType === 'json';
    }
    
    /**
     * Add middleware to the controller
     * 
     * @param string|callable $middleware Middleware class name or callable
     * @return self
     */
    public function middleware($middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }
    
    /**
     * Run middleware stack
     * 
     * @return bool True if all middleware pass
     */
    public function runMiddleware(): bool
    {
        foreach ($this->middleware as $middleware) {
            if (is_string($middleware)) {
                // Instantiate middleware class
                $middlewareInstance = new $middleware();
                
                if (!$middlewareInstance->handle($this)) {
                    return false;
                }
            } elseif (is_callable($middleware)) {
                // Execute callable middleware
                if (!$middleware($this)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Set HTTP status code
     * 
     * @param int $code HTTP status code
     * @return self
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }
    
    /**
     * Set content type
     * 
     * @param string $type Content type
     * @return self
     */
    public function setContentType(string $type): self
    {
        if (isset($this->contentTypes[$type])) {
            $this->contentType = $type;
        }
        
        return $this;
    }
    
    /**
     * Get client IP address
     * 
     * @return string
     */
    protected function getClientIP(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim($_SERVER[$key]);
                // Handle multiple IPs in X-Forwarded-For
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Get current URL
     * 
     * @param bool $withQueryString Include query string
     * @return string
     */
    public function getCurrentUrl(bool $withQueryString = true): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        if (!$withQueryString && strpos($uri, '?') !== false) {
            $uri = strstr($uri, '?', true);
        }
        
        return "{$protocol}://{$host}{$uri}";
    }
    
    /**
     * Generate URL with base path
     * 
     * @param string $path URL path
     * @param array $params Query parameters
     * @return string
     */
    public function url(string $path, array $params = []): string
    {
        // Remove leading slash if present
        $path = ltrim($path, '/');
        
        // Get base URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        
        // Build URL
        $url = "{$protocol}://{$host}{$basePath}/{$path}";
        
        // Add query parameters if any
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * Generate asset URL
     * 
     * @param string $path Asset path
     * @return string
     */
    public function asset(string $path): string
    {
        // Remove leading slash if present
        $path = ltrim($path, '/');
        
        // Get base URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        
        return "{$protocol}://{$host}{$basePath}/assets/{$path}";
    }
}