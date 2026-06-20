<?php
/**
 * File: api/products/index.php
 * Location: /tour_update/api/products/index.php
 *
 * GET /api/products
 * Returns all active products from the database.
 * No authentication required (public endpoint).
 */

$db = get_db();

try {
    $stmt = $db->prepare("
        SELECT 
            id,
            name,
            description,
            price,
            sizes_json,
            stock,
            image_path,
            created_at
        FROM products
        WHERE status = 'active'
        ORDER BY id ASC
    ");
    
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    // Format the products for frontend
    $formatted_products = array_map(function($product) {
        return [
            'id' => (int)$product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => (float)$product['price'],
            'sizes' => json_decode($product['sizes_json'] ?? '[]', true),
            'stock' => (int)$product['stock'],
            'image_path' => $product['image_path'],
            'created_at' => $product['created_at']
        ];
    }, $products);
    
    json_success($formatted_products);
    
} catch (PDOException $e) {
    if (APP_ENV === 'local') {
        json_error('Database error: ' . $e->getMessage(), 500);
    } else {
        error_log('Products API error: ' . $e->getMessage());
        json_error('Unable to fetch products', 500);
    }
}
