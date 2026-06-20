<?php
/**
 * Admin Products API
 * CRUD operations for products
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

// GET - List all products
if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_success($products);
}

// POST - Create new product
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    // Validate required fields
    if (empty($data['name']) || empty($data['description']) || !isset($data['price']) || !isset($data['stock'])) {
        json_error('Missing required fields');
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO products (name, description, price, stock, image_path, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['image_path'] ?? null,
            $data['status'] ?? 'active'
        ]);
        
        json_success(['id' => $db->lastInsertId()]);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}

// PUT - Update existing product
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    // Validate required fields
    if (empty($data['id']) || empty($data['name']) || empty($data['description']) || 
        !isset($data['price']) || !isset($data['stock'])) {
        json_error('Missing required fields');
    }
    
    try {
        // Get old image path if updating with new image
        if (!empty($data['image_path'])) {
            $stmt = $db->prepare("SELECT image_path FROM products WHERE id = ?");
            $stmt->execute([$data['id']]);
            $old_product = $stmt->fetch();
            
            // Delete old image if it's different from the new one
            if ($old_product && !empty($old_product['image_path']) && 
                $old_product['image_path'] !== $data['image_path']) {
                $old_image = __DIR__ . '/../../images/products/' . $old_product['image_path'];
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
        }
        
        $stmt = $db->prepare("
            UPDATE products 
            SET name = ?, description = ?, price = ?, stock = ?,
                image_path = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['image_path'] ?? null,
            $data['status'] ?? 'active',
            $data['id']
        ]);
        
        json_success(null);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}

// DELETE - Remove product
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    if (empty($data['id'])) {
        json_error('Product ID is required');
    }
    
    try {
        // Get image path before deleting
        $stmt = $db->prepare("SELECT image_path FROM products WHERE id = ?");
        $stmt->execute([$data['id']]);
        $product = $stmt->fetch();
        
        // Delete the database record
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$data['id']]);
        
        // Delete the image file if it exists
        if ($product && !empty($product['image_path'])) {
            $image_file = __DIR__ . '/../../images/products/' . $product['image_path'];
            if (file_exists($image_file)) {
                unlink($image_file);
            }
        }
        
        json_success(null);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}
