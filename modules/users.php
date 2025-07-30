<?php
/**
 * Users Module - Handles all user-related database operations
 */

require_once 'config/database.php';

/**
 * Get all users
 * @return array Array of users
 */
function getAllUsers() {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT * FROM users ORDER BY username ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting users: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single user by ID
 * @param int $userId User ID
 * @return array|false User data or false if not found
 */
function getUserById($userId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting user: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user by username
 * @param string $username Username
 * @return array|false User data or false if not found
 */
function getUserByUsername($username) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting user by username: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a new user
 * @param string $username Username
 * @param string $email Email address
 * @return int|false New user ID or false on failure
 */
function createUser($username, $email) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("INSERT INTO users (username, email) VALUES (?, ?)");
        $result = $stmt->execute([$username, $email]);
        
        if ($result) {
            return $pdo->lastInsertId();
        }
        return false;
    } catch (Exception $e) {
        error_log("Error creating user: " . $e->getMessage());
        return false;
    }
}

/**
 * Update an existing user
 * @param int $userId User ID
 * @param string $username Username
 * @param string $email Email address
 * @return bool Success status
 */
function updateUser($userId, $username, $email) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
        return $stmt->execute([$username, $email, $userId]);
    } catch (Exception $e) {
        error_log("Error updating user: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a user
 * @param int $userId User ID
 * @return bool Success status
 */
function deleteUser($userId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        return $stmt->execute([$userId]);
    } catch (Exception $e) {
        error_log("Error deleting user: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if username exists
 * @param string $username Username to check
 * @param int|null $excludeUserId User ID to exclude from check (for updates)
 * @return bool True if username exists
 */
function usernameExists($username, $excludeUserId = null) {
    try {
        $pdo = getDbConnection();
        
        if ($excludeUserId) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND user_id != ?");
            $stmt->execute([$username, $excludeUserId]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
        }
        
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking username: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if email exists
 * @param string $email Email to check
 * @param int|null $excludeUserId User ID to exclude from check (for updates)
 * @return bool True if email exists
 */
function emailExists($email, $excludeUserId = null) {
    try {
        $pdo = getDbConnection();
        
        if ($excludeUserId) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $excludeUserId]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }
        
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking email: " . $e->getMessage());
        return false;
    }
}
?>