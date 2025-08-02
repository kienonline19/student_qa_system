<?php
session_start();

require_once 'modules/users.php';
require_once 'includes/validation.php';

$pageTitle = 'Manage Users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid form submission. Please try again.";
    } else {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add':
                $formData = [
                    'username' => sanitizeInput($_POST['username'] ?? ''),
                    'email' => sanitizeInput($_POST['email'] ?? '')
                ];

                $validation = validateUserData($formData);
                if ($validation['success']) {
                    $userId = createUser($formData['username'], $formData['email']);
                    if ($userId) {
                        $_SESSION['success_message'] = "User created successfully!";
                    } else {
                        $_SESSION['error_message'] = "Failed to create user.";
                    }
                } else {
                    $_SESSION['error_message'] = implode('<br>', $validation['errors']);
                }
                break;

            case 'edit':
                $userId = intval($_POST['user_id'] ?? 0);
                $formData = [
                    'username' => sanitizeInput($_POST['username'] ?? ''),
                    'email' => sanitizeInput($_POST['email'] ?? '')
                ];

                $validation = validateUserData($formData, $userId);
                if ($validation['success']) {
                    $success = updateUser($userId, $formData['username'], $formData['email']);
                    if ($success) {
                        $_SESSION['success_message'] = "User updated successfully!";
                    } else {
                        $_SESSION['error_message'] = "Failed to update user.";
                    }
                } else {
                    $_SESSION['error_message'] = implode('<br>', $validation['errors']);
                }
                break;

            case 'delete':
                $userId = intval($_POST['user_id'] ?? 0);
                if ($userId > 0) {
                    $success = deleteUser($userId);
                    if ($success) {
                        $_SESSION['success_message'] = "User deleted successfully!";
                    } else {
                        $_SESSION['error_message'] = "Failed to delete user. User may have associated posts.";
                    }
                }
                break;
        }
    }

    header("Location: manage_users.php");
    exit;
}

$users = getAllUsers();

$csrfToken = generateCSRFToken();

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="bi bi-people-fill me-2"></i>Manage Users
                    </h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-circle-fill me-2"></i>Add New User
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No users found</h4>
                        <p class="text-muted mb-4">Add your first user to get started.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle-fill me-2"></i>Add First User
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Created</th>
                                    <th>Last Updated</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['user_id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php
                                            if ($user['updated_at'] !== $user['created_at']) {
                                                echo date('M j, Y', strtotime($user['updated_at']));
                                            } else {
                                                echo '<span class="text-muted">Never</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary"
                                                    onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <button class="btn btn-outline-danger"
                                                    onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
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
                            Total Users: <?php echo count($users); ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label for="add_username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="add_username" name="username"
                            required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+"
                            placeholder="Enter username">
                        <div class="invalid-feedback">
                            Username must be 3-50 characters long and contain only letters, numbers, and underscores.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="add_email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="add_email" name="email"
                            required maxlength="100"
                            placeholder="Enter email address">
                        <div class="invalid-feedback">
                            Please provide a valid email address.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle-fill me-2"></i>Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" id="edit_user_id">

                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="edit_username" name="username"
                            required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+">
                        <div class="invalid-feedback">
                            Username must be 3-50 characters long and contain only letters, numbers, and underscores.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="edit_email" name="email"
                            required maxlength="100">
                        <div class="invalid-feedback">
                            Please provide a valid email address.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle-fill me-2"></i>Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="delete_user_id">

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>

                    <p>Are you sure you want to delete the user <strong id="delete_username"></strong>?</p>
                    <p class="text-muted small">
                        Note: If this user has posted questions, the deletion may fail due to database constraints.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash-fill me-2"></i>Delete User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editUser(user) {
        document.getElementById('edit_user_id').value = user.user_id;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_email').value = user.email;

        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    }

    function deleteUser(userId, username) {
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('delete_username').textContent = username;

        const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
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

    document.getElementById('add_username').addEventListener('input', function() {
        const username = this.value;
        const pattern = /^[a-zA-Z0-9_]+$/;

        if (username.length >= 3 && username.length <= 50 && pattern.test(username)) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            if (username.length > 0) {
                this.classList.add('is-invalid');
            }
        }
    });

    document.getElementById('edit_username').addEventListener('input', function() {
        const username = this.value;
        const pattern = /^[a-zA-Z0-9_]+$/;

        if (username.length >= 3 && username.length <= 50 && pattern.test(username)) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            if (username.length > 0) {
                this.classList.add('is-invalid');
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>