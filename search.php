<?php
/**
 * Search Page - Search questions/posts with filters
 */

session_start();

// Include required files
require_once 'modules/posts.php';
require_once 'modules/users.php';
require_once 'modules/modules.php';
require_once 'includes/validation.php';

// Set page title
$pageTitle = 'Search Questions';

// Get search parameters
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$moduleFilter = isset($_GET['module']) ? intval($_GET['module']) : 0;
$userFilter = isset($_GET['user']) ? intval($_GET['user']) : 0;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Get all users and modules for filters
$allUsers = getAllUsers();
$allModules = getAllModules();

// Initialize results
$searchResults = [];
$totalResults = 0;

// Perform search if there are search criteria
if (!empty($searchQuery) || $moduleFilter > 0 || $userFilter > 0) {
    $searchResults = performAdvancedSearch($searchQuery, $moduleFilter, $userFilter, $sortBy);
    $totalResults = count($searchResults);
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$resultsPerPage = 10;
$offset = ($page - 1) * $resultsPerPage;
$totalPages = ceil($totalResults / $resultsPerPage);

// Get results for current page
$currentResults = array_slice($searchResults, $offset, $resultsPerPage);

// Build search URL for pagination
$searchParams = [
    'q' => $searchQuery,
    'module' => $moduleFilter,
    'user' => $userFilter,
    'sort' => $sortBy
];
$baseSearchUrl = 'search.php?' . http_build_query(array_filter($searchParams));

// Include header
include 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <!-- Search Header -->
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="h3 mb-3">
                    <i class="bi bi-search me-2"></i>Search Questions
                </h1>
                
                <!-- Search Form -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="search_query" class="form-label">Search Terms</label>
                            <input type="text" class="form-control" id="search_query" name="q" 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>"
                                   placeholder="Enter keywords to search...">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="module_filter" class="form-label">Module</label>
                            <select class="form-select" id="module_filter" name="module">
                                <option value="">All Modules</option>
                                <?php foreach ($allModules as $module): ?>
                                    <option value="<?php echo $module['module_id']; ?>" 
                                            <?php echo $moduleFilter == $module['module_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($module['module_code'] . ' - ' . $module['module_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="user_filter" class="form-label">User</label>
                            <select class="form-select" id="user_filter" name="user">
                                <option value="">All Users</option>
                                <?php foreach ($allUsers as $user): ?>
                                    <option value="<?php echo $user['user_id']; ?>" 
                                            <?php echo $userFilter == $user['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select class="form-select" id="sort_by" name="sort">
                                <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo $sortBy == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="relevance" <?php echo $sortBy == 'relevance' ? 'selected' : ''; ?>>Most Relevant</option>
                                <option value="title" <?php echo $sortBy == 'title' ? 'selected' : ''; ?>>Title A-Z</option>
                            </select>
                        </div>
                        <div class="col-md-9 mb-3 d-flex align-items-end">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-2"></i>Search
                                </button>
                                <a href="search.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <?php if (!empty($searchQuery) || $moduleFilter > 0 || $userFilter > 0): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            Search Results 
                            <?php if ($totalResults > 0): ?>
                                <span class="badge bg-primary"><?php echo $totalResults; ?></span>
                            <?php endif; ?>
                        </h5>
                        
                        <?php if (!empty($searchQuery) || $moduleFilter > 0 || $userFilter > 0): ?>
                            <div class="text-muted small">
                                <?php 
                                $filters = [];
                                if (!empty($searchQuery)) $filters[] = "\"" . htmlspecialchars($searchQuery) . "\"";
                                if ($moduleFilter > 0) {
                                    $selectedModule = array_filter($allModules, function($m) use ($moduleFilter) { 
                                        return $m['module_id'] == $moduleFilter; 
                                    });
                                    if (!empty($selectedModule)) {
                                        $selectedModule = reset($selectedModule);
                                        $filters[] = "Module: " . htmlspecialchars($selectedModule['module_code']);
                                    }
                                }
                                if ($userFilter > 0) {
                                    $selectedUser = array_filter($allUsers, function($u) use ($userFilter) { 
                                        return $u['user_id'] == $userFilter; 
                                    });
                                    if (!empty($selectedUser)) {
                                        $selectedUser = reset($selectedUser);
                                        $filters[] = "User: " . htmlspecialchars($selectedUser['username']);
                                    }
                                }
                                echo "Filters: " . implode(', ', $filters);
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (empty($currentResults)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-search display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">No results found</h4>
                            <p class="text-muted mb-4">
                                Try adjusting your search criteria or 
                                <a href="search.php">clear all filters</a> to see more results.
                            </p>
                            <a href="add_post.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle-fill me-2"></i>Ask a New Question
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Results List -->
                        <?php foreach ($currentResults as $result): ?>
                            <div class="question-summary border-bottom pb-3 mb-3">
                                <div class="d-flex">
                                    <!-- Stats -->
                                    <div class="flex-shrink-0 me-3 text-center" style="min-width: 80px;">
                                        <div class="question-stats">
                                            <div class="stat-number">0</div>
                                            <div>votes</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-grow-1">
                                        <h5 class="mb-2">
                                            <a href="view_post.php?id=<?php echo $result['post_id']; ?>" 
                                               class="question-hyperlink">
                                                <?php echo highlightSearchTerms($result['title'], $searchQuery); ?>
                                            </a>
                                        </h5>
                                        
                                        <div class="excerpt mb-2">
                                            <?php 
                                            $excerpt = strip_tags($result['content']);
                                            $excerpt = strlen($excerpt) > 200 ? substr($excerpt, 0, 200) . '...' : $excerpt;
                                            echo highlightSearchTerms($excerpt, $searchQuery);
                                            ?>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="tag">
                                                    <?php echo htmlspecialchars($result['module_code']); ?>
                                                </span>
                                            </div>
                                            
                                            <div class="user-info d-flex align-items-center">
                                                <span class="me-2">asked <?php echo timeAgo($result['created_at']); ?></span>
                                                <span class="user-details">
                                                    <?php echo htmlspecialchars($result['username']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Search results pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo $baseSearchUrl; ?>&page=<?php echo $page - 1; ?>" 
                                               aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo $baseSearchUrl; ?>&page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo $baseSearchUrl; ?>&page=<?php echo $page + 1; ?>" 
                                               aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- No search performed yet -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-search display-1 text-muted mb-3"></i>
                    <h3 class="text-muted mb-3">Search Questions</h3>
                    <p class="text-muted mb-4">
                        Enter keywords, select filters, and click Search to find relevant questions.
                    </p>
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item border-0 bg-transparent">
                                    <strong>Search Tips:</strong>
                                </div>
                                <div class="list-group-item border-0 bg-transparent">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Use specific keywords from your question
                                </div>
                                <div class="list-group-item border-0 bg-transparent">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Filter by module to narrow results
                                </div>
                                <div class="list-group-item border-0 bg-transparent">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Sort by relevance or date
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Search -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-lightning-fill me-2"></i>Quick Search
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" 
                               placeholder="Quick search..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                
                <div class="d-grid gap-2">
                    <?php foreach (array_slice($allModules, 0, 5) as $module): ?>
                        <a href="search.php?module=<?php echo $module['module_id']; ?>" 
                           class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-book me-2"></i>
                            <?php echo htmlspecialchars($module['module_code']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Search Statistics -->
        <?php if ($totalResults > 0): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Search Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stats-number"><?php echo $totalResults; ?></div>
                            <small class="text-muted">Results Found</small>
                        </div>
                        <div class="col-6">
                            <div class="stats-number"><?php echo $totalPages; ?></div>
                            <small class="text-muted">Pages</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Popular Searches -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-fire me-2"></i>Popular Topics
                </h6>
            </div>
            <div class="card-body">
                <?php
                $popularTopics = [
                    'PHP PDO', 'MySQL', 'HTML CSS', 'JavaScript', 'Bootstrap',
                    'Form Validation', 'Database Design', 'Web Security'
                ];
                ?>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($popularTopics as $topic): ?>
                        <a href="search.php?q=<?php echo urlencode($topic); ?>" 
                           class="tag me-1 mb-1">
                            <?php echo htmlspecialchars($topic); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

/**
 * Perform advanced search with multiple criteria
 */
function performAdvancedSearch($query, $moduleId, $userId, $sortBy) {
    $allPosts = getAllPosts();
    $results = [];
    
    foreach ($allPosts as $post) {
        $match = true;
        
        // Text search in title and content
        if (!empty($query)) {
            $searchText = strtolower($query);
            $postTitle = strtolower($post['title']);
            $postContent = strtolower($post['content']);
            
            if (strpos($postTitle, $searchText) === false && 
                strpos($postContent, $searchText) === false) {
                $match = false;
            }
        }
        
        // Module filter
        if ($moduleId > 0 && $post['module_id'] != $moduleId) {
            $match = false;
        }
        
        // User filter
        if ($userId > 0 && $post['user_id'] != $userId) {
            $match = false;
        }
        
        if ($match) {
            $results[] = $post;
        }
    }
    
    // Sort results
    usort($results, function($a, $b) use ($sortBy, $query) {
        switch ($sortBy) {
            case 'oldest':
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            case 'relevance':
                if (!empty($query)) {
                    $scoreA = calculateRelevanceScore($a, $query);
                    $scoreB = calculateRelevanceScore($b, $query);
                    return $scoreB - $scoreA;
                }
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            case 'title':
                return strcasecmp($a['title'], $b['title']);
            case 'newest':
            default:
                return strtotime($b['created_at']) - strtotime($a['created_at']);
        }
    });
    
    return $results;
}

/**
 * Calculate relevance score for search results
 */
function calculateRelevanceScore($post, $query) {
    $score = 0;
    $query = strtolower($query);
    $title = strtolower($post['title']);
    $content = strtolower($post['content']);
    
    // Title matches are more important
    if (strpos($title, $query) !== false) {
        $score += 10;
    }
    
    // Exact title match
    if ($title === $query) {
        $score += 20;
    }
    
    // Content matches
    $contentMatches = substr_count($content, $query);
    $score += $contentMatches * 2;
    
    // Word matches
    $queryWords = explode(' ', $query);
    foreach ($queryWords as $word) {
        $word = trim($word);
        if (strlen($word) > 2) {
            if (strpos($title, $word) !== false) $score += 5;
            if (strpos($content, $word) !== false) $score += 1;
        }
    }
    
    return $score;
}

/**
 * Highlight search terms in text
 */
function highlightSearchTerms($text, $query) {
    if (empty($query)) {
        return htmlspecialchars($text);
    }
    
    $text = htmlspecialchars($text);
    $words = explode(' ', $query);
    
    foreach ($words as $word) {
        $word = trim($word);
        if (strlen($word) > 2) {
            $text = preg_replace('/(' . preg_quote($word, '/') . ')/i', 
                               '<span class="search-highlight">$1</span>', $text);
        }
    }
    
    return $text;
}

/**
 * Format time ago
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}

include 'includes/footer.php';
?>