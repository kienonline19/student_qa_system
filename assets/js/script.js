/**
 * Student Q&A System JavaScript
 * Handles form validation, image uploads, and UI interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize file upload handling
    initializeFileUpload();
    
    // Initialize confirmation dialogs
    initializeConfirmationDialogs();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize character counters
    initializeCharacterCounters();
});

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Focus on first invalid field
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
            
            form.classList.add('was-validated');
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(function(input) {
            input.addEventListener('blur', function() {
                validateField(input);
            });
            
            input.addEventListener('input', function() {
                if (input.classList.contains('is-invalid')) {
                    validateField(input);
                }
            });
        });
    });
}

/**
 * Validate individual field
 */
function validateField(field) {
    const isValid = field.checkValidity();
    
    field.classList.remove('is-valid', 'is-invalid');
    
    if (isValid) {
        field.classList.add('is-valid');
    } else {
        field.classList.add('is-invalid');
    }
    
    // Custom validation messages
    updateValidationMessage(field);
}

/**
 * Update validation message
 */
function updateValidationMessage(field) {
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (!feedback) return;
    
    if (field.validity.valueMissing) {
        feedback.textContent = `${getFieldLabel(field)} is required.`;
    } else if (field.validity.typeMismatch) {
        feedback.textContent = `Please enter a valid ${field.type}.`;
    } else if (field.validity.tooShort) {
        feedback.textContent = `${getFieldLabel(field)} must be at least ${field.minLength} characters.`;
    } else if (field.validity.tooLong) {
        feedback.textContent = `${getFieldLabel(field)} must be no more than ${field.maxLength} characters.`;
    } else if (field.validity.patternMismatch) {
        feedback.textContent = getPatternMessage(field);
    }
}

/**
 * Get field label for validation messages
 */
function getFieldLabel(field) {
    const label = document.querySelector(`label[for="${field.id}"]`);
    return label ? label.textContent.replace(':', '').replace('*', '') : 'This field';
}

/**
 * Get pattern-specific validation message
 */
function getPatternMessage(field) {
    if (field.name === 'username') {
        return 'Username can only contain letters, numbers, and underscores.';
    } else if (field.name === 'module_code') {
        return 'Module code can only contain uppercase letters and numbers.';
    }
    return 'Please enter a valid format.';
}

/**
 * Initialize file upload handling
 */
function initializeFileUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(function(input) {
        const dropArea = input.closest('.file-upload-area');
        if (!dropArea) return;
        
        // Drag and drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        
        dropArea.addEventListener('drop', handleDrop, false);
        
        // File input change event
        input.addEventListener('change', function() {
            handleFiles(this.files, input);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        function highlight() {
            dropArea.classList.add('dragover');
        }
        
        function unhighlight() {
            dropArea.classList.remove('dragover');
        }
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            input.files = files;
            handleFiles(files, input);
        }
    });
}

/**
 * Handle file selection
 */
function handleFiles(files, input) {
    if (files.length === 0) return;
    
    const file = files[0];
    const preview = input.parentNode.querySelector('.file-preview');
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        showAlert('Please select a valid image file.', 'danger');
        input.value = '';
        return;
    }
    
    // Validate file size (5MB max)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        showAlert('File size must be less than 5MB.', 'danger');
        input.value = '';
        return;
    }
    
    // Show preview
    if (preview && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                <p class="mt-2 mb-0 text-muted">${file.name} (${formatFileSize(file.size)})</p>
            `;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

/**
 * Format file size for display
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Initialize confirmation dialogs
 */
function initializeConfirmationDialogs() {
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm-delete') || 'Are you sure you want to delete this item?';
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

/**
 * Initialize search functionality
 */
function initializeSearch() {
    const searchForm = document.querySelector('form[action="search.php"]');
    const searchInput = searchForm ? searchForm.querySelector('input[name="q"]') : null;
    
    if (!searchInput) return;
    
    // Add search suggestions (basic implementation)
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            // Could implement search suggestions here
        }, 300);
    });
    
    // Clear search
    if (searchInput.value) {
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'btn btn-sm btn-outline-secondary';
        clearButton.innerHTML = '<i class="bi bi-x"></i>';
        clearButton.title = 'Clear search';
        
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
        });
        
        searchInput.parentNode.appendChild(clearButton);
    }
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize character counters
 */
function initializeCharacterCounters() {
    const textareas = document.querySelectorAll('textarea[maxlength]');
    
    textareas.forEach(function(textarea) {
        const maxLength = parseInt(textarea.getAttribute('maxlength'));
        const counter = document.createElement('div');
        counter.className = 'character-counter text-muted small mt-1';
        
        function updateCounter() {
            const currentLength = textarea.value.length;
            const remaining = maxLength - currentLength;
            
            counter.textContent = `${currentLength}/${maxLength} characters`;
            
            if (remaining < 50) {
                counter.className = 'character-counter text-warning small mt-1';
            } else if (remaining < 10) {
                counter.className = 'character-counter text-danger small mt-1';
            } else {
                counter.className = 'character-counter text-muted small mt-1';
            }
        }
        
        textarea.parentNode.appendChild(counter);
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info', duration = 5000) {
    const alertContainer = document.querySelector('.container');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="bi bi-${getAlertIcon(type)} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    alertContainer.insertBefore(alert, alertContainer.firstChild);
    
    // Auto-dismiss after duration
    if (duration > 0) {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, duration);
    }
}

/**
 * Get alert icon based on type
 */
function getAlertIcon(type) {
    const icons = {
        'success': 'check-circle-fill',
        'danger': 'exclamation-triangle-fill',
        'warning': 'exclamation-triangle-fill',
        'info': 'info-circle-fill'
    };
    return icons[type] || 'info-circle-fill';
}

/**
 * Utility function to escape HTML
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Utility function to format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Loading state management
 */
function setLoadingState(element, loading = true) {
    if (loading) {
        element.classList.add('loading');
        element.disabled = true;
        
        const spinner = element.querySelector('.spinner-border');
        if (!spinner) {
            const icon = element.querySelector('i');
            if (icon) {
                icon.className = 'spinner-border spinner-border-sm me-2';
            }
        }
    } else {
        element.classList.remove('loading');
        element.disabled = false;
        
        const spinner = element.querySelector('.spinner-border');
        if (spinner) {
            spinner.className = 'bi bi-check me-2';
        }
    }
}

/**
 * AJAX form submission helper
 */
function submitFormAjax(form, callback) {
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    setLoadingState(submitButton, true);
    
    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        setLoadingState(submitButton, false);
        if (callback) callback(data);
    })
    .catch(error => {
        setLoadingState(submitButton, false);
        showAlert('An error occurred. Please try again.', 'danger');
        console.error('Form submission error:', error);
    });
}

/**
 * Copy to clipboard functionality
 */
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showAlert('Copied to clipboard!', 'success', 2000);
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
            showAlert('Copied to clipboard!', 'success', 2000);
        } catch (err) {
            showAlert('Failed to copy to clipboard.', 'danger');
        }
        document.body.removeChild(textArea);
    }
}