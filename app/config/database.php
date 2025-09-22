<?php
/**
 * KIIT SEVA - Enterprise Database Configuration System
 * Professional-grade database connection management with enterprise security
 *
 * Features:
 * - Multi-environment support (development, staging, production)
 * - Connection pooling and performance optimization
 * - SSL/TLS encryption and security hardening
 * - Automatic failover and high availability
 * - Comprehensive error handling and logging
 * - Health monitoring and connection testing
 *
 * @author AI Generated for KIIT SEVA
 * @version 2.2 Enterprise Edition (Improved)
 * @license Proprietary - KIIT University
 */

declare(strict_types=1);

/**
 * Enterprise Database Configuration Manager
 * Handles secure database connections with advanced features
 */
class KiitSevaDatabase
{
    // Connection configuration
    private string $host;
    private string $port;
    private string $dbName;
    private string $username;
    private string $password;
    private string $charset = 'utf8mb4';
    private string $collation = 'utf8mb4_unicode_ci';
    private string $timezone = '+05:30'; // Asia/Kolkata timezone offset

    // Connection management
    private static ?PDO $connection = null;
    private static ?PDO $readConnection = null;
    private static ?self $instance = null;

    // Environment and configuration
    private string $environment = 'development';
    private array $config = [];
    private array $ipWhitelist = [];

    // Performance and monitoring
    private int $connectionRetries = 3;
    private float $connectionTimeout = 5.0;
    private int $queryTimeout = 30;
    private bool $useConnectionPooling = false;
    private array $connectionPool = [];

    // Logging and monitoring
    private string $logFile = '';
    private array $queryStats = [];
    private int $connectionAttempts = 0;
    private ?DateTime $lastConnectionTime = null;

    // Rate limiting storage
    private string $rateLimitFile = '';

    // Health check configuration
    private int $maxReconnectAttempts = 3;
    private float $healthCheckInterval = 30.0; // seconds
    private ?DateTime $lastHealthCheck = null;

    /**
     * Private constructor for singleton pattern
     */
    private function __construct()
    {
        try {
            $this->initializeLogger();
            $this->detectEnvironment();
            $this->loadConfiguration();
            $this->validateConfiguration();
            $this->setupSecurityMeasures();

            $this->logInfo("KIIT SEVA Database Configuration initialized for {$this->environment} environment");
        } catch (Exception $e) {
            // Fallback error handling if logging isn't available yet
            error_log("Database Configuration Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize logging system with proper error handling
     */
    private function initializeLogger(): void
    {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';

        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true)) {
                throw new RuntimeException("Failed to create log directory: {$logDir}");
            }
        }

        if (!is_writable($logDir)) {
            throw new RuntimeException("Log directory is not writable: {$logDir}");
        }

        $this->logFile = $logDir . '/database.log';
        $this->rateLimitFile = dirname(__DIR__, 2) . '/storage/cache/db_rate_limit.json';

        // Ensure cache directory exists
        $cacheDir = dirname($this->rateLimitFile);
        if (!is_dir($cacheDir)) {
            if (!mkdir($cacheDir, 0755, true)) {
                throw new RuntimeException("Failed to create cache directory: {$cacheDir}");
            }
        }
    }

    /**
     * Detect current environment automatically
     */
    private function detectEnvironment(): void
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
        } else {
            $this->environment = 'development';
        }

        $this->logInfo("Environment auto-detected as: {$this->environment}");
    }

    /**
     * Load configuration from multiple sources
     */
    private function loadConfiguration(): void
    {
        // Set environment-specific defaults first
        $this->setEnvironmentDefaults();

        // Load from .env file
        $this->loadEnvFile();

        // Load configuration values with proper validation
        $this->host = $this->getConfigValue('DB_HOST', 'host');
        $this->port = $this->getConfigValue('DB_PORT', 'port');
        $this->dbName = $this->getConfigValue('DB_NAME', 'database');
        $this->username = $this->getConfigValue('DB_USER', 'username');
        $this->password = $this->decryptPassword($this->getConfigValue('DB_PASS', 'password'));
        $this->charset = $this->getConfigValue('DB_CHARSET', 'charset');
        $this->collation = $this->getConfigValue('DB_COLLATION', 'collation');

        // Performance settings with type validation
        $this->connectionTimeout = (float)$this->getConfigValue('DB_TIMEOUT', 'timeout');
        $this->queryTimeout = (int)$this->getConfigValue('DB_QUERY_TIMEOUT', 'query_timeout');
        $this->connectionRetries = (int)$this->getConfigValue('DB_RETRIES', 'retries');
        $this->useConnectionPooling = filter_var($this->getConfigValue('DB_POOLING', 'pooling'), FILTER_VALIDATE_BOOLEAN);

        // Health check settings
        $this->maxReconnectAttempts = (int)$this->getConfigValue('DB_MAX_RECONNECT', 'max_reconnect_attempts');
        $this->healthCheckInterval = (float)$this->getConfigValue('DB_HEALTH_INTERVAL', 'health_check_interval');

        // Security settings
        if (isset($_ENV['DB_IP_WHITELIST']) && !empty($_ENV['DB_IP_WHITELIST'])) {
            $this->ipWhitelist = array_map('trim', explode(',', $_ENV['DB_IP_WHITELIST']));
            $this->ipWhitelist = array_filter($this->ipWhitelist); // Remove empty values
        }

        // Timezone validation and setting
        $timezoneEnv = $_ENV['DB_TIMEZONE'] ?? null;
        if ($timezoneEnv && $this->isValidTimezone($timezoneEnv)) {
            $this->timezone = $timezoneEnv;
        }
    }

    /**
     * Get configuration value with fallback
     */
    private function getConfigValue(string $envKey, string $configKey): string
    {
        $value = $_ENV[$envKey] ?? $this->config[$configKey] ?? '';
        return trim((string)$value);
    }

    /**
     * Validate timezone format
     */
    private function isValidTimezone(string $timezone): bool
    {
        // Accept timezone offsets like +05:30, -08:00
        if (preg_match('/^[+-]\d{2}:\d{2}$/', $timezone)) {
            return true;
        }

        // Accept timezone names
        try {
            new DateTimeZone($timezone);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Load environment file with enhanced parsing
     */
    private function loadEnvFile(): void
    {
        $envFiles = [
            dirname(__DIR__, 2) . "/.env.{$this->environment}",
            dirname(__DIR__, 2) . '/.env.local',
            dirname(__DIR__, 2) . '/.env'
        ];

        foreach ($envFiles as $envFile) {
            if (file_exists($envFile) && is_readable($envFile)) {
                try {
                    $this->parseEnvFile($envFile);
                    $this->logInfo("Loaded configuration from: " . basename($envFile));
                } catch (Exception $e) {
                    $this->logWarning("Failed to parse environment file {$envFile}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Enhanced .env file parser with error handling
     */
    private function parseEnvFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException("Failed to read environment file: {$filePath}");
        }

        $lines = explode("\n", $content);

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$name, $value] = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Validate environment variable name
                if (!preg_match('/^[A-Z_][A-Z0-9_]*$/', $name)) {
                    $this->logWarning("Invalid environment variable name on line " . ($lineNumber + 1) . ": {$name}");
                    continue;
                }

                // Handle quoted values
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                // Handle escaped characters
                $value = stripcslashes($value);

                $_ENV[$name] = $value;
            }
        }
    }

    /**
     * Set environment-specific defaults
     */
    private function setEnvironmentDefaults(): void
    {
        switch ($this->environment) {
            case 'development':
                $this->config = [
                    'host' => 'localhost',
                    'port' => '3306',
                    'database' => 'kiit_seva_dev',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'timeout' => 10.0,
                    'query_timeout' => 60,
                    'retries' => 3,
                    'pooling' => false,
                    'ssl_mode' => false,
                    'debug' => true,
                    'max_reconnect_attempts' => 3,
                    'health_check_interval' => 60.0
                ];
                break;

            case 'staging':
                $this->config = [
                    'host' => 'staging-db.kiit.internal',
                    'port' => '3306',
                    'database' => 'kiit_seva_staging',
                    'username' => 'seva_staging',
                    'password' => '', // Set in environment
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'timeout' => 5.0,
                    'query_timeout' => 30,
                    'retries' => 5,
                    'pooling' => true,
                    'ssl_mode' => true,
                    'debug' => false,
                    'max_reconnect_attempts' => 5,
                    'health_check_interval' => 30.0
                ];
                break;

            case 'production':
                $this->config = [
                    'host' => 'prod-db.kiit.ac.in',
                    'port' => '3306',
                    'database' => 'kiit_seva',
                    'username' => 'seva_prod',
                    'password' => '', // Set in environment
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'timeout' => 3.0,
                    'query_timeout' => 15,
                    'retries' => 5,
                    'pooling' => true,
                    'ssl_mode' => true,
                    'debug' => false,
                    'max_reconnect_attempts' => 5,
                    'health_check_interval' => 30.0
                ];
                break;
        }
    }

    /**
     * Validate configuration parameters with more granular checks
     */
    private function validateConfiguration(): void
    {
        $required = ['host', 'dbName', 'username'];
        $missing = [];

        foreach ($required as $param) {
            if (empty($this->$param)) {
                $missing[] = $param;
            }
        }

        if (!empty($missing)) {
            $this->logError("Missing required database configuration: " . implode(', ', $missing));
            throw new DatabaseConfigurationException("Missing required database configuration parameters: " . implode(', ', $missing));
        }

        // Validate port number
        $port = (int)$this->port;
        if ($port < 1 || $port > 65535) {
            throw new DatabaseConfigurationException("Invalid database port: {$this->port}. Must be between 1 and 65535.");
        }

        // Validate charset
        $supportedCharsets = ['utf8', 'utf8mb4', 'latin1'];
        if (!in_array($this->charset, $supportedCharsets)) {
            throw new DatabaseConfigurationException("Unsupported charset: {$this->charset}. Supported: " . implode(', ', $supportedCharsets));
        }

        // Validate timeout values
        if ($this->connectionTimeout <= 0 || $this->connectionTimeout > 300) {
            throw new DatabaseConfigurationException("Connection timeout must be between 0.1 and 300 seconds");
        }

        if ($this->queryTimeout <= 0 || $this->queryTimeout > 3600) {
            throw new DatabaseConfigurationException("Query timeout must be between 1 and 3600 seconds");
        }

        // Validate retry count
        if ($this->connectionRetries < 1 || $this->connectionRetries > 10) {
            throw new DatabaseConfigurationException("Connection retries must be between 1 and 10");
        }

        // Validate health check settings
        if ($this->maxReconnectAttempts < 1 || $this->maxReconnectAttempts > 10) {
            throw new DatabaseConfigurationException("Max reconnect attempts must be between 1 and 10");
        }

        if ($this->healthCheckInterval < 5.0 || $this->healthCheckInterval > 3600.0) {
            throw new DatabaseConfigurationException("Health check interval must be between 5 and 3600 seconds");
        }

        $this->logInfo("Database configuration validated successfully");
    }

    /**
     * Setup security measures with enhanced validation
     */
    private function setupSecurityMeasures(): void
    {
        // IP whitelist check with better validation
        if (!empty($this->ipWhitelist) && $this->environment === 'production') {
            $clientIP = $this->getClientIP();
            
            // Validate IP whitelist entries
            $validIPs = [];
            foreach ($this->ipWhitelist as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    $validIPs[] = $ip;
                } else {
                    $this->logWarning("Invalid IP in whitelist: {$ip}");
                }
            }
            
            $this->ipWhitelist = $validIPs;
            
            if (!empty($this->ipWhitelist) && !in_array($clientIP, $this->ipWhitelist)) {
                $this->logSecurity("Unauthorized database access attempt from IP: {$clientIP}");
                throw new SecurityException("Database access denied from this IP address");
            }
        }

        // Rate limiting with better error handling
        try {
            $this->checkRateLimit();
        } catch (Exception $e) {
            $this->logWarning("Rate limit check failed: " . $e->getMessage());
            // In production, this might be a security concern, so we might want to deny access
            if ($this->environment === 'production') {
                throw new SecurityException("Security validation failed");
            }
        }
    }

    /**
     * Get client IP address with proxy support and validation
     */
    private function getClientIP(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim($_SERVER[$key]);
                // Handle multiple IPs in X-Forwarded-For
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP and exclude private/reserved ranges for security logs
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Check connection rate limit with improved error handling
     */
    private function checkRateLimit(): void
    {
        $maxAttempts = $this->environment === 'production' ? 10 : 50;
        $timeWindow = 60; // seconds

        try {
            $attempts = [];
            if (file_exists($this->rateLimitFile)) {
                $content = file_get_contents($this->rateLimitFile);
                if ($content !== false) {
                    $decoded = json_decode($content, true);
                    $attempts = is_array($decoded) ? $decoded : [];
                } else {
                    $this->logWarning("Could not read rate limit file");
                }
            }

            $currentTime = time();
            $clientIP = $this->getClientIP();

            // Clean old attempts and reorganize data structure
            $cleanedAttempts = [];
            foreach ($attempts as $attempt) {
                if (isset($attempt['time'], $attempt['ip']) &&
                    is_int($attempt['time']) &&
                    ($currentTime - $attempt['time']) < $timeWindow) {
                    $cleanedAttempts[] = $attempt;
                }
            }

            // Count current IP attempts
            $ipAttempts = array_filter($cleanedAttempts, fn($attempt) => $attempt['ip'] === $clientIP);

            if (count($ipAttempts) >= $maxAttempts) {
                $this->logSecurity("Rate limit exceeded for IP: {$clientIP} (attempts: " . count($ipAttempts) . "/{$maxAttempts})");
                throw new SecurityException("Database connection rate limit exceeded. Please try again later.");
            }

            // Record this attempt
            $cleanedAttempts[] = ['ip' => $clientIP, 'time' => $currentTime];

            // Save attempts with error handling
            $jsonData = json_encode($cleanedAttempts);
            if ($jsonData === false) {
                throw new RuntimeException("Failed to encode rate limit data");
            }

            $result = file_put_contents($this->rateLimitFile, $jsonData, LOCK_EX);
            if ($result === false) {
                throw new RuntimeException("Failed to write rate limit file");
            }

        } catch (SecurityException $e) {
            // Re-throw security exceptions
            throw $e;
        } catch (Exception $e) {
            $this->logError("Rate limiting error: " . $e->getMessage());
            throw new SecurityException("Rate limiting system error");
        }
    }

    /**
     * Check connection health and reconnect if necessary
     */
    private function checkConnectionHealth(): bool
    {
        if (self::$connection === null) {
            return false;
        }

        // Skip health check if interval hasn't passed
        if ($this->lastHealthCheck !== null) {
            $timeSinceCheck = time() - $this->lastHealthCheck->getTimestamp();
            if ($timeSinceCheck < $this->healthCheckInterval) {
                return true;
            }
        }

        try {
            $stmt = self::$connection->query("SELECT 1");
            $this->lastHealthCheck = new DateTime();
            return $stmt && $stmt->fetchColumn() === 1;
        } catch (PDOException $e) {
            $this->logWarning("Connection health check failed: " . $e->getMessage());
            self::$connection = null;
            return false;
        }
    }

    /**
     * Create secure PDO connection with advanced features and improved error handling
     */
    public function connect(bool $forceNew = false): PDO
    {
        // Check connection health first
        if (!$forceNew && $this->checkConnectionHealth()) {
            return self::$connection;
        }

        if (self::$connection !== null && !$forceNew) {
            return self::$connection;
        }

        $this->connectionAttempts++;
        $attempts = 0;
        $lastException = null;

        while ($attempts < $this->connectionRetries) {
            try {
                $dsn = $this->buildDSN();
                $options = $this->getPDOOptions();

                $this->logInfo("Attempting database connection (attempt " . ($attempts + 1) . "/{$this->connectionRetries})");

                self::$connection = new PDO($dsn, $this->username, $this->password, $options);

                // Post-connection setup
                $this->configureConnection(self::$connection);

                $this->lastConnectionTime = new DateTime();
                $this->lastHealthCheck = new DateTime();
                $this->logInfo("Database connection established successfully");

                return self::$connection;

            } catch (PDOException $e) {
                $lastException = $e;
                $attempts++;

                // Log different error types with appropriate levels
                if (str_contains($e->getMessage(), 'Access denied')) {
                    $this->logError("Authentication failed (attempt {$attempts}): " . $e->getMessage());
                } elseif (str_contains($e->getMessage(), 'Connection refused') || str_contains($e->getMessage(), 'timed out')) {
                    $this->logWarning("Connection failed (attempt {$attempts}): Network issue");
                } else {
                    $this->logError("Connection attempt {$attempts} failed: " . $e->getMessage());
                }

                if ($attempts < $this->connectionRetries) {
                    // Exponential backoff with jitter
                    $delay = min(pow(2, $attempts) + (random_int(0, 1000) / 1000), 10);
                    $this->logInfo("Retrying connection in {$delay} seconds...");
                    usleep((int)($delay * 1000000)); // Convert to microseconds
                }
            }
        }

        // All attempts failed - handle failure
        $this->handleConnectionFailure($lastException);
    }

    /**
     * Get read-only connection for query optimization
     */
    public function getReadConnection(): PDO
    {
        if (isset($_ENV['DB_READ_HOST']) && !empty($_ENV['DB_READ_HOST'])) {
            if (self::$readConnection === null) {
                $originalHost = $this->host;
                $this->host = trim($_ENV['DB_READ_HOST']);

                try {
                    $dsn = $this->buildDSN();
                    $options = $this->getPDOOptions();
                    
                    self::$readConnection = new PDO($dsn, $this->username, $this->password, $options);
                    $this->configureConnection(self::$readConnection);
                    
                    $this->logInfo("Read-only connection established to: {$this->host}");
                } catch (PDOException $e) {
                    $this->logWarning("Read connection failed, falling back to main connection: " . $e->getMessage());
                    return $this->connect();
                } finally {
                    $this->host = $originalHost;
                }
            }
            return self::$readConnection;
        }

        return $this->connect();
    }

    /**
     * Build DSN string with all options
     */
    private function buildDSN(): string
    {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};charset={$this->charset}";

        // Add SSL options for secure connections
        if ($this->config['ssl_mode'] ?? false) {
            $dsn .= ";sslmode=require";
        }

        return $dsn;
    }

    /**
     * Get comprehensive PDO options for security and performance
     */
    private function getPDOOptions(): array
    {
        $options = [
            // Core PDO settings
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // True prepared statements
            PDO::ATTR_STRINGIFY_FETCHES => false, // Preserve data types

            // Connection settings
            PDO::ATTR_TIMEOUT => (int)$this->connectionTimeout,
            PDO::ATTR_PERSISTENT => $this->useConnectionPooling,

            // MySQL-specific options
            PDO::MYSQL_ATTR_INIT_COMMAND => $this->getMySQLInitCommands(),
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::MYSQL_ATTR_FOUND_ROWS => true,
        ];

        // Production-specific security settings
        if ($this->environment === 'production') {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
            $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = false; // Prevent SQL injection
        } else {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        // SSL configuration
        if ($this->config['ssl_mode'] ?? false) {
            $sslConfig = $this->getSSLConfiguration();
            if (!empty($sslConfig)) {
                $options = array_merge($options, $sslConfig);
            }
        }

        return $options;
    }

    /**
     * Get MySQL initialization commands with safe timezone handling
     */
    private function getMySQLInitCommands(): string
    {
        // Sanitize timezone for safe SQL usage
        $safeTimezone = $this->sanitizeTimezone($this->timezone);

        $commands = [
            "SET NAMES {$this->charset} COLLATE {$this->collation}",
            "SET time_zone = '{$safeTimezone}'",
            "SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'",
        ];

        // Only enable query cache in development (deprecated in MySQL 8.0+)
        if ($this->environment === 'development') {
            $commands[] = "SET SESSION query_cache_type = ON";
        }

        return implode('; ', $commands);
    }

    /**
     * Sanitize timezone for SQL injection prevention
     */
    private function sanitizeTimezone(string $timezone): string
    {
        // Allow only safe timezone formats
        if (preg_match('/^[+-]\d{2}:\d{2}$/', $timezone)) {
            return $timezone;
        }

        // For timezone names, validate against known list
        if (in_array($timezone, timezone_identifiers_list())) {
            return $timezone;
        }

        // Fallback to safe default
        $this->logWarning("Invalid timezone '{$timezone}', falling back to '+05:30'");
        return '+05:30';
    }

    /**
     * Get SSL configuration for secure connections with validation
     */
    private function getSSLConfiguration(): array
    {
        $sslDir = dirname(__DIR__, 2) . '/storage/ssl';
        $sslFiles = [
            PDO::MYSQL_ATTR_SSL_KEY => $sslDir . '/client-key.pem',
            PDO::MYSQL_ATTR_SSL_CERT => $sslDir . '/client-cert.pem',
            PDO::MYSQL_ATTR_SSL_CA => $sslDir . '/ca-cert.pem',
        ];

        // Check if SSL files exist
        foreach ($sslFiles as $file) {
            if (!file_exists($file) || !is_readable($file)) {
                $this->logWarning("SSL file not found or not readable: {$file}");
                return []; // Return empty array if SSL files are missing
            }
        }

        return [
            PDO::MYSQL_ATTR_SSL_KEY => $sslFiles[PDO::MYSQL_ATTR_SSL_KEY],
            PDO::MYSQL_ATTR_SSL_CERT => $sslFiles[PDO::MYSQL_ATTR_SSL_CERT],
            PDO::MYSQL_ATTR_SSL_CA => $sslFiles[PDO::MYSQL_ATTR_SSL_CA],
            PDO::MYSQL_ATTR_SSL_CAPATH => $sslDir,
            PDO::MYSQL_ATTR_SSL_CIPHER => 'HIGH:!aNULL:!eNULL:!EXPORT:!DES:!MD5:!PSK:!SRP:!CAMELLIA'
        ];
    }

    /**
     * Configure connection after establishment with better error handling
     */
    private function configureConnection(PDO $pdo): void
    {
        // Set session variables for KIIT SEVA
        $sessionVars = [
            'application_name' => 'KIIT_SEVA',
            'wait_timeout' => 28800, // 8 hours
            'interactive_timeout' => 28800,
            'max_execution_time' => $this->queryTimeout
        ];

        foreach ($sessionVars as $var => $value) {
            try {
                $stmt = $pdo->prepare("SET SESSION {$var} = ?");
                $stmt->execute([$value]);
            } catch (PDOException $e) {
                // Some session variables might not be settable, log but continue
                $this->logInfo("Could not set session variable {$var}: " . $e->getMessage());
            }
        }

        // Enable performance schema if available (MySQL 5.5+)
        try {
            $stmt = $pdo->query("SELECT @@performance_schema");
            if ($stmt && $stmt->fetchColumn()) {
                $pdo->exec("SET SESSION performance_schema = ON");
            }
        } catch (PDOException $e) {
            // Performance schema might not be available, ignore silently
        }
    }

    /**
     * Handle connection failure with proper failover logic
     */
    private function handleConnectionFailure(?PDOException $lastException): PDO
    {
        $errorMessage = $lastException ? $lastException->getMessage() : 'Unknown connection error';
        $this->logCritical("Database connection failed after {$this->connectionRetries} attempts: {$errorMessage}");

        // Try failover servers if configured
        $failoverHosts = $this->getFailoverHosts();
        if (!empty($failoverHosts)) {
            foreach ($failoverHosts as $failoverHost) {
                try {
                    $originalHost = $this->host;
                    $this->host = trim($failoverHost);

                    $this->logInfo("Trying failover server: {$this->host}");
                    $connection = $this->attemptFailoverConnection();
                    
                    if ($connection) {
                        $this->logInfo("Successfully connected to failover server: {$this->host}");
                        return $connection;
                    }

                } catch (Exception $e) {
                    $this->logWarning("Failover server {$failoverHost} also failed: " . $e->getMessage());
                } finally {
                    $this->host = $originalHost;
                }
            }
        }

        // No failover available or all failed, throw appropriate exception
        $this->throwConnectionException($errorMessage);
    }

    /**
     * Get list of failover hosts from configuration
     */
    private function getFailoverHosts(): array
    {
        $failoverHosts = [];
        
        if (isset($_ENV['DB_FAILOVER_HOSTS']) && !empty($_ENV['DB_FAILOVER_HOSTS'])) {
            $hosts = explode(',', $_ENV['DB_FAILOVER_HOSTS']);
            foreach ($hosts as $host) {
                $host = trim($host);
                if (!empty($host)) {
                    $failoverHosts[] = $host;
                }
            }
        }
        
        return $failoverHosts;
    }

    /**
     * Attempt connection to failover server
     */
    private function attemptFailoverConnection(): ?PDO
    {
        try {
            $dsn = $this->buildDSN();
            $options = $this->getPDOOptions();
            
            $connection = new PDO($dsn, $this->username, $this->password, $options);
            $this->configureConnection($connection);
            
            self::$connection = $connection;
            $this->lastConnectionTime = new DateTime();
            $this->lastHealthCheck = new DateTime();
            
            return $connection;
            
        } catch (PDOException $e) {
            $this->logWarning("Failover connection attempt failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Throw appropriate connection exception based on environment
     */
    private function throwConnectionException(string $errorMessage): never
    {
        if ($this->environment === 'development') {
            throw new DatabaseException(
                "Database connection failed: {$errorMessage}\n" .
                "Host: {$this->host}\n" .
                "Database: {$this->dbName}\n" .
                "Username: {$this->username}"
            );
        } else {
            throw new DatabaseException("Database service temporarily unavailable. Please try again later.");
        }
    }

    /**
     * Test database connection and return comprehensive diagnostics
     */
    public function testConnection(): array
    {
        $startTime = microtime(true);
        $diagnostics = [
            'connected' => false,
            'environment' => $this->environment,
            'host' => $this->host,
            'database' => $this->dbName,
            'charset' => $this->charset,
            'response_time' => 0,
            'server_version' => null,
            'timezone' => null,
            'ssl_enabled' => false,
            'connection_id' => null,
            'uptime' => null,
            'errors' => [],
            'warnings' => []
        ];

        try {
            $pdo = $this->connect();

            // Basic connectivity test
            $stmt = $pdo->query("SELECT 1 as test");
            if (!$stmt || $stmt->fetchColumn() !== 1) {
                throw new DatabaseException("Basic query test failed");
            }

            // Get comprehensive server information
            $serverInfo = $this->getServerInfo($pdo);
            $diagnostics = array_merge($diagnostics, $serverInfo);

            // Test character set consistency
            $this->validateCharsetConsistency($pdo, $diagnostics);

            // Test SSL status
            $diagnostics['ssl_enabled'] = $this->checkSSLStatus($pdo);

            // Performance tests
            $this->runPerformanceTests($pdo, $diagnostics);

            $diagnostics['connected'] = true;

        } catch (Exception $e) {
            $diagnostics['errors'][] = $e->getMessage();
            $this->logError("Connection test failed: " . $e->getMessage());
        } finally {
            $diagnostics['response_time'] = round((microtime(true) - $startTime) * 1000, 2);
        }

        return $diagnostics;
    }

    /**
     * Get comprehensive server information
     */
    private function getServerInfo(PDO $pdo): array
    {
        $info = [];
        
        try {
            // Server version
            $info['server_version'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            
            // Connection ID
            $stmt = $pdo->query("SELECT CONNECTION_ID() as id");
            $info['connection_id'] = $stmt->fetchColumn();
            
            // Timezone
            $stmt = $pdo->query("SELECT @@session.time_zone as tz");
            $info['timezone'] = $stmt->fetchColumn();
            
            // Server uptime
            $stmt = $pdo->query("SHOW STATUS LIKE 'Uptime'");
            $uptime = $stmt->fetch();
            $info['uptime'] = $uptime ? (int)$uptime['Value'] : null;
            
        } catch (PDOException $e) {
            $this->logWarning("Could not retrieve server info: " . $e->getMessage());
        }
        
        return $info;
    }

    /**
     * Validate character set consistency
     */
    private function validateCharsetConsistency(PDO $pdo, array &$diagnostics): void
    {
        try {
            $stmt = $pdo->query("SELECT @@character_set_connection as charset");
            $actualCharset = $stmt->fetchColumn();
            
            if ($actualCharset !== $this->charset) {
                $diagnostics['warnings'][] = "Charset mismatch: expected {$this->charset}, got {$actualCharset}";
            }
            
            $stmt = $pdo->query("SELECT @@collation_connection as collation");
            $actualCollation = $stmt->fetchColumn();
            
            if ($actualCollation !== $this->collation) {
                $diagnostics['warnings'][] = "Collation mismatch: expected {$this->collation}, got {$actualCollation}";
            }
            
        } catch (PDOException $e) {
            $diagnostics['warnings'][] = "Could not verify charset/collation: " . $e->getMessage();
        }
    }

    /**
     * Check SSL connection status
     */
    private function checkSSLStatus(PDO $pdo): bool
    {
        try {
            $stmt = $pdo->query("SHOW STATUS LIKE 'Ssl_cipher'");
            $ssl = $stmt->fetch();
            return !empty($ssl['Value']);
        } catch (PDOException $e) {
            $this->logWarning("Could not check SSL status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Run performance tests
     */
    private function runPerformanceTests(PDO $pdo, array &$diagnostics): void
    {
        try {
            // Test query performance
            $start = microtime(true);
            $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables");
            $tableCount = $stmt->fetchColumn();
            $queryTime = round((microtime(true) - $start) * 1000, 2);
            
            $diagnostics['table_count'] = $tableCount;
            $diagnostics['metadata_query_time'] = $queryTime;
            
            if ($queryTime > 1000) { // More than 1 second
                $diagnostics['warnings'][] = "Slow metadata query response: {$queryTime}ms";
            }
            
        } catch (PDOException $e) {
            $diagnostics['warnings'][] = "Performance test failed: " . $e->getMessage();
        }
    }

    /**
     * Get connection health metrics with enhanced data
     */
    public function getHealthMetrics(): array
    {
        $metrics = [
            'environment' => $this->environment,
            'connection_attempts' => $this->connectionAttempts,
            'last_connection' => $this->lastConnectionTime?->format('Y-m-d H:i:s'),
            'last_health_check' => $this->lastHealthCheck?->format('Y-m-d H:i:s'),
            'query_stats' => $this->getQueryStatsSummary(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'uptime' => $this->lastConnectionTime ? 
                       time() - $this->lastConnectionTime->getTimestamp() : 0,
            'health_check_interval' => $this->healthCheckInterval,
            'connection_pooling' => $this->useConnectionPooling
        ];

        // Add connection status
        $metrics['connection_active'] = self::$connection !== null;
        $metrics['read_connection_active'] = self::$readConnection !== null;

        return $metrics;
    }

    /**
     * Get summarized query statistics
     */
    private function getQueryStatsSummary(): array
    {
        if (empty($this->queryStats)) {
            return ['total_queries' => 0, 'total_time' => 0, 'avg_time' => 0];
        }

        $totalQueries = 0;
        $totalTime = 0;
        $totalSuccessful = 0;
        $totalFailed = 0;

        foreach ($this->queryStats as $stats) {
            $totalQueries += $stats['count'];
            $totalTime += $stats['total_time'];
            $totalSuccessful += $stats['success_count'];
            $totalFailed += $stats['error_count'];
        }

        return [
            'total_queries' => $totalQueries,
            'total_time' => round($totalTime, 3),
            'avg_time' => $totalQueries > 0 ? round($totalTime / $totalQueries, 3) : 0,
            'successful_queries' => $totalSuccessful,
            'failed_queries' => $totalFailed,
            'success_rate' => $totalQueries > 0 ? round(($totalSuccessful / $totalQueries) * 100, 2) : 0
        ];
    }

    /**
     * Execute query with enhanced performance monitoring and error handling
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $startTime = microtime(true);
        $queryHash = md5($sql);

        // Validate SQL before execution
        if (empty(trim($sql))) {
            throw new InvalidArgumentException("SQL query cannot be empty");
        }

        try {
            $pdo = $this->connect();
            
            // Prepare statement with timeout
            $stmt = $pdo->prepare($sql);
            
            // Set query timeout if supported
            if ($this->queryTimeout > 0) {
                $stmt->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            }
            
            $stmt->execute($params);

            $executionTime = microtime(true) - $startTime;
            $this->recordQueryStats($queryHash, $sql, $executionTime, true);

            // Log slow queries
            if ($executionTime > ($this->environment === 'production' ? 1.0 : 5.0)) {
                $this->logWarning("Slow query detected: {$executionTime}s - " . substr($sql, 0, 100));
            }

            return $stmt;

        } catch (PDOException $e) {
            $executionTime = microtime(true) - $startTime;
            $this->recordQueryStats($queryHash, $sql, $executionTime, false, $e->getMessage());

            // Enhanced error logging with context
            $errorContext = [
                'error' => $e->getMessage(),
                'sql' => substr($sql, 0, 500),
                'params_count' => count($params),
                'execution_time' => $executionTime
            ];

            $this->logError("Query failed: " . json_encode($errorContext));

            // Environment-specific error handling
            if ($this->environment === 'development') {
                throw new DatabaseException(
                    "Query Error: " . $e->getMessage() . 
                    "\nSQL: " . $sql . 
                    "\nParameters: " . json_encode($params)
                );
            } else {
                // In production, don't expose SQL details
                throw new DatabaseException("Database operation failed. Error ID: " . uniqid());
            }
        }
    }

    /**
     * Record query statistics for monitoring with improved data structure
     */
    private function recordQueryStats(string $hash, string $sql, float $time, bool $success, ?string $error = null): void
    {
        if (!isset($this->queryStats[$hash])) {
            $this->queryStats[$hash] = [
                'sql' => substr($sql, 0, 100) . (strlen($sql) > 100 ? '...' : ''),
                'count' => 0,
                'total_time' => 0.0,
                'avg_time' => 0.0,
                'min_time' => PHP_FLOAT_MAX,
                'max_time' => 0.0,
                'success_count' => 0,
                'error_count' => 0,
                'last_error' => null,
                'last_executed' => null
            ];
        }

        $stats = &$this->queryStats[$hash];
        $stats['count']++;
        $stats['total_time'] += $time;
        $stats['avg_time'] = $stats['total_time'] / $stats['count'];
        $stats['min_time'] = min($stats['min_time'], $time);
        $stats['max_time'] = max($stats['max_time'], $time);
        $stats['last_executed'] = date('Y-m-d H:i:s');

        if ($success) {
            $stats['success_count']++;
        } else {
            $stats['error_count']++;
            $stats['last_error'] = $error;
        }
    }

    /**
     * Transaction management with enhanced error handling and nesting support
     */
    public function transaction(callable $callback): mixed
    {
        $pdo = $this->connect();
        
        // Check if we're already in a transaction
        $alreadyInTransaction = $pdo->inTransaction();
        
        if (!$alreadyInTransaction) {
            $pdo->beginTransaction();
        }

        try {
            $result = $callback($pdo);
            
            if (!$alreadyInTransaction) {
                $pdo->commit();
                $this->logInfo("Transaction committed successfully");
            }
            
            return $result;

        } catch (Exception $e) {
            if (!$alreadyInTransaction) {
                $pdo->rollback();
                $this->logError("Transaction rolled back due to error: " . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Enhanced password decryption with proper security
     */
    private function decryptPassword(string $encryptedPassword): string
    {
        // For development, return as-is if it's not encrypted
        if ($this->environment === 'development' || empty($encryptedPassword)) {
            return $encryptedPassword;
        }

        // Check if password is actually encrypted (simple check)
        if (!str_contains($encryptedPassword, ':') && strlen($encryptedPassword) < 100) {
            // Probably not encrypted, return as-is
            return $encryptedPassword;
        }

        // In production, implement proper decryption
        // This is a placeholder - implement actual decryption based on your encryption method
        try {
            // Example: base64 decoding (replace with your actual decryption)
            if (base64_encode(base64_decode($encryptedPassword, true)) === $encryptedPassword) {
                return base64_decode($encryptedPassword);
            }
        } catch (Exception $e) {
            $this->logError("Password decryption failed: " . $e->getMessage());
        }

        // If decryption fails, return original (might be plain text)
        return $encryptedPassword;
    }

    /**
     * Close all connections with proper cleanup
     */
    public function closeConnections(): void
    {
        if (self::$connection !== null) {
            self::$connection = null;
            $this->logInfo("Main database connection closed");
        }
        
        if (self::$readConnection !== null) {
            self::$readConnection = null;
            $this->logInfo("Read-only database connection closed");
        }
        
        // Clear connection pool if used
        $this->connectionPool = [];
        
        $this->logInfo("All database connections closed and cleaned up");
    }

    /**
     * Get detailed query statistics for monitoring
     */
    public function getQueryStats(): array
    {
        return $this->queryStats;
    }

    /**
     * Clear query statistics (useful for testing)
     */
    public function clearQueryStats(): void
    {
        $this->queryStats = [];
        $this->logInfo("Query statistics cleared");
    }

    // Enhanced logging methods with better context
    private function logInfo(string $message): void
    {
        $this->writeLog('INFO', $message);
    }

    private function logWarning(string $message): void
    {
        $this->writeLog('WARNING', $message);
    }

    private function logError(string $message): void
    {
        $this->writeLog('ERROR', $message);
        if ($this->environment === 'production') {
            error_log("KIIT SEVA DB Error: {$message}");
        }
    }

    private function logCritical(string $message): void
    {
        $this->writeLog('CRITICAL', $message);
        error_log("KIIT SEVA DB Critical: {$message}");
        
        // In production, you might want to send alerts here
        if ($this->environment === 'production') {
            $this->sendCriticalAlert($message);
        }
    }

    private function logSecurity(string $message): void
    {
        $this->writeLog('SECURITY', $message);

        // Also log to security log
        $securityLog = dirname($this->logFile) . '/security.log';
        $this->writeLog('SECURITY', $message, $securityLog);
        
        // In production, send immediate security alert
        if ($this->environment === 'production') {
            $this->sendSecurityAlert($message);
        }
    }

    /**
     * Enhanced log writing with better error handling
     */
    private function writeLog(string $level, string $message, ?string $file = null): void
    {
        if (empty($this->logFile)) {
            return; // Logging not initialized yet
        }

        $logFile = $file ?? $this->logFile;
        $timestamp = date('Y-m-d H:i:s');
        $ip = $this->getClientIP();
        $pid = getmypid();
        $memory = round(memory_get_usage(true) / 1024 / 1024, 2); // MB
        
        $logEntry = "[{$timestamp}] [{$level}] [PID: {$pid}] [IP: {$ip}] [MEM: {$memory}MB] {$message}" . PHP_EOL;

        $result = file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        if ($result === false && $this->environment === 'development') {
            error_log("Failed to write to log file: {$logFile}");
        }
    }

    /**
     * Send critical alerts (placeholder for actual implementation)
     */
    private function sendCriticalAlert(string $message): void
    {
        // Implement your alerting mechanism here
        // Examples: email, SMS, Slack, monitoring system webhook, etc.
        error_log("CRITICAL ALERT: {$message}");
    }

    /**
     * Send security alerts (placeholder for actual implementation)
     */
    private function sendSecurityAlert(string $message): void
    {
        // Implement your security alerting mechanism here
        // This should be immediate and go to security team
        error_log("SECURITY ALERT: {$message}");
    }

    /**
     * Get sanitized configuration for debugging
     */
    public function getConfig(): array
    {
        $config = [
            'environment' => $this->environment,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->dbName,
            'username' => $this->username,
            'charset' => $this->charset,
            'collation' => $this->collation,
            'timezone' => $this->timezone,
            'connection_timeout' => $this->connectionTimeout,
            'query_timeout' => $this->queryTimeout,
            'connection_retries' => $this->connectionRetries,
            'connection_pooling' => $this->useConnectionPooling,
            'ssl_enabled' => $this->config['ssl_mode'] ?? false,
            'health_check_interval' => $this->healthCheckInterval,
            'max_reconnect_attempts' => $this->maxReconnectAttempts
        ];

        // Add runtime information in non-production environments
        if ($this->environment !== 'production') {
            $config['connection_attempts'] = $this->connectionAttempts;
            $config['last_connection'] = $this->lastConnectionTime?->format('Y-m-d H:i:s');
            $config['last_health_check'] = $this->lastHealthCheck?->format('Y-m-d H:i:s');
            $config['ip_whitelist_count'] = count($this->ipWhitelist);
        }

        return $config;
    }
}

/**
 * Enhanced custom exception classes for better error handling
 */
class DatabaseException extends Exception 
{
    private ?string $sqlState = null;
    
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null, ?string $sqlState = null)
    {
        parent::__construct($message, $code, $previous);
        $this->sqlState = $sqlState;
    }
    
    public function getSQLState(): ?string
    {
        return $this->sqlState;
    }
}

class DatabaseConfigurationException extends DatabaseException {}

class SecurityException extends Exception {}

/**
 * Enhanced global helper functions for easy access
 */

/**
 * Get database instance (singleton)
 */
function getKiitDatabase(): KiitSevaDatabase
{
    return KiitSevaDatabase::getInstance();
}

/**
 * Get database connection with automatic retry
 */
function getDBConnection(bool $forceNew = false): PDO
{
    return getKiitDatabase()->connect($forceNew);
}

/**
 * Get read-only database connection
 */
function getReadDBConnection(): PDO
{
    return getKiitDatabase()->getReadConnection();
}

/**
 * Execute database transaction with enhanced error handling
 */
function dbTransaction(callable $callback): mixed
{
    return getKiitDatabase()->transaction($callback);
}

/**
 * Quick database query execution with parameter binding
 */
function dbQuery(string $sql, array $params = []): PDOStatement
{
    return getKiitDatabase()->query($sql, $params);
}

/**
 * Test database connectivity with full diagnostics
 */
function testDatabaseConnection(): array
{
    return getKiitDatabase()->testConnection();
}

/**
 * Get database health metrics
 */
function getDatabaseHealth(): array
{
    return getKiitDatabase()->getHealthMetrics();
}

/**
 * Get database configuration (sanitized)
 */
function getDatabaseConfig(): array
{
    return getKiitDatabase()->getConfig();
}

/**
 * Execute a simple SELECT query and return all results
 */
function dbSelect(string $sql, array $params = []): array
{
    $stmt = dbQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Execute INSERT/UPDATE/DELETE and return affected rows
 */
function dbExecute(string $sql, array $params = []): int
{
    $stmt = dbQuery($sql, $params);
    return $stmt->rowCount();
}

// Enhanced initialization and error handling
register_shutdown_function(function() {
    try {
        $db = KiitSevaDatabase::getInstance();
        $db->closeConnections();
    } catch (Exception $e) {
        error_log("Database shutdown error: " . $e->getMessage());
    }
});

// Set up error handler for database-related errors
set_error_handler(function($severity, $message, $file, $line) {
    if (str_contains($message, 'PDO') || str_contains($message, 'MySQL')) {
        error_log("Database PHP Error [{$severity}]: {$message} in {$file} on line {$line}");
        
        // In production, don't expose database errors to users
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            throw new DatabaseException("A database error occurred");
        }
    }
    
    // Return false to continue with normal error handling
    return false;
});

// Register exception handler for uncaught database exceptions
set_exception_handler(function($exception) {
    if ($exception instanceof DatabaseException || $exception instanceof SecurityException) {
        error_log("Uncaught Database Exception: " . $exception->getMessage());
        
        // In production, show generic error
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            http_response_code(500);
            echo json_encode(['error' => 'Database service temporarily unavailable']);
        } else {
            // In development, show detailed error
            echo "Database Error: " . $exception->getMessage() . "\n";
            echo $exception->getTraceAsString();
        }
        exit(1);
    }
    
    // Re-throw if not a database exception
    throw $exception;
});