<?php
/**
 * Database Configuration File
 * Contains database connection settings and PDO connection function
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_qa_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection using PDO
 * @return PDO Database connection object
 * @throws Exception if connection fails
 */
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Test database connection
 * @return bool True if connection successful
 */
function testDbConnection() {
    try {
        $pdo = getDbConnection();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>