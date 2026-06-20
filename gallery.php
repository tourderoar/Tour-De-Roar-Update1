<?php
/**
 * File: gallery.php
 * Location: /tour_update/gallery.php
 *
 * Photo gallery page.
 * In Phase 7, this will load images dynamically from the API.
 */

$page_title = 'Photo Gallery';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center">
            <i class="fas fa-images text-6xl mb-6 drop-shadow-lg"></i>
            <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">Photo Gallery</h1>
            <p class="text-xl drop-shadow">Moments from our cycling events and community impact</p>
        </div>
    </div>
</section>

<!-- Gallery Section (Loaded from API) -->
<section class="content-section bg-white">
    <div class="container mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12" style="color: #805AD5;">Event Highlights</h2>
        
        <!-- Loading Spinner -->
        <div id="gallery-loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4" style="border-color: #805AD5;"></div>
            <p class="mt-4 text-gray-600 text-lg">Loading gallery...</p>
        </div>
        
        <!-- Error Message -->
        <div id="gallery-error" class="hidden bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-4"></i>
                <div>
                    <h3 class="font-bold text-red-800 mb-1">Unable to load gallery</h3>
                    <p class="text-red-700">Please try refreshing the page. If the problem persists, contact us.</p>
                </div>
            </div>
        </div>
        
        <!-- Gallery Container (populated by AJAX) -->
        <div id="gallery-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
    </div>
</section>

<script>
// Load gallery images from API
$(document).ready(function() {
    $.ajax({
        url: APP_URL + '/api/gallery',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#gallery-loading').hide();
            
            if (response.success && response.data.length > 0) {
                const colors = ['#FF6B1A', '#3182CE', '#805AD5', '#68D391', '#E53E3E', '#F6E05E'];
                
                response.data.forEach((image, index) => {
                    const color = colors[index % colors.length];
                    
                    const card = `
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                            <div class="h-64 overflow-hidden">
                                <img src="${image.url}" alt="${image.caption}" class="gallery-img hover:scale-110 transition-transform duration-300">
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-lg mb-2" style="color: ${color};">${image.caption}</h3>
                            </div>
                        </div>
                    `;
                    
                    $('#gallery-container').append(card);
                });
            } else {
                $('#gallery-container').html('<div class="col-span-full text-center py-12"><p class="text-gray-600 text-lg">No gallery images available at this time.</p></div>');
            }
        },
        error: function() {
            $('#gallery-loading').hide();
            $('#gallery-error').removeClass('hidden');
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
