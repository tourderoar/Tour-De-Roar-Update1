<?php
/**
 * File: index.php
 * Location: /tour_update/index.php
 *
 * Homepage for Tour de Roar — Welcome section, mission overview, impact stats, and CTA.
 */

$page_title = 'Cycling to Make a Difference';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section text-white py-20 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <div class="h-full w-full bg-gradient-to-br from-yellow-300 via-orange-300 to-red-300"></div>
    </div>
    <div class="container mx-auto px-6 text-center relative z-10">
        <div class="mb-6">
            <div class="h-48 w-48 bg-white bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-6 backdrop-blur-sm p-8">
                <img src="<?= APP_URL ?>/images/logos/logo-white-version.png" alt="Tour de Roar Lion Logo" class="h-full w-full object-contain lion-logo">
            </div>
        </div>
        <h1 class="text-6xl font-bold mb-6 drop-shadow-lg"><i class="fas fa-heart text-roar-red"></i> Tour de Roar</h1>
        <p class="text-2xl mb-8 font-semibold drop-shadow">Cycling to Make a Difference!</p>
        <p class="text-lg mb-8 max-w-2xl mx-auto drop-shadow">
            Join our charity cycling events to raise funds for indigent children worldwide.
            Every child is our child, and we're committed to spreading hope to vulnerable inner city children.
        </p>
        <div class="space-x-4">
            <button onclick="openRegistration()" class="btn-primary">
                <i class="fas fa-calendar mr-2"></i>Register for Event
            </button>
            <a href="<?= APP_URL ?>/donate" class="btn-secondary">
                <i class="fas fa-heart mr-2"></i>Donate Now
            </a>
        </div>
    </div>
</section>

<!-- Mission Overview Section -->
<section class="content-section bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold mb-6" style="color: #805AD5;">Our Mission</h2>
            <p class="text-xl text-gray-700 max-w-3xl mx-auto">
                Every child is our child. Through cycling, we bring communities together to support those who need it most.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6 bg-gradient-to-br from-orange-50 to-red-50 rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <i class="fas fa-bicycle text-5xl mb-4" style="color: #FF6B1A;"></i>
                <h3 class="text-2xl font-bold mb-4" style="color: #805AD5;">Charity Cycling</h3>
                <p class="text-gray-700">Organize impactful cycling events that bring communities together for a great cause.</p>
            </div>

            <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <i class="fas fa-child text-5xl mb-4" style="color: #3182CE;"></i>
                <h3 class="text-2xl font-bold mb-4" style="color: #805AD5;">Children First</h3>
                <p class="text-gray-700">Supporting indigent children worldwide through education, healthcare, and nutrition programs.</p>
            </div>

            <div class="text-center p-6 bg-gradient-to-br from-green-50 to-teal-50 rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <i class="fas fa-globe text-5xl mb-4" style="color: #68D391;"></i>
                <h3 class="text-2xl font-bold mb-4" style="color: #805AD5;">Global Impact</h3>
                <p class="text-gray-700">Making a difference in vulnerable communities around the world, one ride at a time.</p>
            </div>
        </div>
    </div>
</section>

<!-- Quick Stats Section -->
<section class="content-section bg-gradient-to-r from-purple-50 to-orange-50">
    <div class="container mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12" style="color: #805AD5;">Our Impact</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl p-6 shadow-lg text-center border-t-4 hover:shadow-2xl transition-shadow duration-300" style="border-color: #FF6B1A;">
                <i class="fas fa-child text-4xl mb-4" style="color: #FF6B1A;"></i>
                <h3 class="font-bold text-3xl mb-2" style="color: #805AD5;">500+</h3>
                <p class="text-gray-600">Children Supported</p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-lg text-center border-t-4 hover:shadow-2xl transition-shadow duration-300" style="border-color: #3182CE;">
                <i class="fas fa-bicycle text-4xl mb-4" style="color: #3182CE;"></i>
                <h3 class="font-bold text-3xl mb-2" style="color: #805AD5;">25+</h3>
                <p class="text-gray-600">Cycling Events</p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-lg text-center border-t-4 hover:shadow-2xl transition-shadow duration-300" style="border-color: #68D391;">
                <i class="fas fa-dollar-sign text-4xl mb-4" style="color: #68D391;"></i>
                <h3 class="font-bold text-3xl mb-2" style="color: #805AD5;">$50K+</h3>
                <p class="text-gray-600">Funds Raised</p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-lg text-center border-t-4 hover:shadow-2xl transition-shadow duration-300" style="border-color: #E53E3E;">
                <i class="fas fa-heart text-4xl mb-4" style="color: #E53E3E;"></i>
                <h3 class="font-bold text-3xl mb-2" style="color: #805AD5;">1000+</h3>
                <p class="text-gray-600">Lives Impacted</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="content-section bg-white">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-4xl font-bold mb-6" style="color: #805AD5;">Join Our Mission</h2>
        <p class="text-xl text-gray-700 mb-8 max-w-2xl mx-auto">
            Ready to make a difference? Whether you want to participate in our events, become a sponsor, or make a donation,
            every contribution helps us support children in need.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            <a href="<?= APP_URL ?>/events" class="bg-gradient-to-br from-orange-100 to-red-100 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4" style="border-color: #FF6B1A;">
                <i class="fas fa-calendar text-3xl mb-4" style="color: #FF6B1A;"></i>
                <h3 class="text-xl font-bold mb-2" style="color: #805AD5;">Join Events</h3>
                <p class="text-gray-700">Register for upcoming cycling events</p>
            </a>

            <a href="<?= APP_URL ?>/sponsorship" class="bg-gradient-to-br from-blue-100 to-purple-100 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4" style="border-color: #3182CE;">
                <i class="fas fa-handshake text-3xl mb-4" style="color: #3182CE;"></i>
                <h3 class="text-xl font-bold mb-2" style="color: #805AD5;">Become Sponsor</h3>
                <p class="text-gray-700">Partner with us for greater impact</p>
            </a>

            <a href="<?= APP_URL ?>/donate" class="bg-gradient-to-br from-green-100 to-teal-100 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4" style="border-color: #68D391;">
                <i class="fas fa-heart text-3xl mb-4" style="color: #68D391;"></i>
                <h3 class="text-xl font-bold mb-2" style="color: #805AD5;">Make Donation</h3>
                <p class="text-gray-700">Support children directly with a donation</p>
            </a>
        </div>
    </div>
</section>

<!-- Featured Events Section -->
<section class="content-section bg-gradient-to-br from-purple-50 to-orange-50">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold mb-4" style="color: #805AD5;">Upcoming Events</h2>
            <p class="text-xl text-gray-700 max-w-2xl mx-auto">
                Join us for exciting cycling events that make a difference in children's lives
            </p>
        </div>
        
        <!-- Loading Spinner -->
        <div id="featured-events-loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4" style="border-color: #FF6B1A;"></div>
            <p class="mt-4 text-gray-600 text-lg">Loading upcoming events...</p>
        </div>
        
        <!-- Error Message -->
        <div id="featured-events-error" class="hidden bg-red-50 border-l-4 border-red-500 p-6 rounded-lg max-w-2xl mx-auto">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-4"></i>
                <div>
                    <h3 class="font-bold text-red-800 mb-1">Unable to load events</h3>
                    <p class="text-red-700">Please try refreshing the page or visit our events page directly.</p>
                </div>
            </div>
        </div>
        
        <!-- Featured Events Container (populated by AJAX) -->
        <div id="featured-events-container" class="grid grid-cols-1 md:grid-cols-3 gap-8"></div>
        
        <div class="text-center mt-12">
            <a href="<?= APP_URL ?>/events" class="inline-block bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold py-3 px-8 rounded-lg hover:shadow-lg transition-shadow">
                View All Events <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Registration Modal (placeholder — will be replaced with real payment flow in Phase 6) -->
<div id="registrationModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('registrationModal')">&times;</span>
        <h2 class="text-2xl font-bold mb-6" style="color: #805AD5;">Event Registration</h2>
        <form id="registrationForm" onsubmit="processRegistration(event)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Full Name *</label>
                    <input type="text" id="reg-name" required class="w-full px-4 py-3 border rounded-lg" style="border-color: #68D391;">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Email *</label>
                    <input type="email" id="reg-email" required class="w-full px-4 py-3 border rounded-lg" style="border-color: #68D391;">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Phone *</label>
                    <input type="tel" id="reg-phone" required class="w-full px-4 py-3 border rounded-lg" style="border-color: #68D391;">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Emergency Contact *</label>
                    <input type="tel" id="reg-emergency" required class="w-full px-4 py-3 border rounded-lg" style="border-color: #68D391;">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Event Selection *</label>
                <select id="reg-event" required class="w-full px-4 py-3 border rounded-lg" style="border-color: #68D391;">
                    <option value="">Select an Event</option>
                    <option value="spring">Spring Charity Ride 2025 - $35</option>
                    <option value="summer">Summer Challenge 2025 - $50</option>
                    <option value="community">Monthly Community Ride - $15</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" id="reg-waiver" required class="mr-2">
                    <span class="text-sm text-gray-700">I agree to the liability waiver and terms of participation *</span>
                </label>
            </div>
            <button type="submit" class="w-full py-3 rounded-lg font-bold text-white shadow-lg" style="background: linear-gradient(45deg, #FF6B1A, #E53E3E, #805AD5);">
                <i class="fas fa-credit-card mr-2"></i>Proceed to Payment
            </button>
        </form>
    </div>
</div>

<script src="<?= APP_URL ?>/js/phase4-ajax.js"></script>
<script>
$(document).ready(function() {
    loadFeaturedEvents();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
