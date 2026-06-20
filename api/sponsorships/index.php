<?php
/**
 * File: api/sponsorships/index.php
 * Location: /tour_update/api/sponsorships/index.php
 *
 * GET /api/sponsorships
 * Returns all active sponsorship packages from the database.
 * No authentication required (public endpoint).
 */

$db = get_db();

try {
    $stmt = $db->prepare("
        SELECT 
            id,
            name,
            price,
            description,
            perks_json,
            created_at
        FROM sponsorship_packages
        WHERE status = 'active'
        ORDER BY price DESC
    ");
    
    $stmt->execute();
    $packages = $stmt->fetchAll();
    
    // Format the packages for frontend
    $formatted_packages = array_map(function($package) {
        return [
            'id' => (int)$package['id'],
            'name' => $package['name'],
            'price' => (float)$package['price'],
            'description' => $package['description'],
            'perks' => json_decode($package['perks_json'] ?? '[]', true),
            'created_at' => $package['created_at']
        ];
    }, $packages);
    
    json_success($formatted_packages);
    
} catch (PDOException $e) {
    if (APP_ENV === 'local') {
        json_error('Database error: ' . $e->getMessage(), 500);
    } else {
        error_log('Sponsorships API error: ' . $e->getMessage());
        json_error('Unable to fetch sponsorship packages', 500);
    }
}
