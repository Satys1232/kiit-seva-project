<?php
/**
 * KIIT SEVA - User Model
 * Handles user authentication, registration, and profile management
 */

require_once 'BaseModel.php';

class User extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'is_active', 'email_verified'
    ];
    
    protected $hidden = [
        'password'
    ];

    /**
     * Create a new user
     * 
     * @param array $userData
     * @return int|false User ID or false on failure
     */
    public function createUser(array $userData): int|false
    {
        try {
            // Validate required fields
            $required = ['name', 'email', 'password', 'role'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    throw new InvalidArgumentException("Field '$field' is required");
                }
            }

            // Validate email uniqueness
            if ($this->emailExists($userData['email'])) {
                throw new InvalidArgumentException("Email already exists");
            }

            // Validate role
            $allowedRoles = ['student', 'teacher', 'staff'];
            if (!in_array($userData['role'], $allowedRoles)) {
                throw new InvalidArgumentException("Invalid role specified");
            }

            // Hash password if not already hashed
            if (!password_get_info($userData['password'])['algo']) {
                $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            }

            // Set default values
            $userData['is_active'] = $userData['is_active'] ?? true;
            $userData['email_verified'] = $userData['email_verified'] ?? false;

            // Insert user
            $userId = $this->insert($this->table, $userData);
            
            if ($userId) {
                $this->logInfo("User created successfully: ID $userId, Email: {$userData['email']}");
            }

            return $userId;

        } catch (Exception $e) {
            $this->logError("Failed to create user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticate user with email and password
     * 
     * @param string $email
     * @param string $password
     * @return array|false User data or false on failure
     */
    public function authenticateUser(string $email, string $password): array|false
    {
        try {
            // Get user by email
            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                $this->logWarning("Authentication failed: User not found for email $email");
                return false;
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->logWarning("Authentication failed: Invalid password for email $email");
                return false;
            }

            // Check if user is active
            if (!$user['is_active']) {
                $this->logWarning("Authentication failed: Inactive user for email $email");
                return false;
            }

            // Remove password from returned data
            unset($user['password']);
            
            $this->logInfo("User authenticated successfully: ID {$user['id']}, Email: $email");
            return $user;

        } catch (Exception $e) {
            $this->logError("Authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user by email
     * 
     * @param string $email
     * @return array|false
     */
    public function getUserByEmail(string $email): array|false
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
            return $this->fetchOne($sql, [$email]);
        } catch (Exception $e) {
            $this->logError("Failed to get user by email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user by ID
     * 
     * @param int $id
     * @return array|false
     */
    public function getUserById(int $id): array|false
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
            $user = $this->fetchOne($sql, [$id]);
            
            if ($user) {
                unset($user['password']); // Remove password from result
            }
            
            return $user;
        } catch (Exception $e) {
            $this->logError("Failed to get user by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exists
     * 
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
            $result = $this->fetchOne($sql, [$email]);
            return $result && $result['count'] > 0;
        } catch (Exception $e) {
            $this->logError("Failed to check email existence: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user profile
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser(int $id, array $data): bool
    {
        try {
            // Remove sensitive fields that shouldn't be updated directly
            unset($data['id'], $data['password'], $data['created_at']);
            
            if (empty($data)) {
                return true; // Nothing to update
            }

            // Validate email uniqueness if email is being updated
            if (isset($data['email'])) {
                $existingUser = $this->getUserByEmail($data['email']);
                if ($existingUser && $existingUser['id'] != $id) {
                    throw new InvalidArgumentException("Email already exists");
                }
            }

            // Validate role if being updated
            if (isset($data['role'])) {
                $allowedRoles = ['student', 'teacher', 'staff'];
                if (!in_array($data['role'], $allowedRoles)) {
                    throw new InvalidArgumentException("Invalid role specified");
                }
            }

            $result = $this->update($this->table, $data, ['id' => $id]);
            
            if ($result !== false) {
                $this->logInfo("User updated successfully: ID $id");
                return true;
            }

            return false;

        } catch (Exception $e) {
            $this->logError("Failed to update user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user password
     * 
     * @param int $id
     * @param string $newPassword
     * @param string $currentPassword
     * @return bool
     */
    public function updatePassword(int $id, string $newPassword, string $currentPassword = null): bool
    {
        try {
            // Get current user data
            $user = $this->find($this->table, $id);
            if (!$user) {
                throw new InvalidArgumentException("User not found");
            }

            // Verify current password if provided
            if ($currentPassword !== null) {
                if (!password_verify($currentPassword, $user['password'])) {
                    throw new InvalidArgumentException("Current password is incorrect");
                }
            }

            // Validate new password
            if (strlen($newPassword) < 8) {
                throw new InvalidArgumentException("Password must be at least 8 characters long");
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $result = $this->update($this->table, 
                ['password' => $hashedPassword], 
                ['id' => $id]
            );

            if ($result !== false) {
                $this->logInfo("Password updated successfully for user ID: $id");
                return true;
            }

            return false;

        } catch (Exception $e) {
            $this->logError("Failed to update password: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update last login timestamp
     * 
     * @param int $id
     * @return bool
     */
    public function updateLastLogin(int $id): bool
    {
        try {
            $result = $this->update($this->table, 
                ['last_login' => $this->getCurrentTimestamp()], 
                ['id' => $id]
            );

            return $result !== false;

        } catch (Exception $e) {
            $this->logError("Failed to update last login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activate/Deactivate user account
     * 
     * @param int $id
     * @param bool $isActive
     * @return bool
     */
    public function setUserStatus(int $id, bool $isActive): bool
    {
        try {
            $result = $this->update($this->table, 
                ['is_active' => $isActive], 
                ['id' => $id]
            );

            if ($result !== false) {
                $status = $isActive ? 'activated' : 'deactivated';
                $this->logInfo("User $status successfully: ID $id");
                return true;
            }

            return false;

        } catch (Exception $e) {
            $this->logError("Failed to update user status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify user email
     * 
     * @param int $id
     * @return bool
     */
    public function verifyEmail(int $id): bool
    {
        try {
            $result = $this->update($this->table, 
                ['email_verified' => true], 
                ['id' => $id]
            );

            if ($result !== false) {
                $this->logInfo("Email verified successfully for user ID: $id");
                return true;
            }

            return false;

        } catch (Exception $e) {
            $this->logError("Failed to verify email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get users by role
     * 
     * @param string $role
     * @param bool $activeOnly
     * @return array
     */
    public function getUsersByRole(string $role, bool $activeOnly = true): array
    {
        try {
            $sql = "SELECT id, name, email, role, phone, is_active, email_verified, last_login, created_at 
                    FROM {$this->table} WHERE role = ?";
            $params = [$role];

            if ($activeOnly) {
                $sql .= " AND is_active = ?";
                $params[] = true;
            }

            $sql .= " ORDER BY name ASC";

            return $this->fetchAll($sql, $params) ?: [];

        } catch (Exception $e) {
            $this->logError("Failed to get users by role: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search users
     * 
     * @param string $query
     * @param string $role
     * @param int $limit
     * @return array
     */
    public function searchUsers(string $query, string $role = null, int $limit = 50): array
    {
        try {
            $sql = "SELECT id, name, email, role, phone, is_active, created_at 
                    FROM {$this->table} 
                    WHERE (name LIKE ? OR email LIKE ?)";
            $params = ["%$query%", "%$query%"];

            if ($role) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }

            $sql .= " AND is_active = ? ORDER BY name ASC LIMIT ?";
            $params[] = true;
            $params[] = $limit;

            return $this->fetchAll($sql, $params) ?: [];

        } catch (Exception $e) {
            $this->logError("Failed to search users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user statistics
     * 
     * @return array
     */
    public function getUserStats(): array
    {
        try {
            $stats = [];

            // Total users by role
            $sql = "SELECT role, COUNT(*) as count FROM {$this->table} WHERE is_active = ? GROUP BY role";
            $roleStats = $this->fetchAll($sql, [true]);
            
            foreach ($roleStats as $stat) {
                $stats['by_role'][$stat['role']] = (int)$stat['count'];
            }

            // Total active users
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE is_active = ?";
            $result = $this->fetchOne($sql, [true]);
            $stats['total_active'] = (int)($result['count'] ?? 0);

            // Total users
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $result = $this->fetchOne($sql);
            $stats['total'] = (int)($result['count'] ?? 0);

            // Recent registrations (last 30 days)
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $result = $this->fetchOne($sql);
            $stats['recent_registrations'] = (int)($result['count'] ?? 0);

            // Email verification stats
            $sql = "SELECT 
                        SUM(CASE WHEN email_verified = 1 THEN 1 ELSE 0 END) as verified,
                        SUM(CASE WHEN email_verified = 0 THEN 1 ELSE 0 END) as unverified
                    FROM {$this->table} WHERE is_active = ?";
            $result = $this->fetchOne($sql, [true]);
            $stats['email_verification'] = [
                'verified' => (int)($result['verified'] ?? 0),
                'unverified' => (int)($result['unverified'] ?? 0)
            ];

            return $stats;

        } catch (Exception $e) {
            $this->logError("Failed to get user statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete user account (soft delete by deactivating)
     * 
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        try {
            // Instead of hard delete, deactivate the user
            $result = $this->setUserStatus($id, false);
            
            if ($result) {
                $this->logInfo("User soft deleted (deactivated): ID $id");
            }

            return $result;

        } catch (Exception $e) {
            $this->logError("Failed to delete user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate user data
     * 
     * @param array $data
     * @param bool $isUpdate
     * @return array Validation errors
     */
    public function validateUserData(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        // Name validation
        if (!$isUpdate || isset($data['name'])) {
            $name = $data['name'] ?? '';
            if (empty($name)) {
                $errors['name'] = 'Name is required';
            } elseif (strlen($name) < 2 || strlen($name) > 100) {
                $errors['name'] = 'Name must be between 2 and 100 characters';
            }
        }

        // Email validation
        if (!$isUpdate || isset($data['email'])) {
            $email = $data['email'] ?? '';
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!$this->validateEmail($email)) {
                $errors['email'] = 'Please enter a valid email address';
            } elseif (!$this->validateKiitEmail($email)) {
                $errors['email'] = 'Please use your KIIT university email address';
            }
        }

        // Role validation
        if (!$isUpdate || isset($data['role'])) {
            $role = $data['role'] ?? '';
            $allowedRoles = ['student', 'teacher', 'staff'];
            if (empty($role)) {
                $errors['role'] = 'Role is required';
            } elseif (!in_array($role, $allowedRoles)) {
                $errors['role'] = 'Please select a valid role';
            }
        }

        // Phone validation
        if (isset($data['phone']) && !empty($data['phone'])) {
            if (!$this->validatePhoneNumber($data['phone'])) {
                $errors['phone'] = 'Please enter a valid phone number';
            }
        }

        return $errors;
    }

    /**
     * Validate KIIT email domain
     * 
     * @param string $email
     * @return bool
     */
    private function validateKiitEmail(string $email): bool
    {
        $validDomains = ['kiit.ac.in', 'ksom.ac.in', 'kiss.ac.in', 'kims.ac.in'];
        $domain = substr(strrchr($email, "@"), 1);
        return in_array($domain, $validDomains);
    }
}