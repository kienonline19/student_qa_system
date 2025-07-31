<?php
/**
 * Posts Module - Handles all post-related database operations
 * Student Q&A System - COMP1841 Coursework
 */

require_once __DIR__ . '/../config/database.php';

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
 * Search posts by title or content (simple search)
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

/**
 * Advanced search posts with multiple criteria
 * @param string $searchTerm Search term
 * @param int $moduleId Module ID filter
 * @param int $userId User ID filter
 * @param string $sortBy Sort order
 * @return array Array of matching posts
 */
function advancedSearchPosts($searchTerm = '', $moduleId = 0, $userId = 0, $sortBy = 'newest') {
    try {
        $pdo = getDbConnection();
        
        // Build query
        $sql = "
            SELECT p.*, u.username, m.module_code, m.module_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id 
            JOIN modules m ON p.module_id = m.module_id 
            WHERE 1=1
        ";
        
        $params = [];
        
        // Add search term condition
        if (!empty($searchTerm)) {
            $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
            $searchPattern = '%' . $searchTerm . '%';
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }
        
        // Add module filter
        if ($moduleId > 0) {
            $sql .= " AND p.module_id = ?";
            $params[] = $moduleId;
        }
        
        // Add user filter
        if ($userId > 0) {
            $sql .= " AND p.user_id = ?";
            $params[] = $userId;
        }
        
        // Add sorting
        switch ($sortBy) {
            case 'oldest':
                $sql .= " ORDER BY p.created_at ASC";
                break;
            case 'title':
                $sql .= " ORDER BY p.title ASC";
                break;
            case 'relevance':
                if (!empty($searchTerm)) {
                    $sql .= " ORDER BY 
                        CASE 
                            WHEN p.title LIKE ? THEN 3
                            WHEN p.content LIKE ? THEN 2
                            ELSE 1
                        END DESC, p.created_at DESC";
                    $params[] = '%' . $searchTerm . '%';
                    $params[] = '%' . $searchTerm . '%';
                } else {
                    $sql .= " ORDER BY p.created_at DESC";
                }
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY p.created_at DESC";
                break;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Error in advanced search: " . $e->getMessage());
        return [];
    }
}

/**
 * Get posts by module
 * @param int $moduleId Module ID
 * @return array Array of posts
 */
function getPostsByModule($moduleId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, m.module_code, m.module_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id 
            JOIN modules m ON p.module_id = m.module_id 
            WHERE p.module_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$moduleId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting posts by module: " . $e->getMessage());
        return [];
    }
}

/**
 * Get posts by user
 * @param int $userId User ID
 * @return array Array of posts
 */
function getPostsByUser($userId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, m.module_code, m.module_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id 
            JOIN modules m ON p.module_id = m.module_id 
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting posts by user: " . $e->getMessage());
        return [];
    }
}

/**
 * Get related posts based on module and keywords
 * @param int $postId Current post ID to exclude
 * @param int $moduleId Module ID
 * @param string $title Post title for keyword matching
 * @param int $limit Number of results to return
 * @return array Array of related posts
 */
function getRelatedPosts($postId, $moduleId, $title, $limit = 5) {
    try {
        $pdo = getDbConnection();
        
        // Extract keywords from title (simple implementation)
        $keywords = explode(' ', strtolower($title));
        $keywords = array_filter($keywords, function($word) {
            return strlen($word) > 3; // Only words longer than 3 characters
        });
        
        if (empty($keywords)) {
            // If no keywords, just get other posts from same module
            $stmt = $pdo->prepare("
                SELECT p.*, u.username, m.module_code, m.module_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.user_id 
                JOIN modules m ON p.module_id = m.module_id 
                WHERE p.module_id = ? AND p.post_id != ?
                ORDER BY p.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$moduleId, $postId, $limit]);
        } else {
            // Search for posts with similar keywords
            $keywordConditions = [];
            $params = [];
            
            foreach ($keywords as $keyword) {
                $keywordConditions[] = "(p.title LIKE ? OR p.content LIKE ?)";
                $params[] = '%' . $keyword . '%';
                $params[] = '%' . $keyword . '%';
            }
            
            $sql = "
                SELECT p.*, u.username, m.module_code, m.module_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.user_id 
                JOIN modules m ON p.module_id = m.module_id 
                WHERE p.post_id != ? AND (p.module_id = ? OR (" . implode(' OR ', $keywordConditions) . "))
                ORDER BY 
                    CASE WHEN p.module_id = ? THEN 1 ELSE 2 END,
                    p.created_at DESC 
                LIMIT ?
            ";
            
            $params = array_merge([$postId, $moduleId], $params, [$moduleId, $limit]);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
        
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting related posts: " . $e->getMessage());
        return [];
    }
}

/**
 * Get search suggestions based on partial query
 * @param string $query Partial search query
 * @param int $limit Number of suggestions
 * @return array Array of suggestions
 */
function getSearchSuggestions($query, $limit = 5) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.title
            FROM posts p 
            WHERE p.title LIKE ?
            ORDER BY p.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute(['%' . $query . '%', $limit]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("Error getting search suggestions: " . $e->getMessage());
        return [];
    }
}

/**
 * Get post statistics
 * @return array Array with statistics
 */
function getPostStatistics() {
    try {
        $pdo = getDbConnection();
        
        // Total posts
        $totalPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
        
        // Posts this month
        $thisMonth = $pdo->query("
            SELECT COUNT(*) FROM posts 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ")->fetchColumn();
        
        // Posts today
        $today = $pdo->query("
            SELECT COUNT(*) FROM posts 
            WHERE DATE(created_at) = CURRENT_DATE()
        ")->fetchColumn();
        
        // Most active module
        $mostActiveModule = $pdo->query("
            SELECT m.module_code, m.module_name, COUNT(p.post_id) as post_count
            FROM modules m 
            LEFT JOIN posts p ON m.module_id = p.module_id 
            GROUP BY m.module_id 
            ORDER BY post_count DESC 
            LIMIT 1
        ")->fetch();
        
        return [
            'total_posts' => $totalPosts,
            'posts_this_month' => $thisMonth,
            'posts_today' => $today,
            'most_active_module' => $mostActiveModule
        ];
        
    } catch (Exception $e) {
        error_log("Error getting post statistics: " . $e->getMessage());
        return [
            'total_posts' => 0,
            'posts_this_month' => 0,
            'posts_today' => 0,
            'most_active_module' => null
        ];
    }
}

/**
 * Get recent posts for dashboard/sidebar
 * @param int $limit Number of posts to return
 * @return array Array of recent posts
 */
function getRecentPosts($limit = 5) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, m.module_code, m.module_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id 
            JOIN modules m ON p.module_id = m.module_id 
            ORDER BY p.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting recent posts: " . $e->getMessage());
        return [];
    }
}

/**
 * Get posts with pagination
 * @param int $page Page number (1-based)
 * @param int $perPage Posts per page
 * @return array Array with 'posts' and 'total_pages'
 */
function getPostsPaginated($page = 1, $perPage = 10) {
    try {
        $pdo = getDbConnection();
        
        // Get total count
        $totalPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
        $totalPages = ceil($totalPosts / $perPage);
        
        // Get posts for current page
        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, m.module_code, m.module_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id 
            JOIN modules m ON p.module_id = m.module_id 
            ORDER BY p.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $posts = $stmt->fetchAll();
        
        return [
            'posts' => $posts,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'total_posts' => $totalPosts
        ];
        
    } catch (Exception $e) {
        error_log("Error getting paginated posts: " . $e->getMessage());
        return [
            'posts' => [],
            'total_pages' => 0,
            'current_page' => 1,
            'total_posts' => 0
        ];
    }
}

/**
 * Check if post exists
 * @param int $postId Post ID
 * @return bool True if post exists
 */
function postExists($postId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE post_id = ?");
        $stmt->execute([$postId]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking if post exists: " . $e->getMessage());
        return false;
    }
}

/**
 * Get posts count by user
 * @param int $userId User ID
 * @return int Number of posts
 */
function getUserPostCount($userId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Error getting user post count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get posts count by module
 * @param int $moduleId Module ID
 * @return int Number of posts
 */
function getModulePostCount($moduleId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE module_id = ?");
        $stmt->execute([$moduleId]);
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Error getting module post count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Update post view count (for future feature)
 * @param int $postId Post ID
 * @return bool Success status
 */
function incrementPostViews($postId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE posts SET views = COALESCE(views, 0) + 1 WHERE post_id = ?");
        return $stmt->execute([$postId]);
    } catch (Exception $e) {
        error_log("Error incrementing post views: " . $e->getMessage());
        return false;
    }
}

/**
 * Get popular posts (most viewed - for future feature)
 * @param int $limit Number of posts to return
 * @return array Array of popular posts
 */
function getPopularPosts($limit = 5) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, m.module_code, m.module_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id 
            JOIN modules m ON p.module_id = m.module_id 
            ORDER BY COALESCE(p.views, 0) DESC, p.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting popular posts: " . $e->getMessage());
        return [];
    }
}
?>