<?php
/**
 * Admin Sponsorships API
 * CRUD operations for sponsorship packages
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

// GET - List all packages
if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM sponsorship_packages ORDER BY price DESC");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_success($packages);
}

// POST - Create new package
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    // Validate required fields
    if (empty($data['name']) || empty($data['description']) || !isset($data['price'])) {
        json_error('Missing required fields');
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO sponsorship_packages (name, description, perks_json, price, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['perks_json'] ?? null,
            $data['price'],
            $data['status'] ?? 'active'
        ]);
        
        json_success(['id' => $db->lastInsertId()]);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}

// PUT - Update existing package
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    // Validate required fields
    if (empty($data['id']) || empty($data['name']) || empty($data['description']) || !isset($data['price'])) {
        json_error('Missing required fields');
    }
    
    try {
        $stmt = $db->prepare("
            UPDATE sponsorship_packages 
            SET name = ?, description = ?, perks_json = ?, price = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['perks_json'] ?? null,
            $data['price'],
            $data['status'] ?? 'active',
            $data['id']
        ]);
        
        json_success(null);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}

// DELETE - Remove package
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    if (empty($data['id'])) {
        json_error('Package ID is required');
    }
    
    try {
        $stmt = $db->prepare("DELETE FROM sponsorship_packages WHERE id = ?");
        $stmt->execute([$data['id']]);
        
        json_success(null);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}
