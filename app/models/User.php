<?php
/**
 * KIIT SEVA - User Model
 * Handles user data operations and authentication
 */

require_once dirname(__DIR__) . '/models/BaseModel.php';

class User extends BaseModel {
    protected string $table = 'users';
    protected array $requiredFields = ['name', 'email', 'password', 'role'];
    
    /**
     * Create a new user
     */
    public function createUser(array $data): int|false {
        // Validate required fields
        if (!$this->validateRequired($this->requiredFields, $data)) {
            return false;
        }
        
        // Validate email uniqueness
        if ($this->getUserByEmail($data['email'])) {
            return false;
        }
        
        // Validate role
        if (!in_array($data['role'], ['student', 'teacher', 'staff'])) {
            return false;
        }
        
        return $this->insert($this->table, $data);
    }
    
    /**
     * Authenticate user with email and password
     */
    public function authenticateUser(string $email, string $password): array|false {
        $user = $this->getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->updateLastLogin($user['id']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): array|false {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }
    
    /**
     * Get user by ID
     */
    public function getUserById(int $id): array|false {
        return $this->find($this->table, $id);
    }
    
    /**
     * Update user information
     */
    public function updateUser(int $id, array $data): int|false {
        // Remove password from update if empty
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } else if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->update($this->table, $data, ['id' => $id]);
    }
    
    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $userId): bool {
        try {
            $stmt = $this->query(
                "UPDATE {$this->table} SET last_login = NOW() WHERE id = ?",
                [$userId]
            );
            return $stmt !== false;
        } catch (Exception $e) {
            $this->logError('Failed to update last login: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users with optional role filter
     */
    public function getUsers(string $role = null): array|false {
        if ($role) {
            return $this->findWhere($this->table, ['role' => $role]);
        }
        
        return $this->findAll($this->table, 'name ASC');
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole(string $role): array|false {
        return $this->findWhere($this->table, ['role' => $role]);
    }
    
    /**
     * Check if user exists
     */
    public function userExists(int $id): bool {
        return $this->exists($this->table, ['id' => $id]);
    }
    
    /**
     * Delete user (soft delete by setting inactive)
     */
    public function deleteUser(int $id): bool {
        return $this->update($this->table, ['is_active' => 0], ['id' => $id]) !== false;
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats(): array {
        try {
            $stats = [];
            
            // Total users
            $stats['total'] = $this->count($this->table);
            
            // Users by role
            $stats['students'] = $this->count($this->table, ['role' => 'student']);
            $stats['teachers'] = $this->count($this->table, ['role' => 'teacher']);
            $stats['staff'] = $this->count($this->table, ['role' => 'staff']);
            
            // Recent registrations (last 30 days)
            $stats['recent'] = $this->fetchOne(
                "SELECT COUNT(*) as count FROM {$this->table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            )['count'] ?? 0;
            
            return $stats;
        } catch (Exception $e) {
            $this->logError('Failed to get user stats: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search users by name or email
     */
    public function searchUsers(string $query, string $role = null): array|false {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE (name LIKE ? OR email LIKE ?)";
            $params = ["%{$query}%", "%{$query}%"];
            
            if ($role) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }
            
            $sql .= " ORDER BY name ASC";
            
            return $this->fetchAll($sql, $params);
        } catch (Exception $e) {
            $this->logError('Failed to search users: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Change user password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool {
        $user = $this->getUserById($userId);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }
        
        if (!validatePassword($newPassword)) {
            return false;
        }
        
        return $this->update(
            $this->table,
            ['password' => password_hash($newPassword, PASSWORD_DEFAULT)],
            ['id' => $userId]
        ) !== false;
    }
    
    /**
     * Get user profile with additional information
     */
    public function getUserProfile(int $userId): array|false {
        try {
            $user = $this->getUserById($userId);
            
            if (!$user) {
                return false;
            }
            
            // Add role-specific information
            switch ($user['role']) {
                case 'teacher':
                    $teacher = $this->fetchOne(
                        "SELECT * FROM teachers WHERE user_id = ?",
                        [$userId]
                    );
                    if ($teacher) {
                        $user['teacher_info'] = $teacher;
                    }
                    break;
                    
                case 'student':
                    // Get booking count
                    $bookingCount = $this->fetchOne(
                        "SELECT COUNT(*) as count FROM bookings WHERE student_id = ?",
                        [$userId]
                    );
                    $user['booking_count'] = $bookingCount['count'] ?? 0;
                    break;
                    
                case 'staff':
                    // Get assigned vehicle
                    $vehicle = $this->fetchOne(
                        "SELECT * FROM vehicles WHERE driver_id = ?",
                        [$userId]
                    );
                    if ($vehicle) {
                        $user['vehicle_info'] = $vehicle;
                    }
                    break;
            }
            
            return $user;
        } catch (Exception $e) {
            $this->logError('Failed to get user profile: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(int $userId, array $data): bool {
        // Only allow certain fields to be updated
        $allowedFields = ['name', 'email', 'phone'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        // Validate email uniqueness if email is being updated
        if (isset($updateData['email'])) {
            $existingUser = $this->getUserByEmail($updateData['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                return false;
            }
        }
        
        return $this->update($this->table, $updateData, ['id' => $userId]) !== false;
    }
}
?>