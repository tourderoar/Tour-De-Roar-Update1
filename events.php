<?php
/**
 * File: events.php
 * Location: /tour_update/events.php
 *
 * Events listing page — displays upcoming and past cycling events.
 * In Phase 4, this will load events dynamically from the API.
 */

$page_title = 'Upcoming Events';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center">
            <i class="fas fa-calendar text-6xl mb-6 drop-shadow-lg"></i>
            <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">Cycling Events</h1>
            <p class="text-xl drop-shadow">Join us for charity cycling events that make a difference</p>
        </div>
    </div>
</section>

<!-- Upcoming Events Section (Loaded from API) -->
<section class="content-section bg-white">
    <div class="container mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12" style="color: #3182CE;">Upcoming Events</h2>
        
        <!-- Loading Spinner -->
        <div id="events-loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4" style="border-color: #805AD5;"></div>
            <p class="mt-4 text-gray-600 text-lg">Loading events...</p>
        </div>
        
        <!-- Error Message -->
        <div id="events-error" class="hidden bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-4"></i>
                <div>
                    <h3 class="font-bold text-red-800 mb-1">Unable to load events</h3>
                    <p class="text-red-700">Please try refreshing the page. If the problem persists, contact us.</p>
                </div>
            </div>
        </div>
        
        <!-- Events Container (populated by AJAX) -->
        <div id="events-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"></div>
    </div>
</section>

<script>
// Global variable to store events data
let eventsData = [];

// Load events from API
$(document).ready(function() {
    $.ajax({
        url: APP_URL + '/api/events',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#events-loading').hide();
            
            // Store events data globally for payment modal
            eventsData = response.data || [];
            
            
            if (response.success && eventsData.length > 0) {
                const colors = [
                    {bg: 'from-orange-100 to-yellow-100', border: '#FF6B1A', icon: '#FF6B1A', text: '#E53E3E', badge: 'bg-orange-200 text-orange-800', badgeText: 'FEATURED EVENT', btn: 'linear-gradient(45deg, #805AD5, #3182CE)'},
                    {bg: 'from-blue-100 to-green-100', border: '#3182CE', icon: '#3182CE', text: '#3182CE', badge: 'bg-blue-200 text-blue-800', badgeText: 'CHALLENGE EVENT', btn: 'linear-gradient(45deg, #3182CE, #68D391)'},
                    {bg: 'from-purple-100 to-pink-100', border: '#805AD5', icon: '#805AD5', text: '#805AD5', badge: 'bg-purple-200 text-purple-800', badgeText: 'FAMILY FRIENDLY', btn: 'linear-gradient(45deg, #805AD5, #E53E3E)'}
                ];
                
                eventsData.forEach((event, index) => {
                    const color = colors[index % colors.length];
                    const eventDate = new Date(event.event_date);
                    const dateStr = eventDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' });
                    
                    const card = `
                        <div class="bg-gradient-to-br ${color.bg} rounded-xl p-6 shadow-xl border-l-4 hover:shadow-2xl transition-all duration-300" style="border-color: ${color.border};">
                            <div class="h-48 bg-gray-100 rounded-lg mb-4 overflow-hidden shadow-lg">
                                <img src="${APP_URL}/images/events/${event.image_path}" alt="${event.title}" class="gallery-img">
                            </div>
                            <div class="mb-4">
                                <span class="${color.badge} px-3 py-1 rounded-full text-sm font-semibold">${color.badgeText}</span>
                            </div>
                            <h3 class="text-2xl font-bold mb-2" style="color: ${color.text};">${event.title}</h3>
                            <p class="text-gray-700 mb-4">${event.description}</p>
                            
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-calendar mr-3 w-4" style="color: ${color.icon};"></i>
                                    <span><strong>Date:</strong> ${dateStr}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-clock mr-3 w-4" style="color: ${color.icon};"></i>
                                    <span><strong>Time:</strong> ${event.time_start || 'TBA'}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-map-marker-alt mr-3 w-4" style="color: ${color.icon};"></i>
                                    <span><strong>Location:</strong> ${event.location}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-route mr-3 w-4" style="color: ${color.icon};"></i>
                                    <span><strong>Distances:</strong> ${event.distances}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-dollar-sign mr-3 w-4" style="color: ${color.icon};"></i>
                                    <span><strong>Registration:</strong> $${parseFloat(event.price).toFixed(2)}</span>
                                </div>
                            </div>
                            
                            <button onclick="openRegistration(${event.id})" class="w-full py-3 rounded-lg font-bold text-white shadow-lg transition-all duration-300 transform hover:scale-105" style="background: ${color.btn};">
                                <i class="fas fa-sign-in-alt mr-2"></i>Register Now
                            </button>
                        </div>
                    `;
                    
                    $('#events-container').append(card);
                });
            } else {
                $('#events-container').html('<div class="col-span-full text-center py-12"><p class="text-gray-600 text-lg">No upcoming events at this time. Check back soon!</p></div>');
            }
        },
        error: function() {
            $('#events-loading').hide();
            $('#events-error').removeClass('hidden');
        }
    });
});

// Initialize Stripe
const stripe = Stripe(STRIPE_PK);
const elements = stripe.elements();
let cardElement = null;
let currentEvent = null;

function openRegistration(eventId) {
    // Check if user is logged in
    if (!IS_LOGGED_IN) {
        window.location.href = APP_URL + '/account/login?redirect=events';
        return;
    }
    
    // Find the event
    const event = eventsData.find(e => e.id == eventId);
    if (!event) {
        alert('Event not found');
        return;
    }
    
    currentEvent = event;
    
    // Populate modal
    document.getElementById('modal-title').innerHTML = '<i class="fas fa-calendar-check mr-2"></i>' + event.title;
    document.getElementById('event-id').value = event.id;
    document.getElementById('summary-event-title').textContent = event.title;
    document.getElementById('summary-amount').textContent = '$' + parseFloat(event.price).toFixed(2);
    
    // Pre-fill with user data if available
    const user = <?= json_encode(get_logged_in_user() ?? []); ?>;
    if (user.first_name) {
        document.getElementById('participant_name').value = (user.first_name + ' ' + user.last_name).trim();
    }
    if (user.email) {
        const emailField = document.getElementById('participant_email');
        emailField.value = user.email;
        emailField.readOnly = true;
        emailField.style.backgroundColor = '#f7fafc';
        emailField.style.cursor = 'not-allowed';
    }
    if (user.phone) {
        document.getElementById('participant_phone').value = user.phone;
    }
    
    // Create Stripe card element if not already created
    if (!cardElement) {
        cardElement = elements.create('card', {
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
        cardElement.mount('#card-element');
        
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }
    
    // Show modal
    document.getElementById('payment-modal').style.display = 'flex';
    document.getElementById('modal-success').style.display = 'none';
    document.getElementById('modal-error').style.display = 'none';
}

function closePaymentModal() {
    document.getElementById('payment-modal').style.display = 'none';
    document.getElementById('payment-form').reset();
    document.getElementById('card-errors').textContent = '';
}

// Handle payment form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('payment-form');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submit-payment');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        
        document.getElementById('modal-success').style.display = 'none';
        document.getElementById('modal-error').style.display = 'none';
                // Validate card is complete before proceeding
        const cardComplete = await stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
        }).catch(() => null);
        
        if (!cardComplete || cardComplete.error) {
            const errorMsg = cardComplete?.error?.message || 'Please enter valid card details';
            document.getElementById('error-message').textContent = errorMsg;
            document.getElementById('modal-error').style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Complete Registration';
            return;
        }
                try {
            // Create PaymentIntent on server
            const response = await fetch(APP_URL + '/api/payments/event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: document.querySelector('input[name="csrf_token"]').value,
                    event_id: document.getElementById('event-id').value,
                    participant_name: document.getElementById('participant_name').value,
                    participant_email: document.getElementById('participant_email').value,
                    participant_phone: document.getElementById('participant_phone').value,
                    emergency_contact: document.getElementById('emergency_contact').value,
                    emergency_phone: document.getElementById('emergency_phone').value
                })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to initialize payment');
            }
            
            // Confirm payment with Stripe
            const {error} = await stripe.confirmCardPayment(data.data.client_secret, {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: document.getElementById('participant_name').value,
                        email: document.getElementById('participant_email').value,
                    },
                },
            });
            
            if (error) {
                throw new Error(error.message);
            }
            
            // Success!
            document.getElementById('success-message').textContent = 'Registration successful! You will receive a confirmation email shortly.';
            document.getElementById('modal-success').style.display = 'block';
            
            // Close modal after 3 seconds and redirect to transactions
            setTimeout(function() {
                closePaymentModal();
                window.location.href = APP_URL + '/account/transactions?filter=event';
            }, 3000);
            
        } catch (error) {
            document.getElementById('error-message').textContent = error.message;
            document.getElementById('modal-error').style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Complete Registration';
        }
    });
});
</script>

<!-- Event Features Section -->
<section class="content-section bg-gradient-to-r from-blue-50 to-purple-50">
    <div class="container mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12" style="color: #805AD5;">What to Expect</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white p-6 rounded-xl shadow-lg text-center hover:shadow-2xl transition-shadow duration-300">
                <i class="fas fa-route text-4xl mb-4" style="color: #FF6B1A;"></i>
                <h3 class="text-xl font-bold mb-4" style="color: #805AD5;">Marked Routes</h3>
                <p class="text-gray-700">Clearly marked and supported routes with rest stops and mechanical support.</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg text-center hover:shadow-2xl transition-shadow duration-300">
                <i class="fas fa-medal text-4xl mb-4" style="color: #3182CE;"></i>
                <h3 class="text-xl font-bold mb-4" style="color: #805AD5;">Finisher Medals</h3>
                <p class="text-gray-700">Commemorative medals for all participants who complete their chosen distance.</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg text-center hover:shadow-2xl transition-shadow duration-300">
                <i class="fas fa-utensils text-4xl mb-4" style="color: #68D391;"></i>
                <h3 class="text-xl font-bold mb-4" style="color: #805AD5;">Food & Refreshments</h3>
                <p class="text-gray-700">Post-ride meals and refreshments included with registration.</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg text-center hover:shadow-2xl transition-shadow duration-300">
                <i class="fas fa-tshirt text-4xl mb-4" style="color: #E53E3E;"></i>
                <h3 class="text-xl font-bold mb-4" style="color: #805AD5;">Event T-Shirts</h3>
                <p class="text-gray-700">Custom Tour de Roar event t-shirts for all registered participants.</p>
            </div>
        </div>
    </div>
</section>

<!-- Past Events Section -->
<section class="content-section bg-white">
    <div class="container mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12" style="color: #805AD5;">Past Events Highlights</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-gray-50 rounded-xl p-6 shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <div class="h-40 bg-gray-200 rounded-lg mb-4 overflow-hidden">
                    <img src="<?= APP_URL ?>/images/events/balloon_arch_finish.jpg" alt="2024 Spring Ride" class="gallery-img">
                </div>
                <h3 class="text-xl font-bold mb-2" style="color: #FF6B1A;">Spring Charity Ride 2024</h3>
                <p class="text-gray-700 mb-2">150 participants • $12,000 raised</p>
                <p class="text-gray-600 text-sm">Amazing turnout for our annual spring event with perfect weather conditions.</p>
            </div>
            
            <div class="bg-gray-50 rounded-xl p-6 shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <div class="h-40 bg-gray-200 rounded-lg mb-4 overflow-hidden">
                    <img src="<?= APP_URL ?>/images/events/cyclist_interview.jpg" alt="Summer Challenge 2024" class="gallery-img">
                </div>
                <h3 class="text-xl font-bold mb-2" style="color: #3182CE;">Summer Challenge 2024</h3>
                <p class="text-gray-700 mb-2">75 participants • $8,500 raised</p>
                <p class="text-gray-600 text-sm">Challenging routes tested our cyclists while raising funds for education.</p>
            </div>
            
            <div class="bg-gray-50 rounded-xl p-6 shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <div class="h-40 bg-gray-200 rounded-lg mb-4 overflow-hidden">
                    <img src="<?= APP_URL ?>/images/events/event_registration_area.jpg" alt="Community Rides 2024" class="gallery-img">
                </div>
                <h3 class="text-xl font-bold mb-2" style="color: #68D391;">Community Rides 2024</h3>
                <p class="text-gray-700 mb-2">12 events • 400+ participants</p>
                <p class="text-gray-600 text-sm">Monthly community rides brought families together for great causes.</p>
            </div>
        </div>
    </div>
</section>

<!-- Registration Info Section -->
<section class="content-section bg-gradient-to-r from-orange-50 to-red-50">
    <div class="container mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12" style="color: #805AD5;">Registration Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div>
                <h3 class="text-2xl font-bold mb-6" style="color: #FF6B1A;">How to Register</h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center mr-4 mt-1 flex-shrink-0">1</div>
                        <div>
                            <h4 class="font-bold mb-2">Choose Your Event</h4>
                            <p class="text-gray-700">Select from our upcoming cycling events based on your skill level and preference.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center mr-4 mt-1 flex-shrink-0">2</div>
                        <div>
                            <h4 class="font-bold mb-2">Complete Registration</h4>
                            <p class="text-gray-700">Fill out the registration form with your details and emergency contact information.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center mr-4 mt-1 flex-shrink-0">3</div>
                        <div>
                            <h4 class="font-bold mb-2">Secure Payment</h4>
                            <p class="text-gray-700">Complete payment securely through our Stripe payment processing system.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center mr-4 mt-1 flex-shrink-0">4</div>
                        <div>
                            <h4 class="font-bold mb-2">Receive Confirmation</h4>
                            <p class="text-gray-700">Get your confirmation email with event details, waiver, and instructions.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-2xl font-bold mb-6" style="color: #3182CE;">Important Information</h3>
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-3 mt-1 flex-shrink-0" style="color: #68D391;"></i>
                            <span>All participants must sign a liability waiver</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-3 mt-1 flex-shrink-0" style="color: #68D391;"></i>
                            <span>Helmets are required for all cyclists</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-3 mt-1 flex-shrink-0" style="color: #68D391;"></i>
                            <span>Registration includes event t-shirt and post-ride meal</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-3 mt-1 flex-shrink-0" style="color: #68D391;"></i>
                            <span>Early bird discounts available until 30 days before event</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-3 mt-1 flex-shrink-0" style="color: #68D391;"></i>
                            <span>Refunds available up to 14 days before event</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-3 mt-1 flex-shrink-0" style="color: #68D391;"></i>
                            <span>Mechanical support and SAG wagons provided</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Event Registration Payment Modal -->
<div id="payment-modal" class="payment-modal-overlay" style="display: none;">
    <div class="payment-modal-content">
        <div class="payment-modal-header">
            <h2 id="modal-title" class="payment-modal-title">
                <i class="fas fa-calendar-check mr-2"></i>Event Registration
            </h2>
            <button type="button" class="payment-modal-close" onclick="closePaymentModal()">
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
            
            <form id="payment-form">
                <input type="hidden" id="event-id" name="event_id">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <!-- Event Details Summary -->
                <div class="payment-summary">
                    <h3 style="color: #805AD5; font-weight: 700; margin-bottom: 0.75rem;">Event Details</h3>
                    <div class="payment-summary-row">
                        <span>Event:</span>
                        <strong id="summary-event-title">-</strong>
                    </div>
                    <div class="payment-summary-row">
                        <span>Amount:</span>
                        <strong id="summary-amount" style="color: #3182CE; font-size: 1.25rem;">$0.00</strong>
                    </div>
                </div>
                
                <!-- Participant Information -->
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: #805AD5; font-weight: 700; margin-bottom: 0.75rem;">Participant Information</h3>
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" id="participant_name" name="participant_name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" id="participant_email" name="participant_email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" id="participant_phone" name="participant_phone" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label>Emergency Contact Name</label>
                        <input type="text" id="emergency_contact" name="emergency_contact" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label>Emergency Contact Phone</label>
                        <input type="tel" id="emergency_phone" name="emergency_phone" class="form-input">
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
                    <div id="card-element" class="stripe-card-element"></div>
                    <div id="card-errors" class="stripe-card-errors"></div>
                </div>
                
                <button type="submit" id="submit-payment" class="payment-submit-btn">
                    <i class="fas fa-lock mr-2"></i>Complete Registration
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
    background: linear-gradient(135deg, #805AD5 0%, #3182CE 100%);
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
    margin-bottom: 0.5rem;
}

.payment-summary-row:last-child {
    margin-bottom: 0;
    padding-top: 0.5rem;
    border-top: 1px solid #cbd5e0;
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
