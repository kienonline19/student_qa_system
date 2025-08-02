<?php
session_start();

require_once 'modules/modules.php';
require_once 'modules/posts.php';
require_once 'includes/validation.php';

$pageTitle = 'Manage Modules';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid form submission. Please try again.";
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add':
                $formData = [
                    'module_code' => strtoupper(sanitizeInput($_POST['module_code'] ?? '')),
                    'module_name' => sanitizeInput($_POST['module_name'] ?? '')
                ];
                
                $validation = validateModuleData($formData);
                if ($validation['success']) {
                    $moduleId = createModule($formData['module_code'], $formData['module_name']);
                    if ($moduleId) {
                        $_SESSION['success_message'] = "Module created successfully!";
                    } else {
                        $_SESSION['error_message'] = "Failed to create module.";
                    }
                } else {
                    $_SESSION['error_message'] = implode('<br>', $validation['errors']);
                }
                break;
                
            case 'edit':
                $moduleId = intval($_POST['module_id'] ?? 0);
                $formData = [
                    'module_code' => strtoupper(sanitizeInput($_POST['module_code'] ?? '')),
                    'module_name' => sanitizeInput($_POST['module_name'] ?? '')
                ];
                
                $validation = validateModuleData($formData, $moduleId);
                if ($validation['success']) {
                    $success = updateModule($moduleId, $formData['module_code'], $formData['module_name']);
                    if ($success) {
                        $_SESSION['success_message'] = "Module updated successfully!";
                    } else {
                        $_SESSION['error_message'] = "Failed to update module.";
                    }
                } else {
                    $_SESSION['error_message'] = implode('<br>', $validation['errors']);
                }
                break;
                
            case 'delete':
                $moduleId = intval($_POST['module_id'] ?? 0);
                if ($moduleId > 0) {
                    $success = deleteModule($moduleId);
                    if ($success) {
                        $_SESSION['success_message'] = "Module deleted successfully!";
                    } else {
                        $_SESSION['error_message'] = "Failed to delete module. Module may have associated posts.";
                    }
                }
                break;
        }
    }
    
    header("Location: manage_modules.php");
    exit;
}

$modules = getAllModules();
$modulesWithCounts = [];

foreach ($modules as $module) {
    $module['post_count'] = getModulePostCount($module['module_id']);
    $modulesWithCounts[] = $module;
}

$csrfToken = generateCSRFToken();

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="bi bi-book-fill me-2"></i>Manage Modules
                    </h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                        <i class="bi bi-plus-circle-fill me-2"></i>Add New Module
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($modulesWithCounts)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-book display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No modules found</h4>
                        <p class="text-muted mb-4">Add your first module to get started.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                            <i class="bi bi-plus-circle-fill me-2"></i>Add First Module
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Module Code</th>
                                    <th>Module Name</th>
                                    <th>Questions</th>
                                    <th>Created</th>
                                    <th>Last Updated</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modulesWithCounts as $module): ?>
                                    <tr>
                                        <td><?php echo $module['module_id']; ?></td>
                                        <td>
                                            <span class="tag">
                                                <?php echo htmlspecialchars($module['module_code']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($module['module_name']); ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($module['post_count'] > 0): ?>
                                                <a href="search.php?module=<?php echo $module['module_id']; ?>" 
                                                   class="badge bg-primary text-decoration-none">
                                                    <?php echo $module['post_count']; ?> question<?php echo $module['post_count'] != 1 ? 's' : ''; ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No questions</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($module['created_at'])); ?></td>
                                        <td>
                                            <?php 
                                            if ($module['updated_at'] !== $module['created_at']) {
                                                echo date('M j, Y', strtotime($module['updated_at']));
                                            } else {
                                                echo '<span class="text-muted">Never</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="editModule(<?php echo htmlspecialchars(json_encode($module)); ?>)"
                                                        title="Edit Module">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deleteModule(<?php echo $module['module_id']; ?>, '<?php echo htmlspecialchars($module['module_code']); ?>', <?php echo $module['post_count']; ?>)"
                                                        title="Delete Module">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Total Modules: <?php echo count($modulesWithCounts); ?>
                            | Total Questions: <?php echo array_sum(array_column($modulesWithCounts, 'post_count')); ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModuleModal" tabindex="-1" aria-labelledby="addModuleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModuleModalLabel">Add New Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="add_module_code" class="form-label">Module Code *</label>
                        <input type="text" class="form-control" id="add_module_code" name="module_code" 
                               required minlength="2" maxlength="20" pattern="[A-Z0-9]+"
                               placeholder="e.g., COMP1841" style="text-transform: uppercase;">
                        <div class="invalid-feedback">
                            Module code must be 2-20 characters long and contain only uppercase letters and numbers.
                        </div>
                        <div class="form-text">
                            Examples: COMP1841, MATH101, ENG202
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_module_name" class="form-label">Module Name *</label>
                        <input type="text" class="form-control" id="add_module_name" name="module_name" 
                               required minlength="5" maxlength="100"
                               placeholder="e.g., Web Programming 1">
                        <div class="invalid-feedback">
                            Module name must be 5-100 characters long.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle-fill me-2"></i>Add Module
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModuleModal" tabindex="-1" aria-labelledby="editModuleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModuleModalLabel">Edit Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="module_id" id="edit_module_id">
                    
                    <div class="mb-3">
                        <label for="edit_module_code" class="form-label">Module Code *</label>
                        <input type="text" class="form-control" id="edit_module_code" name="module_code" 
                               required minlength="2" maxlength="20" pattern="[A-Z0-9]+"
                               style="text-transform: uppercase;">
                        <div class="invalid-feedback">
                            Module code must be 2-20 characters long and contain only uppercase letters and numbers.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_module_name" class="form-label">Module Name *</label>
                        <input type="text" class="form-control" id="edit_module_name" name="module_name" 
                               required minlength="5" maxlength="100">
                        <div class="invalid-feedback">
                            Module name must be 5-100 characters long.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle-fill me-2"></i>Update Module
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModuleModal" tabindex="-1" aria-labelledby="deleteModuleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModuleModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="module_id" id="delete_module_id">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    
                    <p>Are you sure you want to delete the module <strong id="delete_module_code"></strong>?</p>
                    <p class="text-muted small" id="delete_warning_text">
                        Note: If this module has associated questions, the deletion may fail due to database constraints.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash-fill me-2"></i>Delete Module
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editModule(module) {
    document.getElementById('edit_module_id').value = module.module_id;
    document.getElementById('edit_module_code').value = module.module_code;
    document.getElementById('edit_module_name').value = module.module_name;
    
    const modal = new bootstrap.Modal(document.getElementById('editModuleModal'));
    modal.show();
}

function deleteModule(moduleId, moduleCode, postCount) {
    document.getElementById('delete_module_id').value = moduleId;
    document.getElementById('delete_module_code').textContent = moduleCode;
    
    const warningText = document.getElementById('delete_warning_text');
    if (postCount > 0) {
        warningText.innerHTML = `<strong>Warning:</strong> This module has ${postCount} associated question(s). Deletion will fail unless these questions are deleted first or moved to another module.`;
        warningText.className = 'text-danger small';
    } else {
        warningText.innerHTML = 'This module has no associated questions and can be safely deleted.';
        warningText.className = 'text-muted small';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModuleModal'));
    modal.show();
}

(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

document.getElementById('add_module_code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
    validateModuleCode(this);
});

document.getElementById('edit_module_code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
    validateModuleCode(this);
});

function validateModuleCode(input) {
    const code = input.value;
    const pattern = /^[A-Z0-9]+$/;
    
    if (code.length >= 2 && code.length <= 20 && pattern.test(code)) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    } else {
        input.classList.remove('is-valid');
        if (code.length > 0) {
            input.classList.add('is-invalid');
        }
    }
}

document.getElementById('add_module_name').addEventListener('input', function() {
    validateModuleName(this);
});

document.getElementById('edit_module_name').addEventListener('input', function() {
    validateModuleName(this);
});

function validateModuleName(input) {
    const name = input.value.trim();
    
    if (name.length >= 5 && name.length <= 100) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    } else {
        input.classList.remove('is-valid');
        if (name.length > 0) {
            input.classList.add('is-invalid');
        }
    }
}

document.getElementById('deleteModuleModal').addEventListener('show.bs.modal', function(event) {
    const deleteButton = this.querySelector('button[type="submit"]');
    const warningText = document.getElementById('delete_warning_text');
    
    if (warningText.classList.contains('text-danger')) {
        deleteButton.classList.add('disabled');
        deleteButton.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Cannot Delete';
        
        setTimeout(() => {
            deleteButton.classList.remove('disabled');
            deleteButton.innerHTML = '<i class="bi bi-trash-fill me-2"></i>Force Delete';
        }, 3000);
    } else {
        deleteButton.classList.remove('disabled');
        deleteButton.innerHTML = '<i class="bi bi-trash-fill me-2"></i>Delete Module';
    }
});

document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.classList.contains('disabled')) {
            const originalHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                }
            }, 5000);
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>