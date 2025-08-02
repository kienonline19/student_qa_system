<?php
session_start();

require_once 'modules/posts.php';

$pageTitle = 'View Question';

$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId <= 0) {
    $_SESSION['error_message'] = "Invalid question ID.";
    header("Location: index.php");
    exit;
}

$post = getPostById($postId);

if (!$post) {
    $_SESSION['error_message'] = "Question not found.";
    header("Location: index.php");
    exit;
}

$pageTitle = htmlspecialchars($post['title']);

include 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="h3 mb-2"><?php echo htmlspecialchars($post['title']); ?></h1>
                        <div class="d-flex align-items-center gap-3 text-muted">
                            <span>
                                <i class="bi bi-person-fill me-1"></i>
                                Asked by <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                            </span>
                            <span>
                                <i class="bi bi-calendar-fill me-1"></i>
                                <?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?>
                            </span>
                            <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                <span>
                                    <i class="bi bi-pencil-fill me-1"></i>
                                    Updated <?php echo date('M j, Y', strtotime($post['updated_at'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="edit_post.php?id=<?php echo $post['post_id']; ?>">
                                    <i class="bi bi-pencil-fill me-2"></i>Edit Question
                                </a>
                            </li>
                            <li>
                                <button class="dropdown-item" onclick="copyToClipboard(window.location.href)">
                                    <i class="bi bi-link-45deg me-2"></i>Copy Link
                                </button>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger"
                                    href="delete_post.php?id=<?php echo $post['post_id']; ?>"
                                    data-confirm-delete="Are you sure you want to delete this question? This action cannot be undone.">
                                    <i class="bi bi-trash-fill me-2"></i>Delete Question
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge bg-primary fs-6">
                        <i class="bi bi-book-fill me-1"></i>
                        <?php echo htmlspecialchars($post['module_code']); ?> - <?php echo htmlspecialchars($post['module_name']); ?>
                    </span>
                </div>

                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>

                <?php if ($post['image_path'] && file_exists($post['image_path'])): ?>
                    <div class="mt-4">
                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>"
                            alt="Question image"
                            class="img-fluid rounded shadow-sm"
                            style="max-width: 100%; cursor: pointer;"
                            data-bs-toggle="modal"
                            data-bs-target="#imageModal">
                    </div>

                    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="imageModalLabel">Question Image</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>"
                                        alt="Question image" class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <a href="edit_post.php?id=<?php echo $post['post_id']; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil-fill me-1"></i>Edit
                        </a>
                        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="bi bi-printer-fill me-1"></i>Print
                        </button>
                    </div>
                    <div class="text-muted small">
                        Question ID: #<?php echo $post['post_id']; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to All Questions
                    </a>
                    <div class="d-flex gap-2">
                        <a href="add_post.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle-fill me-2"></i>Ask New Question
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i>Question Author
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                    </div>
                    <div>
                        <div class="fw-bold"><?php echo htmlspecialchars($post['username']); ?></div>
                        <div class="text-muted small">Student</div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <div class="mb-2">
                        <i class="bi bi-calendar-plus me-2"></i>
                        Member since <?php echo date('F Y', strtotime($post['created_at'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-book-fill me-2"></i>Module Information
                </h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary"><?php echo htmlspecialchars($post['module_code']); ?></h6>
                <p class="mb-3"><?php echo htmlspecialchars($post['module_name']); ?></p>

                <a href="search.php?module=<?php echo $post['module_id']; ?>"
                    class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search me-1"></i>More questions in this module
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-chat-square-text me-2"></i>Related Questions
                </h6>
            </div>
            <div class="card-body">
                <?php
                $allPosts = getAllPosts();
                $relatedPosts = [];
                foreach ($allPosts as $otherPost) {
                    if ($otherPost['module_id'] == $post['module_id'] && $otherPost['post_id'] != $post['post_id']) {
                        $relatedPosts[] = $otherPost;
                    }
                }
                $relatedPosts = array_slice($relatedPosts, 0, 5);
                ?>

                <?php if (empty($relatedPosts)): ?>
                    <p class="text-muted mb-0">No related questions found.</p>
                <?php else: ?>
                    <?php foreach ($relatedPosts as $relatedPost): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <a href="view_post.php?id=<?php echo $relatedPost['post_id']; ?>"
                                class="text-decoration-none">
                                <div class="fw-bold">
                                    <?php echo htmlspecialchars(substr($relatedPost['title'], 0, 60)) . (strlen($relatedPost['title']) > 60 ? '...' : ''); ?>
                                </div>
                            </a>
                            <div class="small text-muted">
                                by <?php echo htmlspecialchars($relatedPost['username']); ?>
                                • <?php echo date('M j', strtotime($relatedPost['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-question-circle me-2"></i>Need Help?
                </h6>
            </div>
            <div class="card-body">
                <p class="small mb-3">If this question doesn't help you, try:</p>
                <div class="d-grid gap-2">
                    <a href="add_post.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Ask Your Own Question
                    </a>
                    <a href="search.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-search me-1"></i>Search All Questions
                    </a>
                    <a href="contact.php" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-envelope me-1"></i>Contact Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .post-content {
        font-size: 1.1rem;
        line-height: 1.8;
    }

    .post-content img {
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .post-content img:hover {
        transform: scale(1.02);
    }

    @media print {

        .card-footer,
        .dropdown,
        .btn {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>

<script>
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                showAlert('Link copied to clipboard!', 'success', 2000);
            });
        } else {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                showAlert('Link copied to clipboard!', 'success', 2000);
            } catch (err) {
                showAlert('Failed to copy link.', 'danger');
            }
            document.body.removeChild(textArea);
        }
    }

    function showAlert(message, type = 'info', duration = 5000) {
        const alertContainer = document.querySelector('.container');
        if (!alertContainer) return;

        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
        <i class="bi bi-check-circle-fill me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

        alertContainer.insertBefore(alert, alertContainer.firstChild);

        if (duration > 0) {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, duration);
        }
    }
</script>

<?php include 'includes/footer.php'; ?>