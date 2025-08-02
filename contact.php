<?php
session_start();

require_once 'modules/contact.php';
require_once 'includes/validation.php';

$pageTitle = 'Contact Us';

$errors = [];
$formData = [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid form submission. Please try again.";
    } else {
        $formData = [
            'name' => sanitizeInput($_POST['name'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? ''),
            'subject' => sanitizeInput($_POST['subject'] ?? ''),
            'message' => sanitizeInput($_POST['message'] ?? '')
        ];

        $validation = validateContactData($formData);

        if ($validation['success']) {
            $messageId = saveContactMessage(
                $formData['name'],
                $formData['email'],
                $formData['subject'],
                $formData['message']
            );

            if ($messageId) {
                $adminEmail = 'admin@student-qa.local';
                $emailSubject = 'New Contact Form Submission: ' . $formData['subject'];
                $emailMessage = "
                    <h3>New Contact Form Submission</h3>
                    <p><strong>From:</strong> {$formData['name']} ({$formData['email']})</p>
                    <p><strong>Subject:</strong> {$formData['subject']}</p>
                    <p><strong>Message:</strong></p>
                    <p>" . nl2br(htmlspecialchars($formData['message'])) . "</p>
                    <hr>
                    <p><small>Submitted on " . date('F j, Y \a\t g:i A') . "</small></p>
                ";

                sendEmailNotification($adminEmail, $emailSubject, $emailMessage, $formData['email']);

                $_SESSION['success_message'] = "Thank you for your message! We'll get back to you as soon as possible.";

                $formData = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
            } else {
                $errors[] = "Failed to send your message. Please try again.";
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
                    <i class="bi bi-envelope-fill me-2"></i>Contact Us
                </h2>
            </div>
            <div class="card-body">
                <p class="lead mb-4">
                    Have a question, suggestion, or need help? We'd love to hear from you!
                    Send us a message and we'll respond as quickly as possible.
                </p>

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

                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Your Name *</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?php echo htmlspecialchars($formData['name']); ?>"
                                required minlength="2" maxlength="100"
                                placeholder="Enter your full name">
                            <div class="invalid-feedback">
                                Please provide your name (2-100 characters).
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($formData['email']); ?>"
                                required maxlength="100"
                                placeholder="Enter your email address">
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject *</label>
                        <input type="text" class="form-control" id="subject" name="subject"
                            value="<?php echo htmlspecialchars($formData['subject']); ?>"
                            required minlength="5" maxlength="200"
                            placeholder="What is this message about?">
                        <div class="invalid-feedback">
                            Please provide a subject (5-200 characters).
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="6"
                            required minlength="10" maxlength="2000"
                            placeholder="Please describe your question, suggestion, or issue in detail..."><?php echo htmlspecialchars($formData['message']); ?></textarea>
                        <div class="invalid-feedback">
                            Please provide your message (10-2000 characters).
                        </div>
                        <div class="form-text">
                            Be as specific as possible to help us assist you better.
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Home
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send-fill me-2"></i>Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle-fill me-2"></i>Other Ways to Reach Us
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-question-circle me-2"></i>Frequently Asked Questions</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <strong>How do I ask a good question?</strong><br>
                                <small class="text-muted">Be specific, include error messages, and describe what you've tried.</small>
                            </li>
                            <li class="mb-2">
                                <strong>Can I edit my questions?</strong><br>
                                <small class="text-muted">Yes, use the edit button on your question to make changes.</small>
                            </li>
                            <li class="mb-2">
                                <strong>How do I delete my account?</strong><br>
                                <small class="text-muted">Contact an administrator to request account deletion.</small>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-clock me-2"></i>Response Times</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <strong>General Questions:</strong><br>
                                <small class="text-muted">Usually within 24-48 hours</small>
                            </li>
                            <li class="mb-2">
                                <strong>Technical Issues:</strong><br>
                                <small class="text-muted">Priority response within 24 hours</small>
                            </li>
                            <li class="mb-2">
                                <strong>Account Problems:</strong><br>
                                <small class="text-muted">Same day response during business hours</small>
                            </li>
                        </ul>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-shield-check me-2"></i>Privacy & Security</h6>
                        <p class="small text-muted mb-0">
                            Your contact information is kept secure and will only be used to respond to your inquiry.
                            We do not share your information with third parties.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-people me-2"></i>Community Guidelines</h6>
                        <p class="small text-muted mb-0">
                            Please be respectful in all communications.
                            Harassment, spam, or inappropriate content will not be tolerated.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

    const messageField = document.getElementById('message');
    const maxLength = 2000;

    const counterElement = document.createElement('div');
    counterElement.className = 'character-counter text-muted small mt-1';
    messageField.parentNode.appendChild(counterElement);

    function updateCharacterCounter() {
        const currentLength = messageField.value.length;
        const remaining = maxLength - currentLength;

        counterElement.textContent = `${currentLength}/${maxLength} characters`;

        if (remaining < 100) {
            counterElement.className = 'character-counter text-warning small mt-1';
        } else if (remaining < 20) {
            counterElement.className = 'character-counter text-danger small mt-1';
        } else {
            counterElement.className = 'character-counter text-muted small mt-1';
        }
    }

    messageField.addEventListener('input', updateCharacterCounter);
    updateCharacterCounter();

    document.getElementById('name').addEventListener('input', function() {
        const name = this.value.trim();
        if (name.length >= 2 && name.length <= 100) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            if (name.length > 0) {
                this.classList.add('is-invalid');
            }
        }
    });

    document.getElementById('email').addEventListener('input', function() {
        const email = this.value.trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (emailPattern.test(email)) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            if (email.length > 0) {
                this.classList.add('is-invalid');
            }
        }
    });

    document.getElementById('subject').addEventListener('input', function() {
        const subject = this.value.trim();
        if (subject.length >= 5 && subject.length <= 200) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            if (subject.length > 0) {
                this.classList.add('is-invalid');
            }
        }
    });

    messageField.addEventListener('input', function() {
        const message = this.value.trim();
        if (message.length >= 10 && message.length <= 2000) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            if (message.length > 0) {
                this.classList.add('is-invalid');
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>