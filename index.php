<?php
/**
 * Homepage - Display all questions/posts
 */

session_start();

// Include required files
require_once 'modules/posts.php';
require_once 'modules/users.php';
require_once 'modules/modules.php';

// Set page title
$pageTitle = 'Home';

// Get posts with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$postsPerPage = 10;
$offset = ($page - 1) * $postsPerPage;

// Get all posts
$posts = getAllPosts();
$totalPosts = count($posts);
$totalPages = ceil($totalPosts / $postsPerPage);

// Get posts for current page
$currentPosts = array_slice($posts, $offset, $postsPerPage);

// Include header
include 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="card-title mb-3">
                    <i class="bi bi-chat-square-text-fill text-primary me-2"></i>
                    Student Questions & Answers
                </h1>
                <p class="card-text lead">
                    Welcome to our student Q&A platform! Browse questions from fellow students, 
                    share your knowledge, or ask your own questions to get help with coursework.
                </p>
                <div class="d-flex gap-2">
                    <a href="add_post.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle-fill me-2"></i>Ask a Question
                    </a>
                    <a href="contact.php" class="btn btn-outline-secondary">
                        <i class="bi bi-envelope-fill me-2"></i>Contact Admin
                    </a>
                </div>
            </div>
        </div>

        <!-- Posts List -->
        <?php if (empty($currentPosts)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-chat-square-text display-1 text-muted mb-3"></i>
                    <h3 class="text-muted">No questions yet</h3>
                    <p class="text-muted mb-4">Be the first to ask a question and help build our community!</p>
                    <a href="add_post.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle-fill me-2"></i>Ask the First Question
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($currentPosts as $post): ?>
                <div class="card post-card fade-in">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-2">
                                <a href="view_post.php?id=<?php echo $post['post_id']; ?>" 
                                   class="text-decoration-none">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="edit_post.php?id=<?php echo $post['post_id']; ?>">
                                            <i class="bi bi-pencil-fill me-2"></i>Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" 
                                           href="delete_post.php?id=<?php echo $post['post_id']; ?>"
                                           data-confirm-delete="Are you sure you want to delete this question?">
                                            <i class="bi bi-trash-fill me-2"></i>Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="post-content mb-3">
                            <?php 
                            $content = htmlspecialchars($post['content']);
                            echo strlen($content) > 200 ? substr($content, 0, 200) . '...' : $content;
                            ?>
                        </div>
                        
                        <?php if ($post['image_path']): ?>
                            <div class="mb-3">
                                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" 
                                     alt="Post image" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-meta d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-muted">
                                    <i class="bi bi-person-fill me-1"></i>
                                    <?php echo htmlspecialchars($post['username']); ?>
                                </span>
                                <span class="text-muted">
                                    <i class="bi bi-calendar-fill me-1"></i>
                                    <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                                </span>
                            </div>
                            <div>
                                <span class="badge bg-primary">
                                    <i class="bi bi-book-fill me-1"></i>
                                    <?php echo htmlspecialchars($post['module_code']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="view_post.php?id=<?php echo $post['post_id']; ?>" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye-fill me-1"></i>View Full Question
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Posts pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-graph-up-arrow me-2"></i>Platform Statistics
                </h6>
            </div>
            <div class="card-body">
                <?php
                $allUsers = getAllUsers();
                $allModules = getAllModules();
                ?>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="h4 text-primary"><?php echo $totalPosts; ?></div>
                        <small class="text-muted">Questions</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 text-success"><?php echo count($allUsers); ?></div>
                        <small class="text-muted">Users</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 text-info"><?php echo count($allModules); ?></div>
                        <small class="text-muted">Modules</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Modules -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-book-fill me-2"></i>Popular Modules
                </h6>
            </div>
            <div class="card-body">
                <?php
                // Get module post counts
                $moduleStats = [];
                foreach ($allModules as $module) {
                    $postCount = 0;
                    foreach ($posts as $post) {
                        if ($post['module_id'] == $module['module_id']) {
                            $postCount++;
                        }
                    }
                    if ($postCount > 0) {
                        $moduleStats[] = [
                            'module' => $module,
                            'count' => $postCount
                        ];
                    }
                }
                
                // Sort by post count
                usort($moduleStats, function($a, $b) {
                    return $b['count'] - $a['count'];
                });
                
                $topModules = array_slice($moduleStats, 0, 5);
                ?>
                
                <?php if (empty($topModules)): ?>
                    <p class="text-muted mb-0">No questions posted yet.</p>
                <?php else: ?>
                    <?php foreach ($topModules as $stat): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($stat['module']['module_code']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($stat['module']['module_name']); ?></small>
                            </div>
                            <span class="badge bg-secondary"><?php echo $stat['count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-clock-fill me-2"></i>Recent Activity
                </h6>
            </div>
            <div class="card-body">
                <?php $recentPosts = array_slice($posts, 0, 5); ?>
                <?php if (empty($recentPosts)): ?>
                    <p class="text-muted mb-0">No recent activity.</p>
                <?php else: ?>
                    <?php foreach ($recentPosts as $recentPost): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="small">
                                <a href="view_post.php?id=<?php echo $recentPost['post_id']; ?>" 
                                   class="text-decoration-none fw-bold">
                                    <?php echo htmlspecialchars(substr($recentPost['title'], 0, 50)) . (strlen($recentPost['title']) > 50 ? '...' : ''); ?>
                                </a>
                            </div>
                            <div class="small text-muted">
                                by <?php echo htmlspecialchars($recentPost['username']); ?> 
                                • <?php echo date('M j', strtotime($recentPost['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>