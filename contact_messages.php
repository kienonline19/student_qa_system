<?php
/**
 * Contact Messages Page - View and manage contact form submissions
 */

session_start();

// Include required files
require_once 'modules/contact.php';
require_once 'includes/validation.php';

// Set page title
$pageTitle = 'Contact Messages';

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid form submission. Please try again.";
    } else {
        $messageId = intval($_POST['message_id'] ?? 0);
        if ($messageId > 0) {
            $success = deleteContactMessage($messageId);
            if ($success) {
                $_SESSION['success_message'] = "Message deleted successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to delete message.";
            }
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: contact_messages.php");
    exit;
}

// Get all contact messages
$messages = getAllContactMessages();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$messagesPerPage = 10;
$offset = ($page - 1) * $messagesPerPage;
$totalMessages = count($messages);
$totalPages = ceil($totalMessages / $messagesPerPage);

// Get messages for current page
$currentMessages = array_slice($messages, $offset, $messagesPerPage);

// Generate CSRF token
$csrfToken = generateCSRFToken();

// Include header
include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="bi bi-envelope-fill me-2"></i>Contact Messages
                        <?php if ($totalMessages > 0): ?>
                            <span class="badge bg-primary"><?php echo $totalMessages; ?></span>
                        <?php endif; ?>
                    </h2>
                    <a href="contact.php" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle me-2"></i>Send New Message
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($currentMessages)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-envelope display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No messages yet</h4>
                        <p class="text-muted mb-4">Contact form submissions will appear here.</p>
                        <a href="contact.php" class="btn btn-primary">
                            <i class="bi bi-envelope-fill me-2"></i>Go to Contact Form
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Messages List -->
                    <div class="row">
                        <?php foreach ($currentMessages as $message): ?>
                            <div class="col-12 mb-4">
                                <div class="card border-start border-4 border-primary">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    <i class="bi bi-envelope-open me-2"></i>
                                                    <?php echo htmlspecialchars($message['subject']); ?>
                                                </h6>
                                                <div class="d-flex align-items-center gap-3 text-muted small">
                                                    <span>
                                                        <i class="bi bi-person me-1"></i>
                                                        <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                                    </span>
                                                    <span>
                                                        <i class="bi bi-envelope me-1"></i>
                                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" 
                                                           class="text-decoration-none">
                                                            <?php echo htmlspecialchars($message['email']); ?>
                                                        </a>
                                                    </span>
                                                    <span>
                                                        <i class="bi bi-calendar me-1"></i>
                                                        <?php echo date('M j, Y \a\t g:i A', strtotime($message['created_at'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" 
                                                           href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>">
                                                            <i class="bi bi-reply me-2"></i>Reply via Email
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item" 
                                                                onclick="copyToClipboard('<?php echo htmlspecialchars($message['email']); ?>')">
                                                            <i class="bi bi-clipboard me-2"></i>Copy Email
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button class="dropdown-item text-danger" 
                                                                onclick="deleteMessage(<?php echo $message['message_id']; ?>, '<?php echo htmlspecialchars($message['subject']); ?>')">
                                                            <i class="bi bi-trash me-2"></i>Delete
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="message-content">
                                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                        </div>
                                        
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="d-flex gap-2">
                                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="bi bi-reply-fill me-1"></i>Reply
                                                </a>
                                                <button class="btn btn-outline-secondary btn-sm" 
                                                        onclick="copyToClipboard('<?php echo htmlspecialchars($message['email']); ?>')">
                                                    <i class="bi bi-clipboard me-1"></i>Copy Email
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Contact messages pagination" class="mt-4">
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
        </div>
    </div>
</div>

<!-- Delete Message Modal -->
<div class="modal fade" id="deleteMessageModal" tabindex="-1" aria-labelledby="deleteMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteMessageModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="message_id" id="delete_message_id">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    
                    <p>Are you sure you want to delete the message with subject:</p>
                    <p class="fw-bold" id="delete_message_subject"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash-fill me-2"></i>Delete Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteMessage(messageId, subject) {
    document.getElementById('delete_message_id').value = messageId;
    document.getElementById('delete_message_subject').textContent = subject;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteMessageModal'));
    modal.show();
}

function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showAlert('Email address copied to clipboard!', 'success', 2000);
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showAlert('Email address copied to clipboard!', 'success', 2000);
        } catch (err) {
            showAlert('Failed to copy email address.', 'danger');
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
    ';
    
    alertContainer.insertBefore(alert, alertContainer.firstChild);
    
    if (duration > 0) {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, duration);
    }
}

// Auto-refresh messages every 30 seconds (optional)
let autoRefresh = false;
if (autoRefresh) {
    setInterval(() => {
        window.location.reload();
    }, 30000);
}
</script>

<style>
.message-content {
    max-height: 200px;
    overflow-y: auto;
    line-height: 1.6;
}

.border-start {
    border-left-width: 4px !important;
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s ease;
}

@media (max-width: 768px) {
    .message-content {
        max-height: 150px;
    }
    
    .d-flex.gap-3 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>