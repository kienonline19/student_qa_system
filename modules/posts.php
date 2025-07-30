<?php
/**
 * Posts Module - Handles all post-related database operations
 */

require_once 'config/database.php';

/**
 * Get all posts with user and module information
 * @return array Array of posts
 */
function getAllPosts() {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("
            SELECT p.*, u.username, m.module_code, m.module_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id 
            JOIN modules m ON p.module_id = m.module_id 
            ORDER BY p.created_at DESC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting posts: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single post by ID
 * @param int $postId Post ID
 * @return array|false Post data or false if not found
 */
function getPostById($postId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, m.module_code, m.module_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id 
            JOIN modules m ON p.module_id = m.module_id 
            WHERE p.post_id = ?
        ");
        $stmt->execute([$postId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting post: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a new post
 * @param string $title Post title
 * @param string $content Post content
 * @param int $userId User ID
 * @param int $moduleId Module ID
 * @param string|null $imagePath Image path (optional)
 * @return int|false New post ID or false on failure
 */
function createPost($title, $content, $userId, $moduleId, $imagePath = null) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO posts (title, content, user_id, module_id, image_path) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([$title, $content, $userId, $moduleId, $imagePath]);
        
        if ($result) {
            return $pdo->lastInsertId();
        }
        return false;
    } catch (Exception $e) {
        error_log("Error creating post: " . $e->getMessage());
        return false;
    }
}

/**
 * Update an existing post
 * @param int $postId Post ID
 * @param string $title Post title
 * @param string $content Post content
 * @param int $userId User ID
 * @param int $moduleId Module ID
 * @param string|null $imagePath Image path (optional)
 * @return bool Success status
 */
function updatePost($postId, $title, $content, $userId, $moduleId, $imagePath = null) {
    try {
        $pdo = getDbConnection();
        
        if ($imagePath !== null) {
            $stmt = $pdo->prepare("
                UPDATE posts 
                SET title = ?, content = ?, user_id = ?, module_id = ?, image_path = ? 
                WHERE post_id = ?
            ");
            $result = $stmt->execute([$title, $content, $userId, $moduleId, $imagePath, $postId]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE posts 
                SET title = ?, content = ?, user_id = ?, module_id = ? 
                WHERE post_id = ?
            ");
            $result = $stmt->execute([$title, $content, $userId, $moduleId, $postId]);
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Error updating post: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a post
 * @param int $postId Post ID
 * @return bool Success status
 */
function deletePost($postId) {
    try {
        // Get post to check for image file
        $post = getPostById($postId);
        
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM posts WHERE post_id = ?");
        $result = $stmt->execute([$postId]);
        
        // Delete associated image file if exists
        if ($result && $post && $post['image_path'] && file_exists($post['image_path'])) {
            unlink($post['image_path']);
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting post: " . $e->getMessage());
        return false;
    }
}

/**
 * Search posts by title or content
 * @param string $searchTerm Search term
 * @return array Array of matching posts
 */
function searchPosts($searchTerm) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, m.module_code, m.module_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id 
            JOIN modules m ON p.module_id = m.module_id 
            WHERE p.title LIKE ? OR p.content LIKE ?
            ORDER BY p.created_at DESC
        ");
        $searchTerm = '%' . $searchTerm . '%';
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error searching posts: " . $e->getMessage());
        return [];
    }
}
?>