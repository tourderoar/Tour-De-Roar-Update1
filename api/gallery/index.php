<?php
/**
 * File: api/gallery/index.php
 * Location: /tour_update/api/gallery/index.php
 *
 * GET /api/gallery
 * Returns all gallery images from the database.
 * No authentication required (public endpoint).
 */

$db = get_db();

try {
    $stmt = $db->prepare("
        SELECT 
            id,
            filename,
            caption,
            sort_order,
            created_at
        FROM gallery_images
        ORDER BY sort_order ASC, created_at DESC
    ");
    
    $stmt->execute();
    $images = $stmt->fetchAll();
    
    // Format the images for frontend
    $formatted_images = array_map(function($image) {
        return [
            'id' => (int)$image['id'],
            'filename' => $image['filename'],
            'caption' => $image['caption'],
            'sort_order' => (int)$image['sort_order'],
            'url' => APP_URL . '/images/events/' . $image['filename'],
            'created_at' => $image['created_at']
        ];
    }, $images);
    
    json_success($formatted_images);
    
} catch (PDOException $e) {
    if (APP_ENV === 'local') {
        json_error('Database error: ' . $e->getMessage(), 500);
    } else {
        error_log('Gallery API error: ' . $e->getMessage());
        json_error('Unable to fetch gallery images', 500);
    }
}
