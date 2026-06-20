<?php
/**
 * File: contact.php
 * Location: /tour_update/contact.php
 *
 * Contact page with form.
 * In Phase 3, form submissions will be handled via API.
 */

$page_title = 'Contact Us';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center">
            <i class="fas fa-envelope text-6xl mb-6 drop-shadow-lg"></i>
            <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">Get In Touch</h1>
            <p class="text-xl drop-shadow">We'd love to hear from you</p>
        </div>
    </div>
</section>

<!-- Contact Info Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">How to Reach Us</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                We're here to help with questions about events, programs, volunteering, or partnerships.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center p-8 bg-blue-50 rounded-lg shadow-lg">
                <div class="text-blue-600 text-4xl mb-4">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Visit Us</h3>
                <p class="text-gray-600">
                    2860 South State Hwy 161<br>
                    Ste 160 211<br>
                    Grand Prairie, TX 75052<br>
                    United States
                </p>
            </div>

            <div class="text-center p-8 bg-green-50 rounded-lg shadow-lg">
                <div class="text-green-600 text-4xl mb-4">
                    <i class="fas fa-phone"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Call Us</h3>
                <p class="text-gray-600">
                    Phone: <a href="tel:+19729794608" class="text-blue-600 hover:underline">(972) 979-4608</a><br>
                    Mon-Fri: 9AM-5PM<br>
                    Weekend: By Appointment
                </p>
            </div>

            <div class="text-center p-8 bg-purple-50 rounded-lg shadow-lg">
                <div class="text-purple-600 text-4xl mb-4">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Email Us</h3>
                <p class="text-gray-600">
                    General: <a href="mailto:info@tourderoar.org" class="text-blue-600 hover:underline">info@tourderoar.org</a><br>
                    Events: <a href="mailto:events@tourderoar.org" class="text-blue-600 hover:underline">events@tourderoar.org</a><br>
                    Partnerships: <a href="mailto:partnerships@tourderoar.org" class="text-blue-600 hover:underline">partnerships@tourderoar.org</a>
                </p>
            </div>

            <div class="text-center p-8 bg-orange-50 rounded-lg shadow-lg">
                <div class="text-orange-600 text-4xl mb-4">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Office Hours</h3>
                <p class="text-gray-600">
                    Monday - Friday: 9AM - 5PM<br>
                    Saturday: By Appointment<br>
                    Sunday: Closed
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form (Phase 3: will submit via API) -->
<section id="contact-form" class="py-16 bg-gray-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Send Us a Message</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Fill out the form below and we'll get back to you within 24 hours.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Success Alert -->
            <div id="success-alert" class="hidden mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded">
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-xl mr-3 mt-0.5"></i>
                    <div>
                        <strong>Success!</strong>
                        <p id="success-message" class="mt-1"></p>
                    </div>
                </div>
            </div>
            
            <!-- Error Alert -->
            <div id="error-alert" class="hidden mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-xl mr-3 mt-0.5"></i>
                    <div>
                        <strong>Error!</strong>
                        <p id="error-message" class="mt-1"></p>
                    </div>
                </div>
            </div>
            
            <form id="contactForm" method="post" action="javascript:void(0);">
                <!-- Honeypot field - hidden from users, catches bots -->
                <input type="text" name="website" id="website" 
                       style="position: absolute; left: -9999px;" 
                       tabindex="-1" 
                       autocomplete="new-password"
                       aria-hidden="true">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="firstName" class="block text-gray-700 text-sm font-bold mb-2">
                            First Name *
                        </label>
                        <input type="text" id="firstName" name="firstName" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="lastName" class="block text-gray-700 text-sm font-bold mb-2">
                            Last Name *
                        </label>
                        <input type="text" id="lastName" name="lastName" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                            Email Address *
                        </label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">
                            Phone Number
                        </label>
                        <input type="tel" id="phone" name="phone"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="subject" class="block text-gray-700 text-sm font-bold mb-2">
                        Subject *
                    </label>
                    <select id="subject" name="subject" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Please select a subject</option>
                        <option value="general">General Information</option>
                        <option value="events">Event Registration</option>
                        <option value="volunteer">Volunteer Opportunities</option>
                        <option value="sponsorship">Sponsorship Inquiry</option>
                        <option value="donation">Donation Questions</option>
                        <option value="bike-donation">Bike Donation</option>
                        <option value="partnership">Partnership Opportunity</option>
                        <option value="media">Media Inquiry</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="message" class="block text-gray-700 text-sm font-bold mb-2">
                        Message *
                    </label>
                    <textarea id="message" name="message" rows="6" required
                              placeholder="Please tell us how we can help you..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"></textarea>
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" id="newsletter" name="newsletter" class="mr-3">
                        <span class="text-gray-700">I'd like to receive updates about Tour de Roar events and programs</span>
                    </label>
                </div>

                <div class="text-center">
                    <button type="submit" id="submit-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-16 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Find Us</h2>
            <p class="text-gray-600">
                Visit our office in Grand Prairie, Texas.
            </p>
        </div>
        
        <!-- Embedded Google Maps -->
        <div class="rounded-lg overflow-hidden shadow-lg">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3355.1234567890!2d-97.0123456!3d32.7456789!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzLCsDQ0JzQ0LjQiTiA5N8KwMDAnNDQuNCJX!5e0!3m2!1sen!2sus!4v1234567890123"
                width="100%" 
                height="400" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade"
                title="Tour de Roar Office Location">
            </iframe>
        </div>
        
        <div class="text-center mt-6">
            <p class="text-gray-600 mb-4">
                <strong>2860 South State Hwy 161, Ste 160 211<br>
                Grand Prairie, TX 75052</strong>
            </p>
            <a href="https://maps.google.com/?q=2860+South+State+Hwy+161+Ste+160+211+Grand+Prairie+TX+75052" 
               target="_blank" 
               class="text-blue-600 hover:text-blue-800 font-bold">
                <i class="fas fa-external-link-alt mr-2"></i>
                Open in Google Maps
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        // Hide previous alerts
        $('#success-alert').addClass('hidden');
        $('#error-alert').addClass('hidden');
        
        // Get form data
        const firstName = $('#firstName').val().trim();
        const lastName = $('#lastName').val().trim();
        const name = (firstName + ' ' + lastName).trim();
        
        const formData = {
            name: name,
            email: $('#email').val().trim(),
            subject: $('#subject').val(),
            message: $('#message').val().trim(),
            website: $('#website').val() // Honeypot
        };
        
        // Basic validation
        if (!firstName || !lastName) {
            showError('Please enter your full name');
            return;
        }
        
        if (!formData.email) {
            showError('Please enter your email address');
            return;
        }
        
        if (!formData.subject) {
            showError('Please select a subject');
            return;
        }
        
        if (!formData.message || formData.message.length < 10) {
            showError('Please enter a message (at least 10 characters)');
            return;
        }
        
        // Disable submit button
        const $submitBtn = $('#submit-btn');
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending...');
        
        // Submit to API
        $.ajax({
            url: APP_URL + '/api/contact/',
            type: 'POST',  // Use 'type' for jQuery compatibility
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    $('#contactForm')[0].reset();
                    
                    // Scroll to success message
                    $('html, body').animate({
                        scrollTop: $('#success-alert').offset().top - 100
                    }, 500);
                } else {
                    showError(response.error || 'Failed to send message');
                }
                $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i>Send Message');
            },
            error: function(xhr) {
                let errorMsg = 'Failed to send message. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                showError(errorMsg);
                $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i>Send Message');
            }
        });
    });
    
    function showSuccess(message) {
        $('#success-message').text(message);
        $('#success-alert').removeClass('hidden');
    }
    
    function showError(message) {
        $('#error-message').text(message);
        $('#error-alert').removeClass('hidden');
    }
});
</script>
