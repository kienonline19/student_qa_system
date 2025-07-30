</main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-question-circle-fill me-2"></i>Student Q&A System</h5>
                    <p class="mb-0">A platform for students to help each other with coursework questions.</p>
                    <small class="text-muted">Built with PHP, MySQL, and Bootstrap</small>
                </div>
                <div class="col-md-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light text-decoration-none">
                            <i class="bi bi-house me-1"></i>Home
                        </a></li>
                        <li><a href="add_post.php" class="text-light text-decoration-none">
                            <i class="bi bi-plus-circle me-1"></i>Ask Question
                        </a></li>
                        <li><a href="contact.php" class="text-light text-decoration-none">
                            <i class="bi bi-envelope me-1"></i>Contact Us
                        </a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Resources</h6>
                    <ul class="list-unstyled">
                        <li><a href="https://www.w3.org/WAI/WCAG21/quickref/" target="_blank" 
                               class="text-light text-decoration-none">
                            <i class="bi bi-universal-access me-1"></i>Accessibility Guidelines
                        </a></li>
                        <li><a href="https://ico.org.uk/for-organisations/guide-to-data-protection/" target="_blank" 
                               class="text-light text-decoration-none">
                            <i class="bi bi-shield-check me-1"></i>Privacy Policy
                        </a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small>&copy; <?php echo date('Y'); ?> Student Q&A System. All rights reserved.</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>
                        <i class="bi bi-code-slash me-1"></i>
                        Developed for COMP1841 Web Programming Assignment
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="assets/js/script.js"></script>
</body>
</html>

<?php
// Close any open database connections
if (isset($pdo)) {
    $pdo = null;
}
?>