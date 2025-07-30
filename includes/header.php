<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student Q&A System - Get help with your coursework from fellow students">
    <meta name="keywords" content="student, questions, answers, coursework, help, university">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Student Q&A System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a class="skip-link sr-only sr-only-focusable" href="#main-content">Skip to main content</a>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-question-circle-fill me-2"></i>
                Student Q&A
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                           href="index.php">
                            <i class="bi bi-house-fill me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'add_post.php' ? 'active' : ''; ?>" 
                           href="add_post.php">
                            <i class="bi bi-plus-circle-fill me-1"></i>Ask Question
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" 
                           aria-expanded="false">
                            <i class="bi bi-gear-fill me-1"></i>Manage
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="manage_users.php">
                                <i class="bi bi-people-fill me-2"></i>Users
                            </a></li>
                            <li><a class="dropdown-item" href="manage_modules.php">
                                <i class="bi bi-book-fill me-2"></i>Modules
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="contact_messages.php">
                                <i class="bi bi-envelope-fill me-2"></i>Contact Messages
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" 
                           href="contact.php">
                            <i class="bi bi-envelope-fill me-1"></i>Contact
                        </a>
                    </li>
                </ul>
                
                <!-- Search Form -->
                <form class="d-flex me-3" method="GET" action="search.php" role="search">
                    <input class="form-control me-2" type="search" name="q" placeholder="Search questions..." 
                           aria-label="Search" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button class="btn btn-outline-light" type="submit" aria-label="Search">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="main-content" class="container mt-4">
        <?php
        // Display success messages
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
            echo '<i class="bi bi-check-circle-fill me-2"></i>' . htmlspecialchars($_SESSION['success_message']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['success_message']);
        }
        
        // Display error messages
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>' . htmlspecialchars($_SESSION['error_message']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['error_message']);
        }
        ?>