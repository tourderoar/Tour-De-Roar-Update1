<?php
/**
 * File: donate.php
 * Location: /tour_update/donate.php
 *
 * Donations page.
 * In Phase 3, this will integrate with donation payment API.
 */

$page_title = 'Make a Donation';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="page-header">
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center">
            <i class="fas fa-hand-holding-heart text-6xl mb-6 drop-shadow-lg"></i>
            <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">Make a Difference</h1>
            <p class="text-xl drop-shadow">Your donation empowers children through cycling and healthy living</p>
        </div>
    </div>
</section>

<!-- Impact Stats Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Our Impact</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Your generous donations have helped us create lasting change in children's lives.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            <div class="text-center">
                <div class="bg-blue-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-child text-blue-600 text-3xl"></i>
                </div>
                <div class="text-4xl font-bold text-blue-600 mb-2">500+</div>
                <p class="text-gray-600">Children served annually</p>
            </div>

            <div class="text-center">
                <div class="bg-green-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bicycle text-green-600 text-3xl"></i>
                </div>
                <div class="text-4xl font-bold text-green-600 mb-2">150</div>
                <p class="text-gray-600">Bikes donated to children</p>
            </div>

            <div class="text-center">
                <div class="bg-purple-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-dollar-sign text-purple-600 text-3xl"></i>
                </div>
                <div class="text-4xl font-bold text-purple-600 mb-2">$50K+</div>
                <p class="text-gray-600">Raised for programs</p>
            </div>

            <div class="text-center">
                <div class="bg-orange-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-orange-600 text-3xl"></i>
                </div>
                <div class="text-4xl font-bold text-orange-600 mb-2">25</div>
                <p class="text-gray-600">Community events hosted</p>
            </div>
        </div>

        <!-- What Your Donation Does (Hardcoded) -->
        <div class="bg-gray-50 rounded-lg p-8">
            <h3 class="text-3xl font-bold text-center text-gray-900 mb-8">What Your Donation Accomplishes</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- $25 -->
                <div class="bg-white p-6 rounded-lg shadow-lg border-t-4" style="border-color: #3182CE;">
                    <div class="text-center mb-4">
                        <div class="text-4xl font-bold" style="color: #3182CE;">$25</div>
                    </div>
                    <p class="text-gray-700 text-center">
                        Provides safety equipment (helmet, knee pads) for one child
                    </p>
                </div>

                <!-- $50 -->
                <div class="bg-white p-6 rounded-lg shadow-lg border-t-4" style="border-color: #68D391;">
                    <div class="text-center mb-4">
                        <div class="text-4xl font-bold" style="color: #68D391;">$50</div>
                    </div>
                    <p class="text-gray-700 text-center">
                        Covers one child's participation in a community cycling event
                    </p>
                </div>

                <!-- $75 -->
                <div class="bg-white p-6 rounded-lg shadow-lg border-t-4" style="border-color: #805AD5;">
                    <div class="text-center mb-4">
                        <div class="text-4xl font-bold" style="color: #805AD5;">$75</div>
                    </div>
                    <p class="text-gray-700 text-center">
                        Funds a bike maintenance workshop for families
                    </p>
                </div>

                <!-- $150 -->
                <div class="bg-white p-6 rounded-lg shadow-lg border-t-4" style="border-color: #FF6B1A;">
                    <div class="text-center mb-4">
                        <div class="text-4xl font-bold" style="color: #FF6B1A;">$150</div>
                    </div>
                    <p class="text-gray-700 text-center">
                        Provides a refurbished bike and safety gear to a child in need
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Choose Your Donation Amount (Interactive Buttons) -->
<section id="donation-options" class="py-16 bg-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Choose Your Donation Amount</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Every contribution makes a difference. Select an amount or enter your own to support our mission.
            </p>
        </div>

        <!-- Loading Spinner -->
        <div id="donation-buttons-loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4" style="border-color: #68D391;"></div>
            <p class="mt-4 text-gray-600 text-lg">Loading donation options...</p>
        </div>

        <!-- Donation Buttons Container (populated by AJAX) -->
        <div id="donation-buttons-container" class="grid grid-cols-1 lg:grid-cols-2 gap-12"></div>
    </div>
</section>

<!-- Other Ways to Help -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Other Ways to Support Our Mission</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Can't donate right now? There are many other ways you can help us make a difference.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center p-6 bg-blue-50 rounded-lg">
                <div class="text-blue-600 text-4xl mb-4">
                    <i class="fas fa-share-alt"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Share Our Story</h3>
                <p class="text-gray-600 mb-4">Help us reach more families by sharing our mission on social media</p>
                <a href="#" onclick="alert('Share functionality will be added in a future phase'); return false;" class="text-blue-600 font-bold hover:text-blue-800 transition-colors">
                    Share Now
                </a>
            </div>

            <div class="text-center p-6 bg-green-50 rounded-lg">
                <div class="text-green-600 text-4xl mb-4">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Volunteer</h3>
                <p class="text-gray-600 mb-4">Join our team and help directly at events and programs</p>
                <a href="<?= APP_URL ?>/contact" class="text-green-600 font-bold hover:text-green-800 transition-colors">
                    Get Involved
                </a>
            </div>

            <div class="text-center p-6 bg-purple-50 rounded-lg">
                <div class="text-purple-600 text-4xl mb-4">
                    <i class="fas fa-bicycle"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Donate Bikes</h3>
                <p class="text-gray-600 mb-4">Donate gently used bikes that we can refurbish and give to children</p>
                <a href="<?= APP_URL ?>/contact" class="text-purple-600 font-bold hover:text-purple-800 transition-colors">
                    Learn More
                </a>
            </div>

            <div class="text-center p-6 bg-orange-50 rounded-lg">
                <div class="text-orange-600 text-4xl mb-4">
                    <i class="fas fa-store"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Shop Our Store</h3>
                <p class="text-gray-600 mb-4">Purchase merchandise - proceeds support our programs</p>
                <a href="<?= APP_URL ?>/store" class="text-orange-600 font-bold hover:text-orange-800 transition-colors">
                    Shop Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stories of Impact -->
<section class="py-16 bg-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Stories of Impact</h2>
        </div>

        <div class="space-y-6">
            <!-- Maria's Story -->
            <div class="bg-white rounded-lg shadow-lg p-8 flex items-start">
                <div class="flex-shrink-0 mr-6">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center">
                        <i class="fas fa-award text-blue-600 text-2xl"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Maria's Story</h3>
                    <p class="text-gray-600 italic mb-3">
                        "Thanks to Tour de Roar, my daughter received her first bike and learned to ride with confidence. 
                        Now she's part of the community rides and has made so many new friends. This program changed her life."
                    </p>
                    <p class="text-gray-500 text-sm">Maria B., Parent</p>
                </div>
            </div>

            <!-- Community Impact -->
            <div class="bg-white rounded-lg shadow-lg p-8 flex items-start">
                <div class="flex-shrink-0 mr-6">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center">
                        <i class="fas fa-bicycle text-green-600 text-2xl"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Community Impact</h3>
                    <p class="text-gray-600 italic mb-3">
                        "The monthly community rides have brought our neighborhood together. Kids who never would 
                        have met are now best friends, and parents have formed a supportive network. Tour de Roar builds 
                        more than fitness – it builds community."
                    </p>
                    <p class="text-gray-500 text-sm">James L., Community Member</p>
                </div>
            </div>

            <!-- Health Transformation -->
            <div class="bg-white rounded-lg shadow-lg p-8 flex items-start">
                <div class="flex-shrink-0 mr-6">
                    <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center">
                        <i class="fas fa-heart text-purple-600 text-2xl"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Health Transformation</h3>
                    <p class="text-gray-600 italic mb-3">
                        "My son struggled with anxiety and staying active. The Tour de Roar program gave him a fun way 
                        to exercise and build confidence. His doctor says his physical and mental health have improved dramatically."
                    </p>
                    <p class="text-gray-500 text-sm">Sarah M., Parent</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tax Information -->
<section class="py-16 bg-blue-600 text-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-8">Your Donation is Tax-Deductible</h2>
        <div class="bg-blue-700 p-8 rounded-lg">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-shield-alt text-4xl text-yellow-300 mr-4"></i>
                <div class="text-left">
                    <h3 class="text-xl font-bold">501(c)(3) Nonprofit Organization</h3>
                    <p class="text-blue-200">Tax ID: 82-2322839</p>
                </div>
            </div>
            <p class="text-blue-100 mb-6">
                Tour de Roar is a registered 501(c)(3) nonprofit organization. Your donation is tax-deductible 
                to the fullest extent allowed by law. You will receive a receipt for your records immediately after donation.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div>
                    <div class="text-2xl font-bold text-yellow-300">95%</div>
                    <p class="text-blue-200">of funds go directly to programs</p>
                </div>
                <div>
                    <div class="text-2xl font-bold text-yellow-300">100%</div>
                    <p class="text-blue-200">secure donation processing</p>
                </div>
                <div>
                    <div class="text-2xl font-bold text-yellow-300">24/7</div>
                    <p class="text-blue-200">donation support available</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Donation Payment Modal -->
<div id="donation-modal" class="payment-modal-overlay" style="display: none;">
    <div class="payment-modal-content">
        <div class="payment-modal-header">
            <h2 id="modal-title" class="payment-modal-title">
                <i class="fas fa-heart mr-2"></i>Make a Donation
            </h2>
            <button type="button" class="payment-modal-close" onclick="closeDonationModal()">
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
            
            <form id="donation-form">
                <input type="hidden" id="donation-type-id" name="donation_type_id">
                <input type="hidden" id="is-recurring" name="is_recurring" value="0">
                <input type="hidden" id="preset-amount" name="preset_amount" value="0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <!-- Custom Amount Input (hidden by default) -->
                <div id="custom-amount-section" style="display: none; margin-bottom: 1.5rem;">
                    <label for="custom-amount-input" style="display: block; font-weight: 600; color: #2d3748; margin-bottom: 0.5rem;">
                        Enter Your Donation Amount
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #4a5568; font-size: 1.25rem; font-weight: 600;">$</span>
                        <input type="number" id="custom-amount-input" min="5" max="10000" step="0.01" placeholder="50.00" 
                            style="width: 100%; padding: 0.75rem 1rem 0.75rem 2rem; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1.125rem; font-weight: 600;" />
                    </div>
                    <p style="font-size: 0.875rem; color: #718096; margin-top: 0.5rem;">Minimum: $5.00 | Maximum: $10,000.00</p>
                </div>
                
                <!-- Donation Summary -->
                <div id="donation-summary" class="payment-summary">
                    <h3 style="color: #68D391; font-weight: 700; margin-bottom: 0.75rem;">Your Donation</h3>
                    <div class="payment-summary-row">
                        <span id="donation-type-label">One-Time Donation</span>
                        <strong id="donation-amount" style="color: #3182CE; font-size: 1.5rem;">$0.00</strong>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: #68D391; font-weight: 700; margin-bottom: 0.75rem;">Payment Information</h3>                    <div style="background: #EBF8FF; border-left: 4px solid #3182CE; padding: 12px 16px; margin-bottom: 1rem; border-radius: 4px;">
                        <div style="display: flex; align-items: start; gap: 8px;">
                            <i class="fas fa-shield-alt" style="color: #3182CE; margin-top: 2px;"></i>
                            <div style="font-size: 0.875rem; color: #2C5282; line-height: 1.5;">
                                <strong>Secure Payment:</strong> Your card details are encrypted and processed securely by Stripe. 
                                We never store or have access to your card information.
                            </div>
                        </div>
                    </div>                    <div id="donation-card-element" class="stripe-card-element"></div>
                    <div id="donation-card-errors" class="stripe-card-errors"></div>
                </div>
                
                <button type="submit" id="submit-donation" class="payment-submit-btn">
                    <i class="fas fa-heart mr-2"></i>Complete Donation
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
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.payment-modal-header {
    background: linear-gradient(135deg, #68D391 0%, #48bb78 100%);
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
    background: linear-gradient(135deg, #f0fdf4 0%, #d1fae5 100%);
    border-left: 4px solid #68D391;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.payment-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
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
    background: linear-gradient(135deg, #68D391 0%, #48bb78 100%);
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
    box-shadow: 0 10px 20px rgba(104, 211, 145, 0.3);
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
// Initialize Stripe for donations
const donationStripe = Stripe(STRIPE_PK);
const donationElements = donationStripe.elements();
let donationCardElement = null;

function processDonationPayment(donationTypeId, amount, label, isRecurring) {
    // Check if user is logged in
    if (!IS_LOGGED_IN) {
        window.location.href = APP_URL + '/account/login?redirect=donate';
        return;
    }
    
    // Hide custom amount section for preset donations
    document.getElementById('custom-amount-section').style.display = 'none';
    document.getElementById('donation-summary').style.display = 'block';
    
    // Populate modal
    document.getElementById('donation-type-id').value = donationTypeId;
    document.getElementById('is-recurring').value = isRecurring ? '1' : '0';
    document.getElementById('preset-amount').value = amount;
    document.getElementById('donation-type-label').textContent = isRecurring ? label + ' (Monthly)' : label;
    document.getElementById('donation-amount').textContent = '$' + parseFloat(amount).toFixed(2);
    
    // Create Stripe card element if not already created
    if (!donationCardElement) {
        donationCardElement = donationElements.create('card', {
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
        donationCardElement.mount('#donation-card-element');
        
        donationCardElement.on('change', function(event) {
            const displayError = document.getElementById('donation-card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }
    
    // Show modal
    document.getElementById('donation-modal').style.display = 'flex';
    document.getElementById('modal-success').style.display = 'none';
    document.getElementById('modal-error').style.display = 'none';
}

function processCustomDonation(isRecurring) {
    // Check if user is logged in
    if (!IS_LOGGED_IN) {
        window.location.href = APP_URL + '/account/login?redirect=donate';
        return;
    }
    
    // Show custom amount section
    document.getElementById('custom-amount-section').style.display = 'block';
    document.getElementById('donation-summary').style.display = 'none';
    
    // Set donation type ID: -1 for one-time, -2 for monthly
    document.getElementById('donation-type-id').value = isRecurring ? '-2' : '-1';
    document.getElementById('is-recurring').value = isRecurring ? '1' : '0';
    document.getElementById('preset-amount').value = '0';
    document.getElementById('custom-amount-input').value = '';
    
    // Create Stripe card element if not already created
    if (!donationCardElement) {
        donationCardElement = donationElements.create('card', {
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
        donationCardElement.mount('#donation-card-element');
        
        donationCardElement.on('change', function(event) {
            const displayError = document.getElementById('donation-card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }
    
    // Show modal
    document.getElementById('donation-modal').style.display = 'flex';
    document.getElementById('modal-success').style.display = 'none';
    document.getElementById('modal-error').style.display = 'none';
}

function closeDonationModal() {
    document.getElementById('donation-modal').style.display = 'none';
    document.getElementById('donation-form').reset();
    document.getElementById('donation-card-errors').textContent = '';
    document.getElementById('custom-amount-section').style.display = 'none';
    document.getElementById('donation-summary').style.display = 'block';
}

// Handle donation form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('donation-form');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const donationTypeId = parseInt(document.getElementById('donation-type-id').value);
        const isCustom = donationTypeId === -1 || donationTypeId === -2;
        let amount;
        
        // Validate and get amount
        if (isCustom) {
            const customAmountInput = document.getElementById('custom-amount-input');
            amount = parseFloat(customAmountInput.value);
            
            if (!amount || amount < 5) {
                document.getElementById('error-message').textContent = 'Please enter a donation amount of at least $5.00';
                document.getElementById('modal-error').style.display = 'block';
                return;
            }
            
            if (amount > 10000) {
                document.getElementById('error-message').textContent = 'Maximum donation amount is $10,000.00. For larger donations, please contact us.';
                document.getElementById('modal-error').style.display = 'block';
                return;
            }
        } else {
            amount = parseFloat(document.getElementById('preset-amount').value);
        }
        
        const submitBtn = document.getElementById('submit-donation');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        
        document.getElementById('modal-success').style.display = 'none';
        document.getElementById('modal-error').style.display = 'none';
        
        // Validate card is complete before proceeding
        const cardComplete = await donationStripe.createPaymentMethod({
            type: 'card',
            card: donationCardElement,
        }).catch(() => null);
        
        if (!cardComplete || cardComplete.error) {
            const errorMsg = cardComplete?.error?.message || 'Please enter valid card details';
            document.getElementById('error-message').textContent = errorMsg;
            document.getElementById('modal-error').style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-heart mr-2"></i>Complete Donation';
            return;
        }
        
        try {
            // Create PaymentIntent on server
            const response = await fetch(APP_URL + '/api/payments/donation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: document.querySelector('input[name="csrf_token"]').value,
                    donation_type_id: donationTypeId,
                    custom_amount: isCustom ? amount : null,
                    is_recurring: document.getElementById('is-recurring').value === '1'
                })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to initialize payment');
            }
            
            // Confirm payment with Stripe
            const {error} = await donationStripe.confirmCardPayment(data.data.client_secret, {
                payment_method: {
                    card: donationCardElement,
                },
            });
            
            if (error) {
                throw new Error(error.message);
            }
            
            // Success!
            document.getElementById('success-message').textContent = 'Thank you for your generous donation! You will receive a confirmation email shortly.';
            document.getElementById('modal-success').style.display = 'block';
            
            // Close modal after 3 seconds and redirect to transactions
            setTimeout(function() {
                closeDonationModal();
                window.location.href = APP_URL + '/account/transactions?filter=donation';
            }, 3000);
            
        } catch (error) {
            document.getElementById('error-message').textContent = error.message;
            document.getElementById('modal-error').style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-heart mr-2"></i>Complete Donation';
        }
    });
    
    // Real-time custom amount update
    const customAmountInput = document.getElementById('custom-amount-input');
    if (customAmountInput) {
        customAmountInput.addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            const isRecurring = document.getElementById('is-recurring').value === '1';
            const label = isRecurring ? 'Custom Monthly Donation' : 'Custom One-Time Donation';
            
            document.getElementById('donation-type-label').textContent = label;
            document.getElementById('donation-amount').textContent = '$' + amount.toFixed(2);
            
            // Show/hide summary based on valid amount
            if (amount >= 5) {
                document.getElementById('donation-summary').style.display = 'block';
            } else {
                document.getElementById('donation-summary').style.display = 'none';
            }
        });
    }
});

$(document).ready(function() {
    // Only load donation buttons (one-time and monthly options)
    loadDonationButtons();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
