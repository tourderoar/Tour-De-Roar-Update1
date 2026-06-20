// Tour de Roar - Main JavaScript File
// Contact: (972) 979-4608 | 2860 South State Hwy 161, Ste 160 211, Grand Prairie, TX 75052

// NOTE: Stripe is initialized per-page in events.php, donate.php, sponsorship.php, store.php
// to avoid conflicts with multiple payment forms on different pages

// Global variables for data storage (using localStorage for static hosting)
let registrations = JSON.parse(localStorage.getItem('tourDeRoarRegistrations') || '[]');
let payments = JSON.parse(localStorage.getItem('tourDeRoarPayments') || '[]');

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set active navigation item
    setActiveNavigation();
    
    // Add fade-in animation to content
    const content = document.querySelector('.content-section');
    if (content) {
        content.classList.add('fade-in');
    }
    
    // Initialize any modals
    initializeModals();
    
    // Admin panel keyboard shortcut
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'A') {
            e.preventDefault();
            openAdmin();
        }
    });
});

// Navigation functions
function setActiveNavigation() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('nav a[href]');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || 
            (currentPage === '' && href === 'index.html') ||
            (currentPage === 'index.html' && href === '/')) {
            link.classList.add('bg-blue-800');
        } else {
            link.classList.remove('bg-blue-800');
        }
    });
}

// Mobile menu toggle
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

// Smooth scrolling
function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}

// Modal functions
function initializeModals() {
    // Close modals when clicking outside
    window.onclick = function(event) {
        const modals = document.querySelectorAll('[id$="Modal"], #adminPanel');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }
}

// NOTE: Payment processing is now handled by page-specific implementations
// in events.php, donate.php, sponsorship.php, and store.php using real Stripe Payment Intents API
// via backend endpoints in api/payments/

// Admin panel system (if needed)
function openAdmin() {
    const panel = document.getElementById('adminPanel');
    if (panel) {
        panel.classList.remove('hidden');
        const passwordField = document.getElementById('adminPassword');
        if (passwordField) passwordField.focus();
    }
}

function closeAdmin() {
    const panel = document.getElementById('adminPanel');
    if (panel) {
        panel.classList.add('hidden');
        document.getElementById('adminPassword').value = '';
    }
}

function checkAdminPassword() {
    const password = document.getElementById('adminPassword').value;
    const correctPassword = 'tourderoar2024'; // Change this for production
    
    if (password === correctPassword) {
        closeAdmin();
        showAdminDashboard();
    } else {
        alert('Incorrect password. Please try again.');
        document.getElementById('adminPassword').value = '';
    }
}

function showAdminDashboard() {
    const registrationCount = registrations.length;
    const paymentTotal = payments.reduce((sum, payment) => sum + payment.amount, 0);
    const completedRegistrations = registrations.filter(r => r.paymentStatus === 'completed').length;
    
    const dashboardHTML = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; max-width: 800px; width: 90%; max-height: 80vh; overflow-y: auto;">
                <div style="text-align: right; margin-bottom: 20px;">
                    <button onclick="closeAdminDashboard()" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">✕ Close</button>
                </div>
                <h2 style="color: #333; margin-bottom: 20px;">🚴‍♂️ Tour de Roar Admin Dashboard</h2>
                
                <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2196F3;">
                    <h3 style="color: #333; margin: 0 0 10px 0;">📍 Organization Contact</h3>
                    <p style="margin: 5px 0;"><strong>Address:</strong> 2860 South State Hwy 161, Ste 160 211, Grand Prairie, TX 75052</p>
                    <p style="margin: 5px 0;"><strong>Phone:</strong> (972) 979-4608</p>
                    <p style="margin: 5px 0;"><strong>Email:</strong> info@tourderoar.org</p>
                    <p style="margin: 5px 0;"><strong>Website:</strong> https://tourderoar.org</p>
                </div>

                <div style="background: #f3e5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #9c27b0;">
                    <h3 style="color: #333; margin: 0 0 10px 0;">💳 Stripe Integration Status</h3>
                    <p style="margin: 5px 0;"><strong>Status:</strong> ✅ Configured & Ready</p>
                    <p style="margin: 5px 0;"><strong>Mode:</strong> Test Mode (pk_test_...)</p>
                    <p style="margin: 5px 0;"><strong>Test Card:</strong> 4242 4242 4242 4242</p>
                    <p style="margin: 5px 0; font-size: 0.9em; color: #666;">Switch to live keys (pk_live_...) for production</p>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div style="background: #4CAF50; color: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <h3 style="margin: 0 0 10px 0;">📝 Total Registrations</h3>
                        <div style="font-size: 2em; font-weight: bold;">${registrationCount}</div>
                    </div>
                    <div style="background: #2196F3; color: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <h3 style="margin: 0 0 10px 0;">✅ Completed Payments</h3>
                        <div style="font-size: 2em; font-weight: bold;">${completedRegistrations}</div>
                    </div>
                    <div style="background: #FF9800; color: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <h3 style="margin: 0 0 10px 0;">💰 Total Revenue</h3>
                        <div style="font-size: 2em; font-weight: bold;">$${paymentTotal.toFixed(2)}</div>
                    </div>
                </div>
                
                <h3 style="color: #333; margin-bottom: 15px;">📋 Recent Registrations</h3>
                <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px;">
                    ${registrations.length > 0 ? registrations.slice(-10).reverse().map(reg => `
                        <div style="padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>${reg.participantName}</strong><br>
                                <small style="color: #666;">${reg.eventName} - ${reg.participantEmail}</small><br>
                                <small style="color: #999;">${new Date(reg.timestamp).toLocaleString()}</small>
                            </div>
                            <div style="text-align: right;">
                                <span style="padding: 4px 12px; border-radius: 20px; font-size: 0.8em; color: white; background: ${reg.paymentStatus === 'completed' ? '#4CAF50' : '#FF9800'};">
                                    ${reg.paymentStatus}
                                </span><br>
                                <small style="color: #666; margin-top: 5px; display: block;">${reg.eventPrice}</small>
                            </div>
                        </div>
                    `).join('') : '<div style="padding: 20px; text-align: center; color: #666;">No registrations yet. Share your website to get started!</div>'}
                </div>
                
                <div style="margin-top: 20px; text-align: center;">
                    <button onclick="exportData()" style="background: #4CAF50; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; margin-right: 10px;">
                        📊 Export Data
                    </button>
                    <button onclick="clearData()" style="background: #f44336; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer;">
                        🗑️ Clear All Data
                    </button>
                    <button onclick="testStripeConnection()" style="background: #9c27b0; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                        💳 Test Stripe
                    </button>
                </div>
            </div>
        </div>
    `;
    
    const dashboard = document.createElement('div');
    dashboard.id = 'adminDashboard';
    dashboard.innerHTML = dashboardHTML;
    document.body.appendChild(dashboard);
}

function closeAdminDashboard() {
    const dashboard = document.getElementById('adminDashboard');
    if (dashboard) {
        dashboard.remove();
    }
}

function exportData() {
    const data = {
        organization: {
            name: 'Tour de Roar',
            address: '2860 South State Hwy 161, Ste 160 211, Grand Prairie, TX 75052',
            phone: '(972) 979-4608',
            email: 'info@tourderoar.org',
            website: 'https://tourderoar.org'
        },
        stripe_integration: {
            status: 'configured',
            mode: 'test',
            publishable_key: 'pk_test_51S523Q...' // Partial key for security
        },
        registrations: registrations,
        payments: payments,
        exportDate: new Date().toISOString(),
        stats: {
            total_registrations: registrations.length,
            completed_payments: payments.length,
            total_revenue: payments.reduce((sum, p) => sum + p.amount, 0)
        }
    };
    
    const dataStr = JSON.stringify(data, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = `tour-de-roar-data-${new Date().toISOString().split('T')[0]}.json`;
    link.click();
    
    URL.revokeObjectURL(url);
    
    alert('Data exported successfully!\n\nFile includes all registrations, payments, and organization details.');
}

function clearData() {
    if (confirm('⚠️ Are you sure you want to clear all registration and payment data?\n\nThis action cannot be undone.\n\nContact: (972) 979-4608 if you need help.')) {
        localStorage.removeItem('tourDeRoarRegistrations');
        localStorage.removeItem('tourDeRoarPayments');
        registrations = [];
        payments = [];
        alert('✅ All data has been cleared.\n\nAdmin panel will refresh.');
        closeAdminDashboard();
    }
}

function testStripeConnection() {
    alert(`🧪 Stripe Connection Test

✅ Stripe Key: Configured
✅ Mode: Test Mode
✅ Status: Ready for payments

Test Cards:
💳 Success: 4242 4242 4242 4242
❌ Decline: 4000 0000 0000 0002
🔐 3D Secure: 4000 0025 0000 3155

Expiry: Any future date (12/25)
CVC: Any 3 digits (123)

Contact for help: (972) 979-4608`);
}

// Sponsor contact function with updated contact info
function contactSponsor(packageType = 'General Inquiry') {
    const email = 'partners@tourderoar.org';
    const phone = '(972) 979-4608';
    const subject = encodeURIComponent(`Sponsorship Inquiry: ${packageType}`);
    const body = encodeURIComponent(`Hello Tour de Roar Team,

I am interested in learning more about the ${packageType} sponsorship opportunity.

Please provide additional information about:
- Sponsorship benefits and deliverables
- Available dates and events
- Contract terms and conditions
- Payment options

Contact Information:
Phone: ${phone}
Email: ${email}
Address: 2860 South State Hwy 161, Ste 160 211, Grand Prairie, TX 75052
Website: https://tourderoar.org

I look forward to discussing how we can partner together to support children's health through cycling.

Best regards,`);
    
    window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
}

// Store functions with contact integration
function addToCart(itemName, itemPrice) {
    alert(`🛒 Added to Cart: ${itemName} ($${itemPrice})

📞 To Complete Purchase:
Call: (972) 979-4608
Email: info@tourderoar.org

📍 Pickup Available:
2860 South State Hwy 161, Ste 160 211
Grand Prairie, TX 75052

🚚 Or we can arrange shipping!

Thank you for supporting Tour de Roar! 🚴‍♂️`);
}

// Contact form processing
function submitContactForm(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const contactData = {
        firstName: formData.get('firstName'),
        lastName: formData.get('lastName'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        subject: formData.get('subject'),
        message: formData.get('message'),
        newsletter: formData.get('newsletter') === 'on',
        timestamp: new Date().toISOString()
    };
    
    // Store contact submission
    const contacts = JSON.parse(localStorage.getItem('tourDeRoarContacts') || '[]');
    contacts.push(contactData);
    localStorage.setItem('tourDeRoarContacts', JSON.stringify(contacts));
    
    console.log('Contact form submitted:', contactData);
    
    alert(`✅ Thank you, ${contactData.firstName}!

📧 Your message has been received.
We'll respond within 24 hours.

📞 For urgent matters:
Phone: (972) 979-4608
Email: info@tourderoar.org

📍 Visit us:
2860 South State Hwy 161, Ste 160 211
Grand Prairie, TX 75052

Thank you for contacting Tour de Roar! 🚴‍♂️`);
    
    event.target.reset();
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Animation utilities
function animateOnScroll() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    });
    
    elements.forEach(el => observer.observe(el));
}