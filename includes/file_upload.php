<?php
function handleImageUpload($file, $uploadDir = 'uploads/images/')
{

    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }


    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }


    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }


    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return false;
    }


    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $extension;
    $filepath = $uploadDir . $filename;


    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    }

    return false;
}

function deleteUploadedFile($filepath)
{
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return true;
}

function getFileExtension($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function formatFileSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $pow = floor(log($bytes) / log(1024));
    return round($bytes / (1024 ** $pow), 2) . ' ' . $units[$pow];
}

function validateImageDimensions($filepath, $maxWidth = 1920, $maxHeight = 1080)
{
    $imageInfo = getimagesize($filepath);
    if ($imageInfo === false) {
        return false;
    }

    return $imageInfo[0] <= $maxWidth && $imageInfo[1] <= $maxHeight;
}

function resizeImage($sourcePath, $destPath, $maxWidth = 800, $maxHeight = 600)
{
    $imageInfo = getimagesize($sourcePath);
    if ($imageInfo === false) {
        return false;
    }

    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];
    $mimeType = $imageInfo['mime'];


    $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
    $newWidth = intval($sourceWidth * $ratio);
    $newHeight = intval($sourceHeight * $ratio);


    $newImage = imagecreatetruecolor($newWidth, $newHeight);


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


    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);


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


    imagedestroy($sourceImage);
    imagedestroy($newImage);

    return $result;
}
?>