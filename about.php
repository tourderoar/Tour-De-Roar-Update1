<?php
/**
 * File: about.php
 * Location: /tour_update/about.php
 *
 * About Us page — tells the story of Tour de Roar.
 * Full content converted from about.html in Phase 2.
 */

$page_title = 'About Us';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center">
            <img src="<?= APP_URL ?>/images/logos/logo.jpeg" alt="Tour de Roar Lion Logo" class="h-32 w-auto mx-auto mb-6 lion-logo object-contain">
            <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">About Tour de Roar</h1>
            <p class="text-xl drop-shadow">Learn about our mission to support children through cycling</p>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="content-section bg-white">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-4xl font-bold mb-6" style="color: #FF6B1A;">Our Mission</h2>
                <p class="text-lg text-gray-700 mb-6 leading-relaxed">
                    Tour de Roar organizes charity cycling events to raise funds for indigent children worldwide. 
                    Founded with the belief that every child is our child, we're committed to spreading hope 
                    and making a positive impact in vulnerable communities.
                </p>
                <p class="text-lg text-gray-700 mb-6 leading-relaxed">
                    Through the power of cycling, we bring people together from all walks of life to support 
                    a common cause - ensuring that children in need have access to education, healthcare, 
                    nutrition, and hope for a brighter future.
                </p>
                <div class="flex space-x-4">
                    <a href="<?= APP_URL ?>/events" class="btn-primary">
                        <i class="fas fa-calendar mr-2"></i>Join Our Events
                    </a>
                    <a href="<?= APP_URL ?>/donate" class="btn-secondary">
                        <i class="fas fa-heart mr-2"></i>Support Our Cause
                    </a>
                </div>
            </div>
            <div>
                <div class="bg-gradient-to-br from-orange-100 to-red-100 p-8 rounded-xl shadow-lg">
                    <img src="<?= APP_URL ?>/images/logos/logo-white-version.png" alt="Tour de Roar Lion Logo" class="h-32 w-32 mx-auto mb-6 lion-logo">
                    <blockquote class="text-center">
                        <p class="text-xl font-semibold mb-4" style="color: #805AD5;">"Every child is our child"</p>
                        <p class="text-gray-700">This simple yet powerful statement drives everything we do at Tour de Roar.</p>
                    </blockquote>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How We Help Section -->
<section class="content-section bg-gradient-to-r from-blue-50 to-purple-50">
    <div class="container mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12" style="color: #3182CE;">How We Help</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white p-6 rounded-xl shadow-lg text-center border-t-4 hover:shadow-2xl transition-shadow duration-300" style="border-color: #FF6B1A;">
                <i class="fas fa-graduation-cap text-4xl mb-4" style="color: #FF6B1A;"></i>
                <h3 class="text-xl font-bold mb-4" style="color: #805AD5;">Education Support</h3>
                <p class="text-gray-700">Providing school supplies, uniforms, and educational resources to underprivileged children.</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg text-center border-t-4 hover:shadow-2xl transition-shadow duration-300" style="border-color: #3182CE;">
                <i class="fas fa-heartbeat text-4xl mb-4" style="color: #3182CE;"></i>
                <h3 class="text-xl font-bold mb-4" style="color: #805AD5;">Healthcare Initiatives</h3>
                <p class="text-gray-700">Supporting medical care, vaccinations, and health programs in vulnerable communities.</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg text-center border-t-4 hover:shadow-2xl transition-shadow duration-300" style="border-color: #68D391;">
                <i class="fas fa-utensils text-4xl mb-4" style="color: #68D391;"></i>
                <h3 class="text-xl font-bold mb-4" style="color: #805AD5;">Nutrition Programs</h3>
                <p class="text-gray-700">Funding school feeding programs and nutrition support for malnourished children.</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg text-center border-t-4 hover:shadow-2xl transition-shadow duration-300" style="border-color: #E53E3E;">
                <i class="fas fa-bicycle text-4xl mb-4" style="color: #E53E3E;"></i>
                <h3 class="text-xl font-bold mb-4" style="color: #805AD5;">Community Cycling</h3>
                <p class="text-gray-700">Building local cycling programs that promote health and community engagement.</p>
            </div>
        </div>
    </div>
</section>

<!-- Impact Statistics -->
<section class="content-section bg-white">
    <div class="container mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12" style="color: #805AD5;">Our Impact</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-orange-100 to-red-100 rounded-xl p-6 shadow-lg text-center hover:shadow-2xl transition-shadow duration-300">
                <i class="fas fa-child text-5xl mb-4" style="color: #FF6B1A;"></i>
                <h3 class="font-bold text-4xl mb-2" style="color: #805AD5;">500+</h3>
                <p class="text-gray-600 font-semibold">Children Supported</p>
                <p class="text-sm text-gray-500 mt-2">Direct beneficiaries of our programs</p>
            </div>
            
            <div class="bg-gradient-to-br from-blue-100 to-purple-100 rounded-xl p-6 shadow-lg text-center hover:shadow-2xl transition-shadow duration-300">
                <i class="fas fa-bicycle text-5xl mb-4" style="color: #3182CE;"></i>
                <h3 class="font-bold text-4xl mb-2" style="color: #805AD5;">25+</h3>
                <p class="text-gray-600 font-semibold">Cycling Events</p>
                <p class="text-sm text-gray-500 mt-2">Successful charity rides organized</p>
            </div>
            
            <div class="bg-gradient-to-br from-green-100 to-teal-100 rounded-xl p-6 shadow-lg text-center hover:shadow-2xl transition-shadow duration-300">
                <i class="fas fa-dollar-sign text-5xl mb-4" style="color: #68D391;"></i>
                <h3 class="font-bold text-4xl mb-2" style="color: #805AD5;">$50K+</h3>
                <p class="text-gray-600 font-semibold">Funds Raised</p>
                <p class="text-sm text-gray-500 mt-2">Total donations collected</p>
            </div>
            
            <div class="bg-gradient-to-br from-red-100 to-pink-100 rounded-xl p-6 shadow-lg text-center hover:shadow-2xl transition-shadow duration-300">
                <i class="fas fa-heart text-5xl mb-4" style="color: #E53E3E;"></i>
                <h3 class="font-bold text-4xl mb-2" style="color: #805AD5;">1000+</h3>
                <p class="text-gray-600 font-semibold">Lives Impacted</p>
                <p class="text-sm text-gray-500 mt-2">Including families and communities</p>
            </div>
        </div>
    </div>
</section>

<!-- Leadership Section -->
<section class="content-section bg-gradient-to-r from-purple-50 to-orange-50">
    <div class="container mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12" style="color: #805AD5;">Our Leadership</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-xl shadow-lg text-center hover:shadow-2xl transition-shadow duration-300">
                <div class="w-32 h-32 mx-auto mb-4 rounded-full overflow-hidden border-4" style="border-color: #FF6B1A;">
                    <img src="<?= APP_URL ?>/images/logos/founder.jpeg" alt="Paul Agbo" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-bold mb-2" style="color: #805AD5;">Dcn Paul Agbo</h3>
                <p class="text-gray-600 font-semibold mb-3">Founder & Executive Director</p>
                <p class="text-gray-700 text-sm">Passionate cyclist and advocate for children's rights. Paul founded Tour de Roar with the vision of using cycling to create positive social impact.</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg text-center hover:shadow-2xl transition-shadow duration-300">
                <div class="w-32 h-32 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                    <i class="fas fa-user text-white text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2" style="color: #805AD5;">Board of Directors</h3>
                <p class="text-gray-600 font-semibold mb-3">Volunteer Leadership</p>
                <p class="text-gray-700 text-sm">Dedicated volunteers who provide strategic guidance and oversight to ensure our mission's success.</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg text-center hover:shadow-2xl transition-shadow duration-300">
                <div class="w-32 h-32 mx-auto mb-4 rounded-full bg-gradient-to-br from-green-400 to-teal-500 flex items-center justify-center">
                    <i class="fas fa-users text-white text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2" style="color: #805AD5;">Volunteer Team</h3>
                <p class="text-gray-600 font-semibold mb-3">Community Champions</p>
                <p class="text-gray-700 text-sm">Amazing volunteers who help organize events, manage operations, and spread our message of hope.</p>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="content-section bg-white">
    <div class="container mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12" style="color: #805AD5;">Our Values</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-gradient-to-br from-orange-50 to-red-50 p-8 rounded-xl border-l-4 hover:shadow-lg transition-shadow duration-300" style="border-color: #FF6B1A;">
                <h3 class="text-2xl font-bold mb-4" style="color: #FF6B1A;">Compassion</h3>
                <p class="text-gray-700">We believe every child deserves love, care, and opportunity regardless of their circumstances.</p>
            </div>
            
            <div class="bg-gradient-to-br from-blue-50 to-purple-50 p-8 rounded-xl border-l-4 hover:shadow-lg transition-shadow duration-300" style="border-color: #3182CE;">
                <h3 class="text-2xl font-bold mb-4" style="color: #3182CE;">Unity</h3>
                <p class="text-gray-700">Through cycling, we bring diverse communities together for a common cause.</p>
            </div>
            
            <div class="bg-gradient-to-br from-green-50 to-teal-50 p-8 rounded-xl border-l-4 hover:shadow-lg transition-shadow duration-300" style="border-color: #68D391;">
                <h3 class="text-2xl font-bold mb-4" style="color: #68D391;">Transparency</h3>
                <p class="text-gray-700">We maintain open communication about how donations are used and impact achieved.</p>
            </div>
            
            <div class="bg-gradient-to-br from-red-50 to-pink-50 p-8 rounded-xl border-l-4 hover:shadow-lg transition-shadow duration-300" style="border-color: #E53E3E;">
                <h3 class="text-2xl font-bold mb-4" style="color: #E53E3E;">Sustainability</h3>
                <p class="text-gray-700">We focus on creating long-term positive change in communities we serve.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="content-section brand-gradient text-white">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-4xl font-bold mb-6">Join Our Mission</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            Ready to make a difference in children's lives? There are many ways to get involved with Tour de Roar.
        </p>
        <div class="space-x-4">
            <a href="<?= APP_URL ?>/events" class="bg-white text-purple-700 hover:bg-yellow-100 px-8 py-4 rounded-lg font-bold text-lg shadow-lg transition-all duration-300 transform hover:scale-105 inline-block">
                <i class="fas fa-calendar mr-2"></i>Join Events
            </a>
            <a href="<?= APP_URL ?>/sponsorship" class="bg-transparent border-3 border-white hover:bg-white hover:text-orange-600 text-white px-8 py-4 rounded-lg font-bold text-lg shadow-lg transition-all duration-300 inline-block">
                <i class="fas fa-handshake mr-2"></i>Become Sponsor
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
