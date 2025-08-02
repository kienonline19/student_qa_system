<?php
require_once __DIR__ . '/../config/database.php';

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