<?php
/**
 * File Upload Handler
 * Handles image uploads for products, events, and gallery
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/response.php';

// Start session and require admin
session_init();
require_admin(true);

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

// Validate CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    json_error('Invalid security token', 403);
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    json_error('No file uploaded', 400);
}

$file = $_FILES['image'];
$upload_type = $_POST['type'] ?? 'events'; // events, products, or gallery

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
    ];
    json_error($errors[$file['error']] ?? 'Unknown upload error', 400);
}

// Validate file size (5MB max)
$max_size = 5 * 1024 * 1024; // 5MB in bytes
if ($file['size'] > $max_size) {
    json_error('File size exceeds 5MB limit', 400);
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    json_error('Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed', 400);
}

// Validate upload type and set destination folder
$allowed_upload_types = ['events', 'products'];
if (!in_array($upload_type, $allowed_upload_types)) {
    json_error('Invalid upload type', 400);
}

$upload_dir = __DIR__ . '/../images/' . $upload_type . '/';

// Create directory if it doesn't exist
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        json_error('Failed to create upload directory', 500);
    }
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_' . date('Ymd_His') . '_') . '.' . strtolower($extension);
$destination = $upload_dir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    json_error('Failed to save uploaded file', 500);
}

// Return success with filename
json_success([
    'filename' => $filename,
    'url' => APP_URL . '/images/' . $upload_type . '/' . $filename,
    'size' => $file['size'],
    'type' => $mime_type
]);
