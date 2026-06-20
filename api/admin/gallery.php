<?php
/**
 * Admin Gallery API
 * CRUD operations for gallery images
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/middleware.php';

// Start session and require admin authentication
session_init();
require_admin();

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'];

// GET - List all images
if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM gallery_images ORDER BY created_at DESC");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_success($images);
}

// POST - Add new image
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    // Validate required fields
    if (empty($data['filename'])) {
        json_error('Missing required fields');
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO gallery_images (filename, caption, created_at)
            VALUES (?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['filename'],
            $data['caption'] ?? null
        ]);
        
        json_success(['id' => $db->lastInsertId()]);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}

// PUT - Update existing image
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    // Validate required fields
    if (empty($data['id']) || empty($data['filename'])) {
        json_error('Missing required fields');
    }
    
    try {
        // Get old filename if updating with new image
        $stmt = $db->prepare("SELECT filename FROM gallery_images WHERE id = ?");
        $stmt->execute([$data['id']]);
        $old_image = $stmt->fetch();
        
        // Delete old image if it's different from the new one
        if ($old_image && !empty($old_image['filename']) && 
            $old_image['filename'] !== $data['filename']) {
            $old_file = __DIR__ . '/../../images/events/' . $old_image['filename'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        
        $stmt = $db->prepare("
            UPDATE gallery_images 
            SET filename = ?, caption = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['filename'],
            $data['caption'] ?? null,
            $data['id']
        ]);
        
        json_success(null);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}

// DELETE - Remove image
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    if (empty($data['id'])) {
        json_error('Image ID is required');
    }
    
    try {
        // Get filename before deleting
        $stmt = $db->prepare("SELECT filename FROM gallery_images WHERE id = ?");
        $stmt->execute([$data['id']]);
        $image = $stmt->fetch();
        
        // Delete the database record
        $stmt = $db->prepare("DELETE FROM gallery_images WHERE id = ?");
        $stmt->execute([$data['id']]);
        
        // Delete the image file if it exists
        if ($image && !empty($image['filename'])) {
            $image_file = __DIR__ . '/../../images/events/' . $image['filename'];
            if (file_exists($image_file)) {
                unlink($image_file);
            }
        }
        
        json_success(null);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}
