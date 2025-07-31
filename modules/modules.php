<?php
/**
 * Modules Module - Handles all module-related database operations
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get all modules
 * @return array Array of modules
 */
function getAllModules() {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT * FROM modules ORDER BY module_code ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting modules: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single module by ID
 * @param int $moduleId Module ID
 * @return array|false Module data or false if not found
 */
function getModuleById($moduleId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM modules WHERE module_id = ?");
        $stmt->execute([$moduleId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting module: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a new module
 * @param string $moduleCode Module code
 * @param string $moduleName Module name
 * @return int|false New module ID or false on failure
 */
function createModule($moduleCode, $moduleName) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("INSERT INTO modules (module_code, module_name) VALUES (?, ?)");
        $result = $stmt->execute([$moduleCode, $moduleName]);
        
        if ($result) {
            return $pdo->lastInsertId();
        }
        return false;
    } catch (Exception $e) {
        error_log("Error creating module: " . $e->getMessage());
        return false;
    }
}

/**
 * Update an existing module
 * @param int $moduleId Module ID
 * @param string $moduleCode Module code
 * @param string $moduleName Module name
 * @return bool Success status
 */
function updateModule($moduleId, $moduleCode, $moduleName) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE modules SET module_code = ?, module_name = ? WHERE module_id = ?");
        return $stmt->execute([$moduleCode, $moduleName, $moduleId]);
    } catch (Exception $e) {
        error_log("Error updating module: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a module
 * @param int $moduleId Module ID
 * @return bool Success status
 */
function deleteModule($moduleId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM modules WHERE module_id = ?");
        return $stmt->execute([$moduleId]);
    } catch (Exception $e) {
        error_log("Error deleting module: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if module code exists
 * @param string $moduleCode Module code to check
 * @param int|null $excludeModuleId Module ID to exclude from check (for updates)
 * @return bool True if module code exists
 */
function moduleCodeExists($moduleCode, $excludeModuleId = null) {
    try {
        $pdo = getDbConnection();
        
        if ($excludeModuleId) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE module_code = ? AND module_id != ?");
            $stmt->execute([$moduleCode, $excludeModuleId]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE module_code = ?");
            $stmt->execute([$moduleCode]);
        }
        
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking module code: " . $e->getMessage());
        return false;
    }
}
?>