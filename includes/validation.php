<?php
/**
 * Validation Helper Functions
 */

/**
 * Sanitize input data
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 * @param string $email Email address
 * @return bool True if valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate string length
 * @param string $string String to validate
 * @param int $minLength Minimum length
 * @param int $maxLength Maximum length
 * @return bool True if valid
 */
function validateLength($string, $minLength, $maxLength) {
    $length = strlen($string);
    return $length >= $minLength && $length <= $maxLength;
}

/**
 * Validate required field
 * @param mixed $value Value to check
 * @return bool True if not empty
 */
function validateRequired($value) {
    return !empty(trim($value));
}

/**
 * Validate post data
 * @param array $data Post data
 * @return array Validation result [success => bool, errors => array]
 */
function validatePostData($data) {
    $errors = [];
    
    // Validate title
    if (!validateRequired($data['title'])) {
        $errors[] = "Title is required";
    } elseif (!validateLength($data['title'], 5, 200)) {
        $errors[] = "Title must be between 5 and 200 characters";
    }
    
    // Validate content
    if (!validateRequired($data['content'])) {
        $errors[] = "Content is required";
    } elseif (!validateLength($data['content'], 10, 5000)) {
        $errors[] = "Content must be between 10 and 5000 characters";
    }
    
    // Validate user_id
    if (!validateRequired($data['user_id']) || !is_numeric($data['user_id'])) {
        $errors[] = "Valid user selection is required";
    }
    
    // Validate module_id
    if (!validateRequired($data['module_id']) || !is_numeric($data['module_id'])) {
        $errors[] = "Valid module selection is required";
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate user data
 * @param array $data User data
 * @param int|null $excludeUserId User ID to exclude from uniqueness check
 * @return array Validation result [success => bool, errors => array]
 */
function validateUserData($data, $excludeUserId = null) {
    $errors = [];
    
    // Validate username
    if (!validateRequired($data['username'])) {
        $errors[] = "Username is required";
    } elseif (!validateLength($data['username'], 3, 50)) {
        $errors[] = "Username must be between 3 and 50 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
        $errors[] = "Username can only contain letters, numbers, and underscores";
    } else {
        // Check if username already exists
        require_once 'modules/users.php';
        if (usernameExists($data['username'], $excludeUserId)) {
            $errors[] = "Username already exists";
        }
    }
    
    // Validate email
    if (!validateRequired($data['email'])) {
        $errors[] = "Email is required";
    } elseif (!validateEmail($data['email'])) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        require_once 'modules/users.php';
        if (emailExists($data['email'], $excludeUserId)) {
            $errors[] = "Email already exists";
        }
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate module data
 * @param array $data Module data
 * @param int|null $excludeModuleId Module ID to exclude from uniqueness check
 * @return array Validation result [success => bool, errors => array]
 */
function validateModuleData($data, $excludeModuleId = null) {
    $errors = [];
    
    // Validate module code
    if (!validateRequired($data['module_code'])) {
        $errors[] = "Module code is required";
    } elseif (!validateLength($data['module_code'], 2, 20)) {
        $errors[] = "Module code must be between 2 and 20 characters";
    } elseif (!preg_match('/^[A-Z0-9]+$/', $data['module_code'])) {
        $errors[] = "Module code can only contain uppercase letters and numbers";
    } else {
        // Check if module code already exists
        require_once 'modules/modules.php';
        if (moduleCodeExists($data['module_code'], $excludeModuleId)) {
            $errors[] = "Module code already exists";
        }
    }
    
    // Validate module name
    if (!validateRequired($data['module_name'])) {
        $errors[] = "Module name is required";
    } elseif (!validateLength($data['module_name'], 5, 100)) {
        $errors[] = "Module name must be between 5 and 100 characters";
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate contact form data
 * @param array $data Contact form data
 * @return array Validation result [success => bool, errors => array]
 */
function validateContactData($data) {
    $errors = [];
    
    // Validate name
    if (!validateRequired($data['name'])) {
        $errors[] = "Name is required";
    } elseif (!validateLength($data['name'], 2, 100)) {
        $errors[] = "Name must be between 2 and 100 characters";
    }
    
    // Validate email
    if (!validateRequired($data['email'])) {
        $errors[] = "Email is required";
    } elseif (!validateEmail($data['email'])) {
        $errors[] = "Invalid email format";
    }
    
    // Validate subject
    if (!validateRequired($data['subject'])) {
        $errors[] = "Subject is required";
    } elseif (!validateLength($data['subject'], 5, 200)) {
        $errors[] = "Subject must be between 5 and 200 characters";
    }
    
    // Validate message
    if (!validateRequired($data['message'])) {
        $errors[] = "Message is required";
    } elseif (!validateLength($data['message'], 10, 2000)) {
        $errors[] = "Message must be between 10 and 2000 characters";
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verifyCSRFToken($token) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>