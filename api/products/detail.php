<?php
/**
 * File: api/products/detail.php
 * Location: /tour_update/api/products/detail.php
 *
 * GET /api/products/{id}
 * Returns a single product by ID.
 * No authentication required (public endpoint).
 */

$product_id = $segments[1] ?? null;

if (!$product_id || !is_numeric($product_id)) {
    json_error('Invalid product ID', 400);
}

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
            status,
            created_at,
            updated_at
        FROM products
        WHERE id = :id AND status = 'active'
    ");
    
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        json_error('Product not found', 404);
    }
    
    // Format the product for frontend
    $formatted_product = [
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'description' => $product['description'],
        'price' => (float)$product['price'],
        'sizes' => json_decode($product['sizes_json'] ?? '[]', true),
        'stock' => (int)$product['stock'],
        'image_path' => $product['image_path'],
        'created_at' => $product['created_at'],
        'updated_at' => $product['updated_at']
    ];
    
    json_success($formatted_product);
    
} catch (PDOException $e) {
    if (APP_ENV === 'local') {
        json_error('Database error: ' . $e->getMessage(), 500);
    } else {
        error_log('Product detail API error: ' . $e->getMessage());
        json_error('Unable to fetch product', 500);
    }
}
