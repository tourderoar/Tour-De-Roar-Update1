/**
 * File: js/phase4-ajax.js
 * Location: /tour_update/js/phase4-ajax.js
 *
 * AJAX loading functions for dynamic content
 * Loaded on specific pages that need dynamic API content
 */

// Load sponsorship packages from API
function loadSponsorships() {
    $.ajax({
        url: APP_URL + '/api/sponsorships',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#sponsorships-loading').hide();
            
            if (response.success && response.data.length > 0) {
                const gradients = [
                    'from-yellow-400 to-orange-500',      // Title Sponsor
                    'from-blue-500 to-purple-600',        // Presenting Sponsor
                    'from-green-500 to-teal-600',         // Supporting Sponsor
                    'from-purple-600 to-purple-800',      // Premier Sponsor
                    'from-blue-400 to-blue-600',          // Elite Sponsor
                    'from-gray-600 to-gray-800',          // Platinum Sponsor
                    'from-yellow-500 to-orange-600',      // Gold Sponsor
                    'from-gray-400 to-gray-600',          // Silver Sponsor
                    'from-orange-600 to-red-700'          // Bronze Sponsor
                ];
                
                const icons = [
                    'fa-crown',         // Title Sponsor
                    'fa-star',          // Presenting Sponsor
                    'fa-handshake',     // Supporting Sponsor
                    'fa-gem',           // Premier Sponsor
                    'fa-award',         // Elite Sponsor
                    'fa-medal',         // Platinum Sponsor
                    'fa-trophy',        // Gold Sponsor
                    'fa-star-half-alt', // Silver Sponsor
                    'fa-ribbon'         // Bronze Sponsor
                ];
                
                response.data.forEach((pkg, index) => {
                    const gradient = gradients[index % gradients.length];
                    const icon = icons[index % icons.length];
                    const perks = pkg.perks || [];
                    
                    let perksHTML = '';
                    perks.forEach(perk => {
                        perksHTML += `
                            <li class="flex items-center">
                                <i class="fas fa-check text-white mr-3"></i>
                                <span>${perk}</span>
                            </li>
                        `;
                    });
                    
                    const card = `
                        <div class="bg-gradient-to-br ${gradient} p-8 rounded-lg shadow-xl text-white transform hover:scale-105 transition-transform">
                            <div class="text-center">
                                <div class="text-6xl mb-4">
                                    <i class="fas ${icon}"></i>
                                </div>
                                <h3 class="text-2xl font-bold mb-4">${pkg.name}</h3>
                                <div class="text-4xl font-bold mb-6">$${parseFloat(pkg.price).toLocaleString()}</div>
                            </div>
                            
                            <ul class="space-y-3 mb-8">
                                ${perksHTML}
                            </ul>
                            
                            <button onclick="processSponsorPayment(${pkg.id}, '${pkg.name}', ${pkg.price})" class="w-full bg-white text-gray-900 font-bold py-3 px-6 rounded-lg hover:bg-gray-100 transition-colors">
                                Become ${pkg.name}
                            </button>
                        </div>
                    `;
                    
                    $('#sponsorships-container').append(card);
                });
            } else {
                $('#sponsorships-container').html('<div class="col-span-full text-center py-12"><p class="text-gray-600 text-lg">No sponsorship packages available at this time.</p></div>');
            }
        },
        error: function() {
            $('#sponsorships-loading').hide();
            $('#sponsorships-error').removeClass('hidden');
        }
    });
}

// Load donation types from API
function loadDonations() {
    $.ajax({
        url: APP_URL + '/api/donations',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#donations-loading').hide();
            
            if (response.success && response.data.length > 0) {
                const colors = ['text-green-600', 'text-blue-600', 'text-purple-600', 'text-orange-600'];
                
                response.data.forEach((donation, index) => {
                    const color = colors[index % colors.length];
                    
                    const card = `
                        <div class="text-center">
                            <div class="${color} text-6xl mb-4">$${parseFloat(donation.amount).toFixed(0)}</div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">${donation.label}</h4>
                            <p class="text-gray-600">${donation.description}</p>
                        </div>
                    `;
                    
                    $('#donations-container').append(card);
                });
            } else {
                $('#donations-container').html('<div class="col-span-full text-center py-12"><p class="text-gray-600 text-lg">No donation options available at this time.</p></div>');
            }
        },
        error: function() {
            $('#donations-loading').hide();
            $('#donations-error').removeClass('hidden');
        }
    });
}

// Load donation buttons for "Choose Your Donation Amount" section
function loadDonationButtons() {
    $.ajax({
        url: APP_URL + '/api/donations',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#donation-buttons-loading').hide();
            
            if (response.success && response.data.length > 0) {
                // Separate one-time and recurring donations
                const oneTimeDonations = response.data.filter(d => d.is_recurring == 0);
                const recurringDonations = response.data.filter(d => d.is_recurring == 1);
                
                // Build One-Time Donation buttons
                let oneTimeButtons = '';
                oneTimeDonations.forEach((donation, index) => {
                    const colors = [
                        {bg: 'bg-green-100 hover:bg-green-200', text: 'text-green-800', border: 'border-green-300'},
                        {bg: 'bg-blue-100 hover:bg-blue-200', text: 'text-blue-800', border: 'border-blue-300'},
                        {bg: 'bg-purple-100 hover:bg-purple-200', text: 'text-purple-800', border: 'border-purple-300'},
                        {bg: 'bg-orange-100 hover:bg-orange-200', text: 'text-orange-800', border: 'border-orange-300'}
                    ];
                    const color = colors[index % colors.length];
                    
                    oneTimeButtons += `
                        <button onclick="processDonationPayment(${donation.id}, ${donation.amount}, '${donation.label}', false)" class="donation-btn ${color.bg} ${color.text} font-bold py-4 px-6 rounded-lg transition-colors border-2 border-transparent hover:${color.border}">
                            <div class="text-2xl mb-1">$${parseFloat(donation.amount).toFixed(0)}</div>
                            <div class="text-sm">${donation.label}</div>
                        </button>
                    `;
                });
                
                // Build Monthly Donation buttons
                let monthlyButtons = '';
                recurringDonations.forEach((donation, index) => {
                    const colors = [
                        {bg: 'bg-green-100 hover:bg-green-200', text: 'text-green-800', border: 'border-green-300'},
                        {bg: 'bg-blue-100 hover:bg-blue-200', text: 'text-blue-800', border: 'border-blue-300'},
                        {bg: 'bg-purple-100 hover:bg-purple-200', text: 'text-purple-800', border: 'border-purple-300'},
                        {bg: 'bg-orange-100 hover:bg-orange-200', text: 'text-orange-800', border: 'border-orange-300'}
                    ];
                    const color = colors[index % colors.length];
                    
                    monthlyButtons += `
                        <button onclick="processDonationPayment(${donation.id}, ${donation.amount}, '${donation.label}', true)" class="donation-btn ${color.bg} ${color.text} font-bold py-4 px-6 rounded-lg transition-colors border-2 border-transparent hover:${color.border}">
                            <div class="text-2xl mb-1">$${parseFloat(donation.amount).toFixed(0)}</div>
                            <div class="text-sm">${donation.label}</div>
                            <div class="text-xs">per month</div>
                        </button>
                    `;
                });
                
                // Build HTML structure
                let html = '<div class="col-span-full text-center py-12"><p class="text-gray-600 text-lg">No donation options available at this time.</p></div>';
                
                if (oneTimeDonations.length > 0 || recurringDonations.length > 0) {
                    html = '';
                    
                    // One-Time Donations section
                    if (oneTimeDonations.length > 0) {
                        html += `
                            <div class="bg-white rounded-lg shadow-lg p-8">
                                <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">One-Time Donation</h3>
                                
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    ${oneTimeButtons}
                                </div>

                                <button onclick="processCustomDonation(false)" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                                    Donate a Custom Amount
                                </button>
                            </div>
                        `;
                    }
                    
                    // Monthly Donations section
                    if (recurringDonations.length > 0) {
                        html += `
                            <div class="bg-white rounded-lg shadow-lg p-8 border-2 border-yellow-400">
                                <div class="text-center mb-6">
                                    <div class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full inline-block mb-2">
                                        MOST IMPACT
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-900">Monthly Giving</h3>
                                    <p class="text-gray-600 mt-2">Provide sustained support that helps us plan long-term programs</p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    ${monthlyButtons}
                                </div>

                                <button onclick="processCustomDonation(true)" class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-3 px-6 rounded-lg transition-colors">
                                    Give a Custom Monthly Amount
                                </button>
                            </div>
                        `;
                    }
                }
                
                $('#donation-buttons-container').html(html);
            } else {
                $('#donation-buttons-container').html('<div class="col-span-full text-center py-12"><p class="text-gray-600 text-lg">No donation options available at this time.</p></div>');
            }
        },
        error: function() {
            $('#donation-buttons-loading').hide();
            $('#donation-buttons-container').html('<div class="col-span-full text-center py-12"><p class="text-red-600 text-lg">Unable to load donation options. Please try again later.</p></div>');
        }
    });
}

// Load featured events for homepage
function loadFeaturedEvents() {
    $.ajax({
        url: APP_URL + '/api/events',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#featured-events-loading').hide();
            
            if (response.success && response.data.length > 0) {
                // Show only first 3 events
                const featured = response.data.slice(0, 3);
                
                featured.forEach((event) => {
                    const eventDate = new Date(event.event_date);
                    const dateStr = eventDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                    
                    const card = `
                        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition-shadow duration-300">
                            <div class="h-48 bg-gray-100 rounded-lg mb-4 overflow-hidden">
                                <img src="${APP_URL}/images/events/${event.image_path}" alt="${event.title}" class="gallery-img">
                            </div>
                            <h3 class="text-xl font-bold mb-2" style="color: #805AD5;">${event.title}</h3>
                            <p class="text-gray-600 mb-4">${event.description.substring(0, 120)}...</p>
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fas fa-calendar mr-2" style="color: #FF6B1A;"></i>
                                <span>${dateStr}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600 mb-4">
                                <i class="fas fa-map-marker-alt mr-2" style="color: #FF6B1A;"></i>
                                <span>${event.location}</span>
                            </div>
                            <a href="${APP_URL}/events" class="inline-block bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-2 rounded-lg hover:shadow-lg transition-shadow">
                                Learn More
                            </a>
                        </div>
                    `;
                    
                    $('#featured-events-container').append(card);
                });
            } else {
                $('#featured-events-container').html('<div class="col-span-full text-center py-12"><p class="text-gray-600 text-lg">No upcoming events at this time.</p></div>');
            }
        },
        error: function() {
            $('#featured-events-loading').hide();
            $('#featured-events-error').removeClass('hidden');
        }
    });
}

// Payment placeholder functions
function processSponsorPayment(pkgId, pkgName, price) {
    // This function is now defined in sponsorship.php with full Stripe integration
    // Call it from the global scope
    if (typeof window.processSponsorPayment !== 'undefined') {
        window.processSponsorPayment(pkgId, pkgName, price);
    } else {
        alert('Sponsorship payment will be available in Phase 6.\nPackage: ' + pkgName + '\nAmount: $' + price.toLocaleString());
    }
}

function processDonationPayment(donationTypeId, amount, label, isRecurring) {
    // This function is now defined in donate.php with full Stripe integration
    // Call it from the global scope
    if (typeof window.processDonationPayment !== 'undefined') {
        window.processDonationPayment(donationTypeId, amount, label, isRecurring);
    } else {
        alert('Donation payment will be available in Phase 6.\nAmount: $' + amount + '\n' + label);
    }
}
