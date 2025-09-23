<?php
/**
 * KIIT SEVA - Enterprise BaseModel Class
 * Professional-grade foundation for all database operations with enterprise-level security,
 * performance optimization, and comprehensive error handling.
 *
 * @package     KIIT-SEVA
 * @subpackage  Models
 * @author      AI Generated for KIIT SEVA
 * @version     1.0.0
 * @license     Proprietary - KIIT University
 */

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

/**
 * BaseModel Class
 * 
 * Serves as the foundation for all database operations with enterprise-level security,
 * performance optimization, and comprehensive error handling.
 */
class BaseModel
{
    /** @var PDO|null Database connection instance */
    protected static ?PDO $connection = null;
    
    /** @var array Cache storage for query results */
    protected static array $queryCache = [];
    
    /** @var array Prepared statement cache */
    protected static array $statementCache = [];
    
    /** @var bool Debug mode flag */
    protected bool $debugMode = false;
    
    /** @var string Log file path */
    protected string $logFile = '';
    
    /** @var array Query statistics for performance monitoring */
    protected array $queryStats = [];
    
    /** @var string Current environment (development, staging, production) */
    protected string $environment = '';
    
    /** @var array Protected fields that cannot be mass-assigned */
    protected array $protectedFields = ['id', 'created_at', 'updated_at', 'deleted_at'];
    
    /** @var string Primary key field name */
    protected string $primaryKey = 'id';
    
    /** 
     * @var string Table name for the model 
     * @phpstan-ignore-next-line
     */
    protected string $table = '';
    
    /** 
     * @var array Field data types for validation 
     * @phpstan-ignore-next-line
     */
    protected array $fieldTypes = [];
    
    /** 
     * @var array Required fields for validation 
     * @phpstan-ignore-next-line
     */
    protected array $requiredFields = [];
    
    /** @var array Current transaction savepoints */
    protected array $savepoints = [];
    
    /** @var int Maximum retry attempts for lost connections */
    protected int $maxRetryAttempts = 3;
    
    /** @var array Valid KIIT email domains */
    protected array $validKiitDomains = ['kiit.ac.in', 'ksom.ac.in', 'kiss.ac.in', 'kims.ac.in'];
    
    /** @var array Valid KIIT campus codes */
    protected array $validCampusCodes = ['15', '17', '25', '7', '5', '3'];
    
    /**
     * Constructor
     * 
     * Initializes the BaseModel with configuration settings
     */
    public function __construct()
    {
        $this->initializeModel();
    }
    
    /**
     * Initialize model settings and configuration
     * 
     * @return void
     */
    protected function initializeModel(): void
    {
        try {
            // Set up logging
            $this->logFile = dirname(__DIR__, 2) . '/storage/logs/database.log';
            
            // Detect environment
            $this->detectEnvironment();
            
            // Set debug mode based on environment
            $this->debugMode = $this->environment === 'development';
            
            // Connect to database if not already connected
            $this->getConnection();
            
            // Set default timezone to Asia/Kolkata for KIIT
            date_default_timezone_set('Asia/Kolkata');
            
        } catch (Exception $e) {
            $this->logError('Model initialization failed: ' . $e->getMessage());
            
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
        $envFile = dirname(__DIR__, 2) . '/.env';
        
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
            $this->environment = $env['APP_ENV'] ?? 'development';
        } else {
            $this->environment = 'development';
        }
    }
    
    /**
     * Get database connection using singleton pattern
     * 
     * @return PDO Database connection
     * @throws PDOException If connection fails after retry attempts
     */
    public function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                // Get database configuration from KiitSevaDatabase
                require_once dirname(__DIR__) . '/config/database.php';
                $dbConfig = \KiitSevaDatabase::getInstance();
                
                // Get connection from database configuration
                self::$connection = $dbConfig->connect();
                
                // Configure connection settings
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                
                // Log successful connection
                $this->logInfo('Database connection established successfully');
                
            } catch (PDOException $e) {
                $this->logError('Database connection failed: ' . $e->getMessage());
                
                // Attempt reconnection
                $this->reconnect($e);
            }
        }
        
        // Check connection health
        $this->checkConnectionHealth();
        
        return self::$connection;
    }
    
    /**
     * Reconnect to database after connection failure
     * 
     * @param PDOException $exception Original connection exception
     * @return void
     * @throws PDOException If reconnection fails after retry attempts
     */
    protected function reconnect(PDOException $exception): void
    {
        $attempts = 0;
        
        while ($attempts < $this->maxRetryAttempts) {
            try {
                $attempts++;
                $this->logWarning("Attempting database reconnection (attempt {$attempts}/{$this->maxRetryAttempts})");
                
                // Wait before retry with exponential backoff
                $backoff = pow(2, $attempts) * 100000; // microseconds
                usleep($backoff);
                
                // Get database configuration from KiitSevaDatabase
                require_once dirname(__DIR__) . '/config/database.php';
                $dbConfig = \KiitSevaDatabase::getInstance();
                
                // Get fresh connection
                self::$connection = $dbConfig->connect(true);
                
                // Configure connection settings
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                
                $this->logInfo('Database reconnection successful');
                return;
                
            } catch (PDOException $e) {
                $this->logError("Reconnection attempt {$attempts} failed: " . $e->getMessage());
                
                // If we've reached max attempts, throw the exception
                if ($attempts >= $this->maxRetryAttempts) {
                    throw new PDOException(
                        "Failed to connect to database after {$this->maxRetryAttempts} attempts: " . $e->getMessage(),
                        (int)$e->getCode(),
                        $e
                    );
                }
            }
        }
    }
    
    /**
     * Check database connection health
     * 
     * @return bool True if connection is healthy
     */
    public function checkConnectionHealth(): bool
    {
        if (self::$connection === null) {
            return false;
        }
        
        try {
            // Simple query to check connection
            $stmt = self::$connection->query('SELECT 1');
            $stmt->fetch();
            return true;
        } catch (PDOException $e) {
            $this->logWarning('Connection health check failed: ' . $e->getMessage());
            
            // Try to reconnect
            try {
                $this->reconnect($e);
                return true;
            } catch (PDOException $reconnectException) {
                $this->logError('Failed to restore database connection: ' . $reconnectException->getMessage());
                return false;
            }
        }
    }
    
    /**
     * Begin a database transaction
     * 
     * @return bool True if transaction started successfully
     */
    public function beginTransaction(): bool
    {
        try {
            $connection = $this->getConnection();
            
            // Only start a transaction if one is not already active
            if (!$connection->inTransaction()) {
                $result = $connection->beginTransaction();
                $this->logInfo('Transaction started');
                return $result;
            }
            
            // Create a savepoint if transaction already active
            $savepoint = 'sp_' . uniqid();
            $connection->exec("SAVEPOINT {$savepoint}");
            $this->savepoints[] = $savepoint;
            $this->logInfo("Savepoint created: {$savepoint}");
            
            return true;
        } catch (PDOException $e) {
            $this->logError('Failed to start transaction: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Commit the current transaction
     * 
     * @return bool True if commit was successful
     */
    public function commit(): bool
    {
        try {
            $connection = $this->getConnection();
            
            // If we have savepoints, release the most recent one
            if (!empty($this->savepoints)) {
                $savepoint = array_pop($this->savepoints);
                $connection->exec("RELEASE SAVEPOINT {$savepoint}");
                $this->logInfo("Savepoint released: {$savepoint}");
                return true;
            }
            
            // Otherwise commit the transaction
            if ($connection->inTransaction()) {
                $result = $connection->commit();
                $this->logInfo('Transaction committed');
                return $result;
            }
            
            return false;
        } catch (PDOException $e) {
            $this->logError('Failed to commit transaction: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rollback the current transaction
     * 
     * @return bool True if rollback was successful
     */
    public function rollback(): bool
    {
        try {
            $connection = $this->getConnection();
            
            // If we have savepoints, rollback to the most recent one
            if (!empty($this->savepoints)) {
                $savepoint = array_pop($this->savepoints);
                $connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
                $this->logInfo("Rolled back to savepoint: {$savepoint}");
                return true;
            }
            
            // Otherwise rollback the entire transaction
            if ($connection->inTransaction()) {
                $result = $connection->rollBack();
                $this->logInfo('Transaction rolled back');
                return $result;
            }
            
            return false;
        } catch (PDOException $e) {
            $this->logError('Failed to rollback transaction: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the ID of the last inserted record
     * 
     * @return string|false Last insert ID or false on failure
     */
    public function lastInsertId(): string|false
    {
        try {
            return $this->getConnection()->lastInsertId();
        } catch (PDOException $e) {
            $this->logError('Failed to get last insert ID: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a custom SQL query with parameters
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @return \PDOStatement|false PDOStatement object or false on failure
     */
    public function query(string $sql, array $params = []): \PDOStatement|false
    {
        $startTime = microtime(true);
        $cacheKey = $this->generateCacheKey($sql, $params);
        
        try {
            $connection = $this->getConnection();
            
            // Check statement cache
            if (isset(self::$statementCache[$cacheKey])) {
                $stmt = self::$statementCache[$cacheKey];
            } else {
                $stmt = $connection->prepare($sql);
                self::$statementCache[$cacheKey] = $stmt;
            }
            
            // Bind parameters with explicit data types
            foreach ($params as $param => $value) {
                $type = $this->getPdoDataType($value);
                
                if (is_int($param)) {
                    $stmt->bindValue($param + 1, $value, $type);
                } else {
                    $stmt->bindValue($param, $value, $type);
                }
            }
            
            // Execute the query
            $stmt->execute();
            
            // Log query performance
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            $this->logQueryPerformance($sql, $params, $executionTime);
            
            return $stmt;
        } catch (PDOException $e) {
            $this->logError('Query execution failed: ' . $e->getMessage() . ' SQL: ' . $sql);
            
            if ($this->debugMode) {
                throw $e;
            }
            
            return false;
        }
    }
    
    /**
     * Fetch all records from a query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @param int|null $ttl Cache time-to-live in seconds (null for no caching)
     * @return array|false Array of records or false on failure
     */
    public function fetchAll(string $sql, array $params = [], ?int $ttl = null): array|false
    {
        $cacheKey = $this->generateCacheKey($sql, $params);
        
        // Check cache if TTL is provided
        if ($ttl !== null && isset(self::$queryCache[$cacheKey])) {
            $cache = self::$queryCache[$cacheKey];
            
            // Return cached result if not expired
            if ($cache['expires'] > time()) {
                $this->logInfo('Query result served from cache');
                return $cache['data'];
            }
            
            // Remove expired cache entry
            unset(self::$queryCache[$cacheKey]);
        }
        
        // Execute query
        $stmt = $this->query($sql, $params);
        
        if ($stmt === false) {
            return false;
        }
        
        // Fetch results
        $results = $stmt->fetchAll();
        
        // Store in cache if TTL is provided
        if ($ttl !== null) {
            self::$queryCache[$cacheKey] = [
                'data' => $results,
                'expires' => time() + $ttl
            ];
        }
        
        return $results;
    }
    
    /**
     * Fetch a single record from a query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @param int|null $ttl Cache time-to-live in seconds (null for no caching)
     * @return array|false Record array or false if not found or on failure
     */
    public function fetchOne(string $sql, array $params = [], ?int $ttl = null): array|false
    {
        $cacheKey = $this->generateCacheKey($sql, $params);
        
        // Check cache if TTL is provided
        if ($ttl !== null && isset(self::$queryCache[$cacheKey])) {
            $cache = self::$queryCache[$cacheKey];
            
            // Return cached result if not expired
            if ($cache['expires'] > time()) {
                $this->logInfo('Query result served from cache');
                return $cache['data'];
            }
            
            // Remove expired cache entry
            unset(self::$queryCache[$cacheKey]);
        }
        
        // Execute query
        $stmt = $this->query($sql, $params);
        
        if ($stmt === false) {
            return false;
        }
        
        // Fetch result
        $result = $stmt->fetch();
        
        // Return false if no record found
        if ($result === false) {
            return false;
        }
        
        // Store in cache if TTL is provided
        if ($ttl !== null) {
            self::$queryCache[$cacheKey] = [
                'data' => $result,
                'expires' => time() + $ttl
            ];
        }
        
        return $result;
    }
    
    /**
     * Execute a non-SELECT query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @return int|false Number of affected rows or false on failure
     */
    public function execute(string $sql, array $params = []): int|false
    {
        $stmt = $this->query($sql, $params);
        
        if ($stmt === false) {
            return false;
        }
        
        return $stmt->rowCount();
    }
    
    /**
     * Insert a record into a table
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value pairs
     * @return int|false Last insert ID or false on failure
     */
    public function insert(string $table, array $data): int|false
    {
        try {
            // Sanitize and validate input data
            $data = $this->sanitizeInput($data);
            
            // Remove protected fields
            $data = $this->removeProtectedFields($data);
            
            // Add timestamps
            $data['created_at'] = $this->getCurrentTimestamp();
            $data['updated_at'] = $this->getCurrentTimestamp();
            
            // Build query
            $columns = array_keys($data);
            $placeholders = array_map(fn($col) => ":{$col}", $columns);
            
            $sql = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $this->escapeIdentifier($table),
                implode(', ', array_map([$this, 'escapeIdentifier'], $columns)),
                implode(', ', $placeholders)
            );
            
            // Execute query
            $stmt = $this->query($sql, $data);
            
            if ($stmt === false) {
                return false;
            }
            
            // Get last insert ID
            $lastId = $this->lastInsertId();
            
            // Clear cache for this table
            $this->clearTableCache($table);
            
            return $lastId !== false ? (int)$lastId : false;
        } catch (Exception $e) {
            $this->logError('Insert operation failed: ' . $e->getMessage());
            
            if ($this->debugMode) {
                throw $e;
            }
            
            return false;
        }
    }
    
    /**
     * Update records in a table
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value pairs
     * @param array $conditions Associative array of column => value pairs for WHERE clause
     * @return int|false Number of affected rows or false on failure
     */
    public function update(string $table, array $data, array $conditions): int|false
    {
        try {
            // Sanitize and validate input data
            $data = $this->sanitizeInput($data);
            
            // Remove protected fields
            $data = $this->removeProtectedFields($data);
            
            // Add updated_at timestamp
            $data['updated_at'] = $this->getCurrentTimestamp();
            
            // Build SET clause
            $setClauses = [];
            $params = [];
            
            foreach ($data as $column => $value) {
                $paramName = "set_{$column}";
                $setClauses[] = $this->escapeIdentifier($column) . " = :{$paramName}";
                $params[$paramName] = $value;
            }
            
            // Build WHERE clause
            list($whereClause, $whereParams) = $this->buildWhereClause($conditions);
            $params = array_merge($params, $whereParams);
            
            // Build query
            $sql = sprintf(
                "UPDATE %s SET %s WHERE %s",
                $this->escapeIdentifier($table),
                implode(', ', $setClauses),
                $whereClause
            );
            
            // Execute query
            $stmt = $this->query($sql, $params);
            
            if ($stmt === false) {
                return false;
            }
            
            // Clear cache for this table
            $this->clearTableCache($table);
            
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->logError('Update operation failed: ' . $e->getMessage());
            
            if ($this->debugMode) {
                throw $e;
            }
            
            return false;
        }
    }
    
    /**
     * Delete records from a table
     * 
     * @param string $table Table name
     * @param array $conditions Associative array of column => value pairs for WHERE clause
     * @return int|false Number of affected rows or false on failure
     */
    public function delete(string $table, array $conditions): int|false
    {
        try {
            // Validate conditions to prevent accidental deletion of all records
            if (empty($conditions)) {
                throw new InvalidArgumentException('Delete operation requires conditions');
            }
            
            // Build WHERE clause
            list($whereClause, $params) = $this->buildWhereClause($conditions);
            
            // Build query
            $sql = sprintf(
                "DELETE FROM %s WHERE %s",
                $this->escapeIdentifier($table),
                $whereClause
            );
            
            // Execute query
            $stmt = $this->query($sql, $params);
            
            if ($stmt === false) {
                return false;
            }
            
            // Clear cache for this table
            $this->clearTableCache($table);
            
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->logError('Delete operation failed: ' . $e->getMessage());
            
            if ($this->debugMode) {
                throw $e;
            }
            
            return false;
        }
    }
    
    /**
     * Find a record by primary key
     * 
     * @param string $table Table name
     * @param int|string $id Primary key value
     * @param int|null $ttl Cache time-to-live in seconds (null for no caching)
     * @return array|false Record array or false if not found
     */
    public function find(string $table, int|string $id, ?int $ttl = null): array|false
    {
        return $this->findWhere($table, [$this->primaryKey => $id], 1, $ttl);
    }
    
    /**
     * Find records based on conditions
     * 
     * @param string $table Table name
     * @param array $conditions Associative array of column => value pairs for WHERE clause
     * @param int|null $limit Maximum number of records to return (null for no limit)
     * @param int|null $ttl Cache time-to-live in seconds (null for no caching)
     * @return array|false Array of records or false on failure
     */
    public function findWhere(string $table, array $conditions, ?int $limit = null, ?int $ttl = null): array|false
    {
        try {
            // Build WHERE clause
            list($whereClause, $params) = $this->buildWhereClause($conditions);
            
            // Build query
            $sql = sprintf(
                "SELECT * FROM %s WHERE %s",
                $this->escapeIdentifier($table),
                $whereClause
            );
            
            // Add limit if specified
            if ($limit !== null) {
                $sql .= " LIMIT " . (int)$limit;
            }
            
            // Execute query
            if ($limit === 1) {
                return $this->fetchOne($sql, $params, $ttl);
            } else {
                return $this->fetchAll($sql, $params, $ttl);
            }
        } catch (Exception $e) {
            $this->logError('Find operation failed: ' . $e->getMessage());
            
            if ($this->debugMode) {
                throw $e;
            }
            
            return false;
        }
    }
    
    /**
     * Find all records in a table
     * 
     * @param string $table Table name
     * @param string|null $orderBy Column to order by (with optional direction, e.g. "name ASC")
     * @param int|null $limit Maximum number of records to return (null for no limit)
     * @param int|null $ttl Cache time-to-live in seconds (null for no caching)
     * @return array|false Array of records or false on failure
     */
    public function findAll(string $table, ?string $orderBy = null, ?int $limit = null, ?int $ttl = null): array|false
    {
        try {
            // Build query
            $sql = sprintf("SELECT * FROM %s", $this->escapeIdentifier($table));
            
            // Add ORDER BY if specified
            if ($orderBy !== null) {
                $sql .= " ORDER BY " . $orderBy;
            }
            
            // Add LIMIT if specified
            if ($limit !== null) {
                $sql .= " LIMIT " . (int)$limit;
            }
            
            // Execute query
            return $this->fetchAll($sql, [], $ttl);
        } catch (Exception $e) {
            $this->logError('FindAll operation failed: ' . $e->getMessage());
            
            if ($this->debugMode) {
                throw $e;
            }
            
            return false;
        }
    }
    
    /**
     * Check if a record exists
     * 
     * @param string $table Table name
     * @param array $conditions Associative array of column => value pairs for WHERE clause
     * @return bool True if record exists, false otherwise
     */
    public function exists(string $table, array $conditions): bool
    {
        try {
            // Build WHERE clause
            list($whereClause, $params) = $this->buildWhereClause($conditions);
            
            // Build query
            $sql = sprintf(
                "SELECT 1 FROM %s WHERE %s LIMIT 1",
                $this->escapeIdentifier($table),
                $whereClause
            );
            
            // Execute query
            $result = $this->fetchOne($sql, $params);
            
            return $result !== false;
        } catch (Exception $e) {
            $this->logError('Exists operation failed: ' . $e->getMessage());
            
            if ($this->debugMode) {
                throw $e;
            }
            
            return false;
        }
    }
    
    /**
     * Count records in a table
     * 
     * @param string $table Table name
     * @param array $conditions Associative array of column => value pairs for WHERE clause
     * @param int|null $ttl Cache time-to-live in seconds (null for no caching)
     * @return int|false Number of records or false on failure
     */
    public function count(string $table, array $conditions = [], ?int $ttl = null): int|false
    {
        try {
            // Build query
            $sql = sprintf("SELECT COUNT(*) as count FROM %s", $this->escapeIdentifier($table));
            
            // Add WHERE clause if conditions are specified
            $params = [];
            
            if (!empty($conditions)) {
                list($whereClause, $params) = $this->buildWhereClause($conditions);
                $sql .= " WHERE {$whereClause}";
            }
            
            // Execute query
            $result = $this->fetchOne($sql, $params, $ttl);
            
            if ($result === false) {
                return false;
            }
            
            return (int)$result['count'];
        } catch (Exception $e) {
            $this->logError('Count operation failed: ' . $e->getMessage());
            
            if ($this->debugMode) {
                throw $e;
            }
            
            return false;
        }
    }
    
    /**
     * Perform bulk insert operation
     * 
     * @param string $table Table name
     * @param array $records Array of records to insert
     * @return int|false Number of inserted records or false on failure
     */
    public function bulkInsert(string $table, array $records): int|false
    {
        if (empty($records)) {
            return 0;
        }
        
        try {
            // Begin transaction
            $this->beginTransaction();
            
            $insertedCount = 0;
            
            foreach ($records as $data) {
                $result = $this->insert($table, $data);
                
                if ($result !== false) {
                    $insertedCount++;
                }
            }
            
            // Commit transaction
            $this->commit();
            
            // Clear cache for this table
            $this->clearTableCache($table);
            
            return $insertedCount;
        } catch (Exception $e) {
            // Rollback transaction
            $this->rollback();
            
            $this->logError('Bulk insert operation failed: ' . $e->getMessage());
            
            if ($this->debugMode) {
                throw $e;
            }
            
            return false;
        }
    }
    
    /**
     * Build WHERE clause from conditions
     * 
     * @param array $conditions Associative array of column => value pairs
     * @return array Array containing [whereClause, params]
     */
    protected function buildWhereClause(array $conditions): array
    {
        if (empty($conditions)) {
            return ['1=1', []];
        }
        
        $clauses = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $paramName = "where_{$column}";
            
            if (is_null($value)) {
                $clauses[] = $this->escapeIdentifier($column) . " IS NULL";
            } elseif (is_array($value)) {
                // Handle IN clause
                $placeholders = [];
                
                foreach ($value as $i => $item) {
                    $itemParamName = "{$paramName}_{$i}";
                    $placeholders[] = ":{$itemParamName}";
                    $params[$itemParamName] = $item;
                }
                
                $clauses[] = $this->escapeIdentifier($column) . " IN (" . implode(', ', $placeholders) . ")";
            } else {
                $clauses[] = $this->escapeIdentifier($column) . " = :{$paramName}";
                $params[$paramName] = $value;
            }
        }
        
        return [implode(' AND ', $clauses), $params];
    }
    
    /**
     * Sanitize input data
     * 
     * @param array $input Input data
     * @return array Sanitized data
     */
    public function sanitizeInput(array $input): array
    {
        $sanitized = [];
        
        foreach ($input as $key => $value) {
            // Skip null values
            if ($value === null) {
                $sanitized[$key] = null;
                continue;
            }
            
            // Sanitize based on data type
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize a string value
     * 
     * @param string $string Input string
     * @return string Sanitized string
     */
    public function sanitizeString(string $string): string
    {
        // Trim whitespace
        $string = trim($string);
        
        // Remove null bytes
        $string = str_replace("\0", '', $string);
        
        return $string;
    }
    
    /**
     * Strip HTML and PHP tags from a string
     * 
     * @param string $input Input string
     * @return string String with tags removed
     */
    public function stripTags(string $input): string
    {
        return strip_tags($input);
    }
    
    /**
     * Encode output for safe display
     * 
     * @param mixed $data Data to encode
     * @return mixed Encoded data
     */
    public function encodeOutput(mixed $data): mixed
    {
        if (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->encodeOutput($value);
            }
        }
        
        return $data;
    }
    
    /**
     * Validate email format and KIIT domain
     * 
     * @param string $email Email address to validate
     * @return bool True if valid, false otherwise
     */
    public function validateEmail(string $email): bool
    {
        // Basic email format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Extract domain
        $parts = explode('@', $email);
        $domain = $parts[1] ?? '';
        
        // Check if domain is a valid KIIT domain
        return in_array($domain, $this->validKiitDomains);
    }
    
    /**
     * Validate Indian phone number format
     * 
     * @param string $phone Phone number to validate
     * @return bool True if valid, false otherwise
     */
    public function validatePhoneNumber(string $phone): bool
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid Indian phone number (10 digits, optionally prefixed with +91 or 0)
        return preg_match('/^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[6789]\d{9}$/', $phone) === 1;
    }
    
    /**
     * Validate required fields
     * 
     * @param array $fields Required field names
     * @param array $data Data to validate
     * @return bool True if all required fields are present and not empty
     */
    public function validateRequired(array $fields, array $data): bool
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate data types according to schema
     * 
     * @param array $schema Schema defining field types
     * @param array $data Data to validate
     * @return bool True if all fields match their expected types
     */
    public function validateDataTypes(array $schema, array $data): bool
    {
        foreach ($schema as $field => $type) {
            if (!isset($data[$field])) {
                continue;
            }
            
            $value = $data[$field];
            
            switch ($type) {
                case 'int':
                case 'integer':
                    if (!is_int($value) && !ctype_digit($value)) {
                        return false;
                    }
                    break;
                    
                case 'float':
                case 'double':
                    if (!is_float($value) && !is_numeric($value)) {
                        return false;
                    }
                    break;
                    
                case 'bool':
                case 'boolean':
                    if (!is_bool($value) && $value !== '0' && $value !== '1' && $value !== 0 && $value !== 1) {
                        return false;
                    }
                    break;
                    
                case 'string':
                    if (!is_string($value)) {
                        return false;
                    }
                    break;
                    
                case 'array':
                    if (!is_array($value)) {
                        return false;
                    }
                    break;
                    
                case 'email':
                    if (!$this->validateEmail($value)) {
                        return false;
                    }
                    break;
                    
                case 'phone':
                    if (!$this->validatePhoneNumber($value)) {
                        return false;
                    }
                    break;
                    
                case 'date':
                    if (!$this->validateDate($value)) {
                        return false;
                    }
                    break;
            }
        }
        
        return true;
    }
    
    /**
     * Validate date format
     * 
     * @param string $date Date string to validate
     * @param string $format Expected date format
     * @return bool True if valid, false otherwise
     */
    public function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validate KIIT student ID format
     * 
     * @param string $studentId Student ID to validate
     * @return bool True if valid, false otherwise
     */
    public function validateStudentId(string $studentId): bool
    {
        // KIIT student IDs are typically in format: 2105XXXX
        return preg_match('/^[1-2][0-9]{3}[0-9]{4}$/', $studentId) === 1;
    }
    
    /**
     * Validate KIIT campus code
     * 
     * @param string $campusCode Campus code to validate
     * @return bool True if valid, false otherwise
     */
    public function validateCampusCode(string $campusCode): bool
    {
        return in_array($campusCode, $this->validCampusCodes);
    }
    
    /**
     * Get current timestamp in MySQL format
     * 
     * @return string Current timestamp
     */
    protected function getCurrentTimestamp(): string
    {
        $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        return $dateTime->format('Y-m-d H:i:s');
    }
    
    /**
     * Remove protected fields from data
     * 
     * @param array $data Input data
     * @return array Data with protected fields removed
     */
    protected function removeProtectedFields(array $data): array
    {
        foreach ($this->protectedFields as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }
    
    /**
     * Escape SQL identifier (table or column name)
     * 
     * @param string $identifier Identifier to escape
     * @return string Escaped identifier
     */
    protected function escapeIdentifier(string $identifier): string
    {
        // Remove any backticks
        $identifier = str_replace('`', '', $identifier);
        
        // Escape the identifier
        return '`' . $identifier . '`';
    }
    
    /**
     * Generate cache key for a query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return string Cache key
     */
    protected function generateCacheKey(string $sql, array $params): string
    {
        return md5($sql . serialize($params));
    }
    
    /**
     * Clear cache entries for a specific table
     * 
     * @param string $table Table name
     * @return void
     */
    protected function clearTableCache(string $table): void
    {
        foreach (self::$queryCache as $key => $value) {
            if (strpos($key, $table) !== false) {
                unset(self::$queryCache[$key]);
            }
        }
    }
    
    /**
     * Get PDO data type for a value
     * 
     * @param mixed $value Value to check
     * @return int PDO data type constant
     */
    protected function getPdoDataType(mixed $value): int
    {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            return PDO::PARAM_NULL;
        } else {
            return PDO::PARAM_STR;
        }
    }
    
    /**
     * Log query performance
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @param float $executionTime Execution time in seconds
     * @return void
     */
    protected function logQueryPerformance(string $sql, array $params, float $executionTime): void
    {
        $this->queryStats[] = [
            'sql' => $sql,
            'params' => $params,
            'execution_time' => $executionTime,
            'timestamp' => $this->getCurrentTimestamp()
        ];
        
        // Log slow queries
        if ($executionTime > 1.0) {
            $this->logWarning("Slow query detected ({$executionTime}s): {$sql}");
        }
    }
    
    /**
     * Log an informational message
     * 
     * @param string $message Message to log
     * @return void
     */
    protected function logInfo(string $message): void
    {
        $this->logMessage('INFO', $message);
    }
    
    /**
     * Log a warning message
     * 
     * @param string $message Message to log
     * @return void
     */
    protected function logWarning(string $message): void
    {
        $this->logMessage('WARNING', $message);
    }
    
    /**
     * Log an error message
     * 
     * @param string $message Message to log
     * @return void
     */
    protected function logError(string $message): void
    {
        $this->logMessage('ERROR', $message);
    }
    
    /**
     * Log a message to the database log file
     * 
     * @param string $level Log level
     * @param string $message Message to log
     * @return void
     */
    protected function logMessage(string $level, string $message): void
    {
        if (empty($this->logFile)) {
            return;
        }
        
        $timestamp = $this->getCurrentTimestamp();
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        // Create log directory if it doesn't exist
        $logDir = dirname($this->logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Append to log file
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Get query statistics
     * 
     * @return array Query statistics
     */
    public function getQueryStats(): array
    {
        return $this->queryStats;
    }
    
    /**
     * Get database connection status
     * 
     * @return array Connection status information
     */
    public function getConnectionStatus(): array
    {
        return [
            'connected' => self::$connection !== null,
            'healthy' => $this->checkConnectionHealth(),
            'environment' => $this->environment,
            'debug_mode' => $this->debugMode,
            'query_count' => count($this->queryStats),
            'cache_entries' => count(self::$queryCache),
            'statement_cache_entries' => count(self::$statementCache)
        ];
    }
    
    /**
     * Clear query cache
     * 
     * @return void
     */
    public function clearCache(): void
    {
        self::$queryCache = [];
    }
    
    /**
     * Clear prepared statement cache
     * 
     * @return void
     */
    public function clearStatementCache(): void
    {
        self::$statementCache = [];
    }
    
    /**
     * Set debug mode
     * 
     * @param bool $mode Debug mode flag
     * @return void
     */
    public function setDebugMode(bool $mode): void
    {
        $this->debugMode = $mode;
    }
    
    /**
     * Get debug mode
     * 
     * @return bool Debug mode flag
     */
    public function getDebugMode(): bool
    {
        return $this->debugMode;
    }
}