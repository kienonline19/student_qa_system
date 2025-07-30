<?php
/**
 * Edit Post Page - Form to edit existing questions/posts
 */

session_start();

// Include required files
require_once 'modules/posts.php';
require_once 'modules/users.php';
require_once 'modules/modules.php';
require_once 'includes/validation.php';
require_once 'includes/file_upload.php';

// Set page title
$pageTitle = 'Edit Question';

// Get post ID from URL
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId <= 0) {
    $_SESSION['error_message'] = "Invalid question ID.";
    header("Location: index.php");
    exit;
}

// Get post data
$post = getPostById($postId);

if (!$post) {
    $_SESSION['error_message'] = "Question not found.";
    header("Location: index.php");
    exit;
}

// Get users and modules for dropdowns
$users = getAllUsers();
$modules = getAllModules();

// Initialize variables
$errors = [];
$formData = [
    'title' => $post['title'],
    'content' => $post['content'],
    'user_id' => $post['user_id'],
    'module_id' => $post['module_id']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid form submission. Please try again.";
    } else {
        // Sanitize input data
        $formData = [
            'title' => sanitizeInput($_POST['title'] ?? ''),
            'content' => sanitizeInput($_POST['content'] ?? ''),
            'user_id' => intval($_POST['user_id'] ?? 0),
            'module_id' => intval($_POST['module_id'] ?? 0)
        ];
        
        // Validate form data
        $validation = validatePostData($formData);
        
        if ($validation['success']) {
            // Handle image upload
            $imagePath = $post['image_path']; // Keep existing image by default
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // New image uploaded
                $newImagePath = handleImageUpload($_FILES['image']);
                if ($newImagePath !== false) {
                    // Delete old image if it exists
                    if ($post['image_path'] && file_exists($post['image_path'])) {
                        unlink($post['image_path']);
                    }
                    $imagePath = $newImagePath;
                } else {
                    $errors[] = "Failed to upload new image. Please check the file type and size.";
                }
            } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
                // Remove existing image
                if ($post['image_path'] && file_exists($post['image_path'])) {
                    unlink($post['image_path']);
                }
                $imagePath = null;
            }
            
            if (empty($errors)) {
                // Update the post
                $success = updatePost(
                    $postId,
                    $formData['title'], 
                    $formData['content'], 
                    $formData['user_id'], 
                    $formData['module_id'], 
                    $imagePath
                );
                
                if ($success) {
                    $_SESSION['success_message'] = "Question updated successfully!";
                    header("Location: view_post.php?id=" . $postId);
                    exit;
                } else {
                    $errors[] = "Failed to update question. Please try again.";
                }
            }
        } else {
            $errors = $validation['errors'];
        }
    }
}

// Generate CSRF token
$csrfToken = generateCSRFToken();

// Include header
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">
                    <i class="bi bi-pencil-fill me-2"></i>Edit Question
                </h2>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Question Title *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($formData['title']); ?>"
                               required minlength="5" maxlength="200"
                               placeholder="Enter a clear, descriptive title for your question">
                        <div class="invalid-feedback">
                            Please provide a title between 5 and 200 characters.
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="mb-3">
                        <label for="content" class="form-label">Question Details *</label>
                        <textarea class="form-control" id="content" name="content" rows="8"
                                  required minlength="10" maxlength="5000"
                                  placeholder="Describe your question in detail..."><?php echo htmlspecialchars($formData['content']); ?></textarea>
                        <div class="invalid-feedback">
                            Please provide question details between 10 and 5000 characters.
                        </div>
                    </div>

                    <!-- User Selection -->
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Posted By *</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Select a user...</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['user_id']; ?>" 
                                        <?php echo $formData['user_id'] == $user['user_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['username'] . ' (' . $user['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Please select who is posting this question.
                        </div>
                    </div>

                    <!-- Module Selection -->
                    <div class="mb-3">
                        <label for="module_id" class="form-label">Module *</label>
                        <select class="form-select" id="module_id" name="module_id" required>
                            <option value="">Select a module...</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?php echo $module['module_id']; ?>" 
                                        <?php echo $formData['module_id'] == $module['module_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($module['module_code'] . ' - ' . $module['module_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Please select the relevant module for this question.
                        </div>
                    </div>

                    <!-- Current Image Display -->
                    <?php if ($post['image_path'] && file_exists($post['image_path'])): ?>
                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div class="border rounded p-3">
                                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" 
                                     alt="Current image" class="img-fluid rounded mb-2" style="max-height: 200px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                    <label class="form-check-label text-danger" for="remove_image">
                                        <i class="bi bi-trash me-1"></i>Remove current image
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Image Upload -->
                    <div class="mb-4">
                        <label for="image" class="form-label">
                            <?php echo $post['image_path'] ? 'Replace Image (Optional)' : 'Add Image (Optional)'; ?>
                        </label>
                        <div class="file-upload-area border rounded p-4 text-center">
                            <input type="file" class="form-control" id="image" name="image" 
                                   accept="image/*" style="display: none;">
                            <div class="upload-content">
                                <i class="bi bi-cloud-upload display-4 text-muted mb-3"></i>
                                <p class="mb-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                            onclick="document.getElementById('image').click()">
                                        Choose File
                                    </button>
                                    or drag and drop an image here
                                </p>
                                <small class="text-muted">
                                    Supported formats: JPG, PNG, GIF, WebP (Max 5MB)
                                </small>
                            </div>
                            <div class="file-preview mt-3" style="display: none;"></div>
                        </div>
                        <div class="form-text">
                            <?php echo $post['image_path'] ? 'Upload a new image to replace the current one.' : 'Upload a screenshot or visual aid.'; ?>
                        </div>
                    </div>

                    <!-- Form Actions -->
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
                            <button type="reset" class="btn btn-outline-warning me-2">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reset Changes
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle-fill me-2"></i>Update Question
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change History Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>Question Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Originally Posted:</strong><br>
                        <span class="text-muted">
                            <?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Last Updated:</strong><br>
                        <span class="text-muted">
                            <?php 
                            if ($post['updated_at'] !== $post['created_at']) {
                                echo date('F j, Y \a\t g:i A', strtotime($post['updated_at']));
                            } else {
                                echo 'Never modified';
                            }
                            ?>
                        </span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Question ID:</strong> #<?php echo $post['post_id']; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Current Module:</strong> <?php echo htmlspecialchars($post['module_code']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle file upload preview
document.getElementById('image').addEventListener('change', function() {
    const file = this.files[0];
    const preview = document.querySelector('.file-preview');
    const uploadContent = document.querySelector('.upload-content');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                <p class="mt-2 mb-0 text-muted">${file.name} (${formatFileSize(file.size)})</p>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="clearFilePreview()">
                    <i class="bi bi-trash"></i> Remove
                </button>
            `;
            preview.style.display = 'block';
            uploadContent.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});

// Handle remove current image checkbox
document.getElementById('remove_image')?.addEventListener('change', function() {
    if (this.checked) {
        // Clear new file input when removing current image
        document.getElementById('image').value = '';
        clearFilePreview();
        
        // Show warning
        if (!document.querySelector('.remove-warning')) {
            const warning = document.createElement('div');
            warning.className = 'alert alert-warning remove-warning mt-2';
            warning.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Current image will be permanently deleted when you save.';
            this.closest('.border').appendChild(warning);
        }
    } else {
        // Remove warning
        const warning = document.querySelector('.remove-warning');
        if (warning) warning.remove();
    }
});

function clearFilePreview() {
    document.getElementById('image').value = '';
    document.querySelector('.file-preview').style.display = 'none';
    document.querySelector('.upload-content').style.display = 'block';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Reset form functionality
document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
    e.preventDefault();
    if (confirm('Are you sure you want to reset all changes? This will restore the original values.')) {
        // Reset form fields to original values
        document.getElementById('title').value = '<?php echo addslashes($post['title']); ?>';
        document.getElementById('content').value = '<?php echo addslashes($post['content']); ?>';
        document.getElementById('user_id').value = '<?php echo $post['user_id']; ?>';
        document.getElementById('module_id').value = '<?php echo $post['module_id']; ?>';
        
        // Clear file input and preview
        clearFilePreview();
        
        // Uncheck remove image
        const removeCheckbox = document.getElementById('remove_image');
        if (removeCheckbox) {
            removeCheckbox.checked = false;
            const warning = document.querySelector('.remove-warning');
            if (warning) warning.remove();
        }
        
        // Remove validation classes
        document.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
            el.classList.remove('is-valid', 'is-invalid');
        });
        document.querySelector('form').classList.remove('was-validated');
    }
});
</script>

<?php include 'includes/footer.php'; ?>