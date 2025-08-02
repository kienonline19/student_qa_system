<?php
session_start();

require_once 'modules/posts.php';
require_once 'modules/users.php';
require_once 'modules/modules.php';
require_once 'includes/validation.php';
require_once 'includes/file_upload.php';

$pageTitle = 'Ask a Question';

$users = getAllUsers();
$modules = getAllModules();

$errors = [];
$formData = [
    'title' => '',
    'content' => '',
    'user_id' => '',
    'module_id' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid form submission. Please try again.";
    } else {
        $formData = [
            'title' => sanitizeInput($_POST['title'] ?? ''),
            'content' => sanitizeInput($_POST['content'] ?? ''),
            'user_id' => intval($_POST['user_id'] ?? 0),
            'module_id' => intval($_POST['module_id'] ?? 0)
        ];

        $validation = validatePostData($formData);

        if ($validation['success']) {
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = handleImageUpload($_FILES['image']);
                if ($imagePath === false) {
                    $errors[] = "Failed to upload image. Please check the file type and size.";
                }
            }

            if (empty($errors)) {
                $postId = createPost(
                    $formData['title'],
                    $formData['content'],
                    $formData['user_id'],
                    $formData['module_id'],
                    $imagePath
                );

                if ($postId) {
                    $_SESSION['success_message'] = "Question posted successfully!";
                    header("Location: view_post.php?id=" . $postId);
                    exit;
                } else {
                    $errors[] = "Failed to create post. Please try again.";
                    if ($imagePath && file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }
        } else {
            $errors = $validation['errors'];
        }
    }
}

$csrfToken = generateCSRFToken();

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">
                    <i class="bi bi-plus-circle-fill me-2"></i>Ask a Question
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

                    <div class="mb-3">
                        <label for="title" class="form-label">Question Title *</label>
                        <input type="text" class="form-control" id="title" name="title"
                            value="<?php echo htmlspecialchars($formData['title']); ?>"
                            required minlength="5" maxlength="200"
                            placeholder="Enter a clear, descriptive title for your question">
                        <div class="invalid-feedback">
                            Please provide a title between 5 and 200 characters.
                        </div>
                        <div class="form-text">
                            Be specific and clear. Good example: "How to connect PHP to MySQL using PDO?"
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Question Details *</label>
                        <textarea class="form-control" id="content" name="content" rows="8"
                            required minlength="10" maxlength="5000"
                            placeholder="Describe your question in detail. Include what you've tried, what error messages you're getting, and what you expect to happen."><?php echo htmlspecialchars($formData['content']); ?></textarea>
                        <div class="invalid-feedback">
                            Please provide question details between 10 and 5000 characters.
                        </div>
                        <div class="form-text">
                            Provide as much detail as possible. Include code snippets, error messages, and what you've already tried.
                        </div>
                    </div>

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

                    <div class="mb-4">
                        <label for="image" class="form-label">Screenshot/Image (Optional)</label>
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
                            Upload a screenshot of your code, error message, or any visual aid that helps explain your question.
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Questions
                        </a>
                        <div>
                            <button type="reset" class="btn btn-outline-warning me-2">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle-fill me-2"></i>Post Question
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-lightbulb-fill me-2"></i>Tips for Getting Good Answers
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-check-circle text-success me-2"></i>Do:</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-arrow-right text-primary me-2"></i>
                                Be specific and clear in your title
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-arrow-right text-primary me-2"></i>
                                Include what you've already tried
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-arrow-right text-primary me-2"></i>
                                Show your code or error messages
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-arrow-right text-primary me-2"></i>
                                Explain what you expect to happen
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-x-circle text-danger me-2"></i>Don't:</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-arrow-right text-primary me-2"></i>
                                Ask "why doesn't this work?" without details
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-arrow-right text-primary me-2"></i>
                                Post entire assignment requirements
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-arrow-right text-primary me-2"></i>
                                Use vague titles like "Help needed"
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-arrow-right text-primary me-2"></i>
                                Forget to mention the programming language
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>

<?php include 'includes/footer.php'; ?>