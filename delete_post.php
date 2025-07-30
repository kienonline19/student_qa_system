<?php
/**
 * Delete Post Page - Handle post deletion
 */

session_start();

// Include required files
require_once 'modules/posts.php';
require_once 'includes/validation.php';

// Get post ID from URL
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId <= 0) {
    $_SESSION['error_message'] = "Invalid question ID.";
    header("Location: index.php");
    exit;
}

// Get post data for confirmation
$post = getPostById($postId);

if (!$post) {
    $_SESSION['error_message'] = "Question not found.";
    header("Location: index.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid form submission. Please try again.";
        header("Location: view_post.php?id=" . $postId);
        exit;
    }
    
    // Confirm deletion
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
        // Delete the post
        $success = deletePost($postId);
        
        if ($success) {
            $_SESSION['success_message'] = "Question deleted successfully.";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Failed to delete question. Please try again.";
            header("Location: view_post.php?id=" . $postId);
            exit;
        }
    } else {
        // User cancelled deletion
        $_SESSION['error_message'] = "Question deletion cancelled.";
        header("Location: view_post.php?id=" . $postId);
        exit;
    }
}

// Generate CSRF token
$csrfToken = generateCSRFToken();

// Set page title
$pageTitle = 'Delete Question';

// Include header
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h3 class="mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Deletion
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-warning-triangle-fill me-2"></i>
                    <strong>Warning!</strong> This action cannot be undone. The question and any associated data will be permanently deleted.
                </div>

                <h5>Are you sure you want to delete this question?</h5>
                
                <!-- Question Preview -->
                <div class="card mt-3 mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Question to be deleted:</h6>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h6>
                        <p class="card-text">
                            <?php 
                            $content = htmlspecialchars($post['content']);
                            echo strlen($content) > 150 ? substr($content, 0, 150) . '...' : $content;
                            ?>
                        </p>
                        <div class="text-muted small">
                            <span class="me-3">
                                <i class="bi bi-person-fill me-1"></i>
                                Posted by <?php echo htmlspecialchars($post['username']); ?>
                            </span>
                            <span class="me-3">
                                <i class="bi bi-calendar-fill me-1"></i>
                                <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                            </span>
                            <span class="badge bg-primary">
                                <?php echo htmlspecialchars($post['module_code']); ?>
                            </span>
                        </div>
                        
                        <?php if ($post['image_path'] && file_exists($post['image_path'])): ?>
                            <div class="mt-3">
                                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" 
                                     alt="Question image" class="img-fluid rounded" style="max-height: 100px;">
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-image-fill me-1"></i>
                                    This image will also be deleted
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Deletion Form -->
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="understand" required>
                            <label class="form-check-label" for="understand">
                                I understand that this action cannot be undone
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="view_post.php?id=<?php echo $postId; ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Question
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-house-fill me-2"></i>Home
                            </a>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" onclick="cancelDeletion()">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </button>
                            <button type="submit" name="confirm_delete" value="yes" 
                                    class="btn btn-danger" id="deleteBtn" disabled>
                                <i class="bi bi-trash-fill me-2"></i>Delete Question
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>What happens when you delete this question?
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        The question will be permanently removed from the database
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Any uploaded images will be deleted from the server
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        The question will no longer appear in search results
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-x-circle text-danger me-2"></i>
                        This action cannot be reversed
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-x-circle text-danger me-2"></i>
                        All data associated with this question will be lost
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Enable delete button only when checkbox is checked
document.getElementById('understand').addEventListener('change', function() {
    document.getElementById('deleteBtn').disabled = !this.checked;
});

// Cancel deletion function
function cancelDeletion() {
    if (confirm('Are you sure you want to cancel? You will return to the question.')) {
        window.location.href = 'view_post.php?id=<?php echo $postId; ?>';
    }
}

// Add extra confirmation for delete button
document.getElementById('deleteBtn').addEventListener('click', function(e) {
    if (!confirm('FINAL CONFIRMATION: Are you absolutely sure you want to delete this question? This action CANNOT be undone.')) {
        e.preventDefault();
        return false;
    }
});

// Handle form submission with loading state
document.querySelector('form').addEventListener('submit', function() {
    const deleteBtn = document.getElementById('deleteBtn');
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
});

// Prevent accidental navigation away
window.addEventListener('beforeunload', function(e) {
    e.preventDefault();
    e.returnValue = '';
});
</script>

<style>
/* Additional styles for delete confirmation */
.card.border-danger {
    box-shadow: 0 0 15px rgba(220, 53, 69, 0.3);
}

.btn-danger:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

#deleteBtn:not(:disabled):hover {
    background-color: #c82333;
    border-color: #bd2130;
}
</style>

<?php include 'includes/footer.php'; ?>