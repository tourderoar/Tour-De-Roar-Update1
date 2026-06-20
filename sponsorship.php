<?php
/**
 * File: sponsorship.php
 * Location: /tour_update/sponsorship.php
 *
 * Sponsorship opportunities page.
 * In Phase 3, this will integrate with sponsorship payment API.
 */

$page_title = 'Sponsorship Opportunities';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="page-header">
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center">
            <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">Partner with Tour de Roar</h1>
            <p class="text-xl mb-8 drop-shadow">Join us in making a difference in children's lives while growing your business</p>
            <div class="flex justify-center gap-4">
                <a href="#packages" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-3 px-8 rounded-lg transition-colors shadow-lg">
                    View Packages
                </a>
                <a href="<?= APP_URL ?>/contact" class="bg-transparent border-2 border-white hover:bg-white hover:text-purple-600 text-white font-bold py-3 px-8 rounded-lg transition-colors">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Why Sponsor Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Partner With Tour de Roar?</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Your sponsorship creates meaningful impact while providing valuable brand exposure and community engagement opportunities.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-blue-50 p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition-shadow">
                <div class="text-blue-600 text-5xl mb-4">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Make Real Impact</h3>
                <p class="text-gray-600">
                    Every dollar you contribute directly supports programs that improve children's physical and mental health through cycling.
                </p>
            </div>

            <div class="bg-green-50 p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition-shadow">
                <div class="text-green-600 text-5xl mb-4">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Community Engagement</h3>
                <p class="text-gray-600">
                    Connect with passionate cyclists and families who value health, community, and giving back.
                </p>
            </div>

            <div class="bg-purple-50 p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition-shadow">
                <div class="text-purple-600 text-5xl mb-4">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Brand Visibility</h3>
                <p class="text-gray-600">
                    Gain exposure through our events, website, social media, and promotional materials reaching thousands.
                </p>
            </div>

            <div class="bg-yellow-50 p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition-shadow">
                <div class="text-yellow-600 text-5xl mb-4">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Recognition</h3>
                <p class="text-gray-600">
                    Receive public acknowledgment for your commitment to improving children's health and community wellness.
                </p>
            </div>

            <div class="bg-red-50 p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition-shadow">
                <div class="text-red-600 text-5xl mb-4">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Partnership Benefits</h3>
                <p class="text-gray-600">
                    Access exclusive networking opportunities and build relationships with other community-minded businesses.
                </p>
            </div>

            <div class="bg-gray-100 p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition-shadow">
                <div class="text-gray-600 text-5xl mb-4">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Marketing ROI</h3>
                <p class="text-gray-600">
                    Demonstrate corporate social responsibility while reaching engaged, health-conscious audiences.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Sponsorship Packages Section (Phase 3: will process payments via API) -->
<section id="packages" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Sponsorship Packages</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Choose the sponsorship level that aligns with your goals and budget. All packages support our mission to improve children's health.
            </p>
        </div>

        <!-- Loading Spinner -->
        <div id="sponsorships-loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4" style="border-color: #805AD5;"></div>
            <p class="mt-4 text-gray-600 text-lg">Loading sponsorship packages...</p>
        </div>
        
        <!-- Error Message -->
        <div id="sponsorships-error" class="hidden bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-4"></i>
                <div>
                    <h3 class="font-bold text-red-800 mb-1">Unable to load sponsorship packages</h3>
                    <p class="text-red-700">Please try refreshing the page or contact us at (972) 979-4608.</p>
                </div>
            </div>
        </div>
        
        <!-- Sponsorships Container (populated by AJAX) -->
        <div id="sponsorships-container" class="grid grid-cols-1 lg:grid-cols-3 gap-8"></div>
    </div>
</section>

<!-- Impact Statement -->
<section class="py-16 bg-blue-600 text-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold mb-8">Your Sponsorship Creates Lasting Impact</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div>
                <div class="text-4xl font-bold text-yellow-300">500+</div>
                <p class="text-xl">Children served annually</p>
            </div>
            <div>
                <div class="text-4xl font-bold text-yellow-300">15+</div>
                <p class="text-xl">Community events hosted</p>
            </div>
            <div>
                <div class="text-4xl font-bold text-yellow-300">$50K</div>
                <p class="text-xl">Raised for children's programs</p>
            </div>
        </div>
        <p class="text-xl mb-8">
            When you sponsor Tour de Roar, you're not just supporting cycling events – you're investing in children's health, 
            building stronger communities, and creating opportunities for families to stay active together.
        </p>
        <a href="<?= APP_URL ?>/contact" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-3 px-8 rounded-lg transition-colors inline-block">
            Start Your Partnership Today
        </a>
    </div>
</section>

<!-- Custom Sponsorship Solutions Section -->
<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Custom Sponsorship Solutions</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Don't see a package that fits your needs? We're happy to work with you to create a custom 
                sponsorship opportunity that aligns with your business goals and our mission.
            </p>
        </div>

        <div class="bg-gray-50 rounded-xl shadow-lg p-8 md:p-12">
            <h3 class="text-2xl font-bold text-center text-gray-900 mb-8">We Can Customize:</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
                <!-- Activation Opportunities -->
                <div>
                    <h4 class="text-xl font-bold text-purple-600 mb-4">Activation Opportunities</h4>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Product demonstrations</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Interactive brand experiences</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Educational workshops</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Community challenges</span>
                        </li>
                    </ul>
                </div>

                <!-- Recognition Benefits -->
                <div>
                    <h4 class="text-xl font-bold text-orange-600 mb-4">Recognition Benefits</h4>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Branded course elements</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Speaking opportunities</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Award presentations</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Media interviews</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="text-center mt-10">
                <a href="<?= APP_URL ?>/contact" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition-colors inline-block shadow-lg">
                    Discuss Custom Options
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Sponsorship Payment Modal -->
<div id="sponsorship-modal" class="payment-modal-overlay" style="display: none;">
    <div class="payment-modal-content">
        <div class="payment-modal-header">
            <h2 id="modal-title" class="payment-modal-title">
                <i class="fas fa-handshake mr-2"></i>Become a Sponsor
            </h2>
            <button type="button" class="payment-modal-close" onclick="closeSponsorshipModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="payment-modal-body">
            <div id="modal-success" class="alert-success" style="display: none;">
                <i class="fas fa-check-circle mr-2"></i>
                <span id="success-message"></span>
            </div>
            
            <div id="modal-error" class="alert-error" style="display: none;">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span id="error-message"></span>
            </div>
            
            <form id="sponsorship-form">
                <input type="hidden" id="package-id" name="package_id">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <!-- Sponsorship Summary -->
                <div class="payment-summary">
                    <h3 style="color: #805AD5; font-weight: 700; margin-bottom: 0.75rem;">Sponsorship Package</h3>
                    <div class="payment-summary-row">
                        <span id="package-name-label">-</span>
                        <strong id="package-amount" style="color: #3182CE; font-size: 1.5rem;">$0.00</strong>
                    </div>
                </div>
                
                <!-- Company Information -->
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: #805AD5; font-weight: 700; margin-bottom: 0.75rem;">Company Information</h3>
                    
                    <div class="form-group">
                        <label>Company/Organization Name *</label>
                        <input type="text" id="company_name" name="company_name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Contact Email *</label>
                        <input type="email" id="contact_email" name="contact_email" class="form-input" required>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: #805AD5; font-weight: 700; margin-bottom: 0.75rem;">Payment Information</h3>
                    <div style="background: #EBF8FF; border-left: 4px solid #3182CE; padding: 12px 16px; margin-bottom: 1rem; border-radius: 4px;">
                        <div style="display: flex; align-items: start; gap: 8px;">
                            <i class="fas fa-shield-alt" style="color: #3182CE; margin-top: 2px;"></i>
                            <div style="font-size: 0.875rem; color: #2C5282; line-height: 1.5;">
                                <strong>Secure Payment:</strong> Your card details are encrypted and processed securely by Stripe. 
                                We never store or have access to your card information.
                            </div>
                        </div>
                    </div>
                    <div id="sponsor-card-element" class="stripe-card-element"></div>
                    <div id="sponsor-card-errors" class="stripe-card-errors"></div>
                </div>
                
                <button type="submit" id="submit-sponsorship" class="payment-submit-btn">
                    <i class="fas fa-lock mr-2"></i>Complete Sponsorship
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.payment-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.payment-modal-content {
    background: white;
    border-radius: 16px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.payment-modal-header {
    background: linear-gradient(135deg, #805AD5 0%, #E53E3E 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 16px 16px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.payment-modal-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

.payment-modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    line-height: 1;
    transition: transform 0.2s;
}

.payment-modal-close:hover {
    transform: scale(1.1);
}

.payment-modal-body {
    padding: 2rem;
}

.payment-summary {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-left: 4px solid #805AD5;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.payment-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2d3748;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #805AD5;
}

.stripe-card-element {
    padding: 0.75rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
}

.stripe-card-errors {
    color: #E53E3E;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    min-height: 20px;
}

.payment-submit-btn {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #805AD5 0%, #E53E3E 100%);
    color: white;
    font-weight: 700;
    font-size: 1.125rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.payment-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(128, 90, 213, 0.3);
}

.payment-submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.alert-success {
    background: #f0fdf4;
    border-left: 4px solid #68D391;
    color: #065f46;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background: #fee2e2;
    border-left: 4px solid #E53E3E;
    color: #991b1b;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}
</style>

<script src="<?= APP_URL ?>/js/phase4-ajax.js"></script>
<script>
// Initialize Stripe for sponsorships
const sponsorStripe = Stripe(STRIPE_PK);
const sponsorElements = sponsorStripe.elements();
let sponsorCardElement = null;

function processSponsorPayment(pkgId, pkgName, price) {
    // Check if user is logged in
    if (!IS_LOGGED_IN) {
        window.location.href = APP_URL + '/account/login?redirect=sponsorship';
        return;
    }
    
    // Populate modal
    document.getElementById('package-id').value = pkgId;
    document.getElementById('modal-title').innerHTML = '<i class="fas fa-handshake mr-2"></i>' + pkgName;
    document.getElementById('package-name-label').textContent = pkgName;
    document.getElementById('package-amount').textContent = '$' + parseFloat(price).toLocaleString();
    
    // Pre-fill with user data if available
    const user = <?= json_encode(get_logged_in_user() ?? []); ?>;
    if (user.email) {
        const emailField = document.getElementById('contact_email');
        emailField.value = user.email;
        emailField.readOnly = true;
        emailField.style.backgroundColor = '#f7fafc';
        emailField.style.cursor = 'not-allowed';
    }
    
    // Create Stripe card element if not already created
    if (!sponsorCardElement) {
        sponsorCardElement = sponsorElements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#2d3748',
                    '::placeholder': {
                        color: '#a0aec0',
                    },
                },
            },
        });
        sponsorCardElement.mount('#sponsor-card-element');
        
        sponsorCardElement.on('change', function(event) {
            const displayError = document.getElementById('sponsor-card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }
    
    // Show modal
    document.getElementById('sponsorship-modal').style.display = 'flex';
    document.getElementById('modal-success').style.display = 'none';
    document.getElementById('modal-error').style.display = 'none';
}

function closeSponsorshipModal() {
    document.getElementById('sponsorship-modal').style.display = 'none';
    document.getElementById('sponsorship-form').reset();
    document.getElementById('sponsor-card-errors').textContent = '';
}

// Handle sponsorship form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('sponsorship-form');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submit-sponsorship');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        
        document.getElementById('modal-success').style.display = 'none';
        document.getElementById('modal-error').style.display = 'none';
        
        // Validate card is complete before proceeding
        const cardComplete = await sponsorStripe.createPaymentMethod({
            type: 'card',
            card: sponsorCardElement,
        }).catch(err => {
            return null;
        });
        
        if (!cardComplete || cardComplete.error) {
            const errorMsg = cardComplete?.error?.message || 'Please enter valid card details';
            document.getElementById('error-message').textContent = errorMsg;
            document.getElementById('modal-error').style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class=\"fas fa-handshake mr-2\"></i>Complete Sponsorship';
            return;
        }
        
        try {
            // Create PaymentIntent on server
            const response = await fetch(APP_URL + '/api/payments/sponsorship', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: document.querySelector('input[name="csrf_token"]').value,
                    package_id: document.getElementById('package-id').value,
                    company_name: document.getElementById('company_name').value,
                    contact_email: document.getElementById('contact_email').value
                })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to initialize payment');
            }
            
            // Confirm payment with Stripe
            const {error} = await sponsorStripe.confirmCardPayment(data.data.client_secret, {
                payment_method: {
                    card: sponsorCardElement,
                    billing_details: {
                        name: document.getElementById('company_name').value,
                        email: document.getElementById('contact_email').value,
                    },
                },
            });
            
            if (error) {
                throw new Error(error.message);
            }
            
            // Success!
            document.getElementById('success-message').textContent = 'Thank you for becoming a sponsor! Our team will contact you shortly to finalize the partnership details.';
            document.getElementById('modal-success').style.display = 'block';
            
            // Close modal after 3 seconds and redirect to transactions
            setTimeout(function() {
                closeSponsorshipModal();
                window.location.href = APP_URL + '/account/transactions?filter=sponsorship';
            }, 4000);
            
        } catch (error) {
            document.getElementById('error-message').textContent = error.message;
            document.getElementById('modal-error').style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Complete Sponsorship';
        }
    });
});

$(document).ready(function() {
    loadSponsorships();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
