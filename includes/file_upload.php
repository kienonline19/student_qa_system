<?php
/**
 * File Upload Helper Functions
 */

/**
 * Handle image upload for posts
 * @param array $file $_FILES array element
 * @param string $uploadDir Upload directory
 * @return string|false File path on success, false on failure
 */
function handleImageUpload($file, $uploadDir = 'uploads/images/') {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }
    
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    }
    
    return false;
}

/**
 * Delete uploaded file
 * @param string $filepath File path to delete
 * @return bool Success status
 */
function deleteUploadedFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return true;
}

/**
 * Get file extension
 * @param string $filename Filename
 * @return string File extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Format file size for display
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $pow = floor(log($bytes) / log(1024));
    return round($bytes / (1024 ** $pow), 2) . ' ' . $units[$pow];
}

/**
 * Validate image dimensions
 * @param string $filepath Image file path
 * @param int $maxWidth Maximum width
 * @param int $maxHeight Maximum height
 * @return bool True if dimensions are valid
 */
function validateImageDimensions($filepath, $maxWidth = 1920, $maxHeight = 1080) {
    $imageInfo = getimagesize($filepath);
    if ($imageInfo === false) {
        return false;
    }
    
    return $imageInfo[0] <= $maxWidth && $imageInfo[1] <= $maxHeight;
}

/**
 * Resize image if needed
 * @param string $sourcePath Source image path
 * @param string $destPath Destination image path
 * @param int $maxWidth Maximum width
 * @param int $maxHeight Maximum height
 * @return bool Success status
 */
function resizeImage($sourcePath, $destPath, $maxWidth = 800, $maxHeight = 600) {
    $imageInfo = getimagesize($sourcePath);
    if ($imageInfo === false) {
        return false;
    }
    
    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];
    $mimeType = $imageInfo['mime'];
    
    // Calculate new dimensions
    $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
    $newWidth = intval($sourceWidth * $ratio);
    $newHeight = intval($sourceHeight * $ratio);
    
    // Create new image resource
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Create source image resource based on type
    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($sourcePath);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }
    
    // Resize image
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
    
    // Save resized image
    $result = false;
    switch ($mimeType) {
        case 'image/jpeg':
            $result = imagejpeg($newImage, $destPath, 85);
            break;
        case 'image/png':
            $result = imagepng($newImage, $destPath);
            break;
        case 'image/gif':
            $result = imagegif($newImage, $destPath);
            break;
    }
    
    // Clean up memory
    imagedestroy($sourceImage);
    imagedestroy($newImage);
    
    return $result;
}
?>