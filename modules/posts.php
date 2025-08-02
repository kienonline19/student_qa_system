<?php

require_once __DIR__ . '/../config/database.php';

function getAllPosts()
{
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

function getPostById($postId)
{
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

function createPost($title, $content, $userId, $moduleId, $imagePath = null)
{
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

function updatePost($postId, $title, $content, $userId, $moduleId, $imagePath = null)
{
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

function deletePost($postId)
{
    try {
        $post = getPostById($postId);

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM posts WHERE post_id = ?");
        $result = $stmt->execute([$postId]);

        if ($result && $post && $post['image_path'] && file_exists($post['image_path'])) {
            unlink($post['image_path']);
        }

        return $result;
    } catch (Exception $e) {
        error_log("Error deleting post: " . $e->getMessage());
        return false;
    }
}

function searchPosts($searchTerm)
{
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

function advancedSearchPosts($searchTerm = '', $moduleId = 0, $userId = 0, $sortBy = 'newest')
{
    try {
        $pdo = getDbConnection();

        $sql = "
            SELECT p.*, u.username, m.module_code, m.module_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id 
            JOIN modules m ON p.module_id = m.module_id 
            WHERE 1=1
        ";

        $params = [];

        if (!empty($searchTerm)) {
            $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
            $searchPattern = '%' . $searchTerm . '%';
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }

        if ($moduleId > 0) {
            $sql .= " AND p.module_id = ?";
            $params[] = $moduleId;
        }

        if ($userId > 0) {
            $sql .= " AND p.user_id = ?";
            $params[] = $userId;
        }

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

function getPostsByModule($moduleId)
{
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

function getPostsByUser($userId)
{
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

function getRelatedPosts($postId, $moduleId, $title, $limit = 5)
{
    try {
        $pdo = getDbConnection();

        $keywords = explode(' ', strtolower($title));
        $keywords = array_filter($keywords, function ($word) {
            return strlen($word) > 3;
        });

        if (empty($keywords)) {
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

function getSearchSuggestions($query, $limit = 5)
{
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

function getPostStatistics()
{
    try {
        $pdo = getDbConnection();

        $totalPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();

        $thisMonth = $pdo->query("
            SELECT COUNT(*) FROM posts 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ")->fetchColumn();

        $today = $pdo->query("
            SELECT COUNT(*) FROM posts 
            WHERE DATE(created_at) = CURRENT_DATE()
        ")->fetchColumn();

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

function getRecentPosts($limit = 5)
{
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

function getPostsPaginated($page = 1, $perPage = 10)
{
    try {
        $pdo = getDbConnection();

        $totalPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
        $totalPages = ceil($totalPosts / $perPage);

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

function postExists($postId)
{
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

function getUserPostCount($userId)
{
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

function getModulePostCount($moduleId)
{
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

function incrementPostViews($postId)
{
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE posts SET views = COALESCE(views, 0) + 1 WHERE post_id = ?");
        return $stmt->execute([$postId]);
    } catch (Exception $e) {
        error_log("Error incrementing post views: " . $e->getMessage());
        return false;
    }
}

function getPopularPosts($limit = 5)
{
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