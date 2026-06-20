<?php
/**
 * File: store.php
 * Location: /tour_update/store.php
 *
 * Merchandise store page.
 * In Phase 5, this will integrate with the shopping cart and checkout API.
 */

$page_title = 'Merchandise Store';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center">
            <i class="fas fa-store text-6xl mb-6 drop-shadow-lg"></i>
            <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">Tour de Roar Store</h1>
            <p class="text-xl drop-shadow">Show your support with official merchandise</p>
        </div>
    </div>
</section>

<!-- Products Section (Loaded from API) -->
<section class="content-section bg-white">
    <div class="container mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12" style="color: #805AD5;">Official Merchandise</h2>
        
        <!-- Loading Spinner -->
        <div id="products-loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4" style="border-color: #FF6B1A;"></div>
            <p class="mt-4 text-gray-600 text-lg">Loading products...</p>
        </div>
        
        <!-- Error Message -->
        <div id="products-error" class="hidden bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-4"></i>
                <div>
                    <h3 class="font-bold text-red-800 mb-1">Unable to load products</h3>
                    <p class="text-red-700">Please try refreshing the page. If the problem persists, contact us.</p>
                </div>
            </div>
        </div>
        
        <!-- Products Container (populated by AJAX) -->
        <div id="products-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8"></div>
    </div>
</section>

<script>
// Load products from API
$(document).ready(function() {
    $.ajax({
        url: APP_URL + '/api/products',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#products-loading').hide();
            
            if (response.success && response.data.length > 0) {
                const colors = [
                    {border: '#3182CE', textColor: '#3182CE', btnGradient: 'linear-gradient(45deg, #68D391, #3182CE)'},
                    {border: '#F6E05E', textColor: '#805AD5', btnGradient: 'linear-gradient(45deg, #F6E05E, #FF6B1A)'},
                    {border: '#E53E3E', textColor: '#E53E3E', btnGradient: 'linear-gradient(45deg, #E53E3E, #805AD5)'},
                    {border: '#3182CE', textColor: '#3182CE', btnGradient: 'linear-gradient(45deg, #3182CE, #805AD5)'}
                ];
                
                response.data.forEach((product, index) => {
                    const color = colors[index % colors.length];
                    const sizes = product.sizes.join(', ');
                    const imagePath = product.image_path ? APP_URL + '/images/products/' + product.image_path : APP_URL + '/images/placeholder.jpg';
                    
                    const card = `
                        <div class="bg-white rounded-xl shadow-lg p-4 border-t-4 hover:shadow-xl transition-all duration-300" style="border-color: ${color.border};">
                            <div class="h-48 bg-gray-100 rounded-lg mb-4 overflow-hidden shadow-md">
                                <img src="${imagePath}" alt="${product.name}" class="w-full h-full object-cover">
                            </div>
                            <h3 class="font-bold mb-2 text-xl" style="color: ${color.textColor};">${product.name}</h3>
                            <p class="text-gray-600 text-sm mb-2">${product.description}</p>
                            <div class="mb-3">
                                <span class="text-sm text-gray-500">Sizes: ${sizes}</span>
                            </div>
                            <p class="font-bold text-2xl mb-3" style="color: #FF6B1A;">$${parseFloat(product.price).toFixed(2)}</p>
                            <button onclick="processPayment(${product.id}, '${product.name}', ${product.price})" class="w-full py-2 rounded-lg font-bold text-white shadow-md transition-all duration-300 hover:shadow-lg hover:scale-105" style="background: ${color.btnGradient};">
                                <i class="fas fa-shopping-cart mr-2"></i>Buy Now
                            </button>
                        </div>
                    `;
                    
                    $('#products-container').append(card);
                });
            } else {
                $('#products-container').html('<div class="col-span-full text-center py-12"><p class="text-gray-600 text-lg">No products available at this time.</p></div>');
            }
        },
        error: function() {
            $('#products-loading').hide();
            $('#products-error').removeClass('hidden');
        }
    });
});

function processPayment(productId, productName, price) {
    // Check if user is logged in
    if (!IS_LOGGED_IN) {
        window.location.href = APP_URL + '/account/login?redirect=store';
        return;
    }
    
    // Store current product
    currentProduct = {
        id: productId,
        name: productName,
        price: parseFloat(price)
    };
    
    // Populate modal
    document.getElementById('product-id').value = productId;
    document.getElementById('product-name-label').textContent = productName;
    document.getElementById('product_quantity').value = 1;
    updateOrderTotal();
    
    // Pre-fill with user data if available
    const user = <?= json_encode(get_logged_in_user() ?? []); ?>;
    if (user.first_name) {
        document.getElementById('shipping_name').value = (user.first_name + ' ' + user.last_name).trim();
    }
    if (user.email) {
        const emailField = document.getElementById('shipping_email');
        emailField.value = user.email;
        emailField.readOnly = true;
        emailField.style.backgroundColor = '#f7fafc';
        emailField.style.cursor = 'not-allowed';
    }
    
    // Create Stripe card element if not already created
    if (!storeCardElement) {
        storeCardElement = storeElements.create('card', {
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
        storeCardElement.mount('#store-card-element');
        
        storeCardElement.on('change', function(event) {
            const displayError = document.getElementById('store-card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }
    
    // Show modal
    document.getElementById('store-modal').style.display = 'flex';
    document.getElementById('modal-success').style.display = 'none';
    document.getElementById('modal-error').style.display = 'none';
}

function updateOrderTotal() {
    if (!currentProduct) return;
    
    const quantity = parseInt(document.getElementById('product_quantity').value) || 1;
    const total = currentProduct.price * quantity;
    
    document.getElementById('quantity-label').textContent = quantity;
    document.getElementById('order-amount').textContent = '$' + total.toFixed(2);
}

function closeStoreModal() {
    document.getElementById('store-modal').style.display = 'none';
    document.getElementById('store-form').reset();
    document.getElementById('store-card-errors').textContent = '';
    currentProduct = null;
}

// Handle store form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('store-form');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submit-order');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        
        document.getElementById('modal-success').style.display = 'none';
        document.getElementById('modal-error').style.display = 'none';
        
        // Validate card is complete before proceeding
        const cardComplete = await storeStripe.createPaymentMethod({
            type: 'card',
            card: storeCardElement,
        }).catch(err => {
            return null;
        });
        
        if (!cardComplete || cardComplete.error) {
            const errorMsg = cardComplete?.error?.message || 'Please enter valid card details';
            document.getElementById('error-message').textContent = errorMsg;
            document.getElementById('modal-error').style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class=\"fas fa-shopping-cart mr-2\"></i>Complete Purchase';
            return;
        }
        
        try {
            const quantity = parseInt(document.getElementById('product_quantity').value);
            const size = document.getElementById('product_size').value;
            
            // Create PaymentIntent on server
            const response = await fetch(APP_URL + '/api/payments/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: document.querySelector('input[name="csrf_token"]').value,
                    items: [
                        {
                            product_id: currentProduct.id,
                            quantity: quantity,
                            size: size || null
                        }
                    ],
                    shipping_name: document.getElementById('shipping_name').value,
                    shipping_email: document.getElementById('shipping_email').value,
                    shipping_address: document.getElementById('shipping_address').value
                })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to initialize payment');
            }
            
            // Confirm payment with Stripe
            const {error} = await storeStripe.confirmCardPayment(data.data.client_secret, {
                payment_method: {
                    card: storeCardElement,
                    billing_details: {
                        name: document.getElementById('shipping_name').value,
                        email: document.getElementById('shipping_email').value,
                        address: {
                            line1: document.getElementById('shipping_address').value,
                        },
                    },
                },
            });
            
            if (error) {
                throw new Error(error.message);
            }
            
            // Success!
            document.getElementById('success-message').textContent = 'Order placed successfully! We\'ll send a confirmation email with tracking details shortly.';
            document.getElementById('modal-success').style.display = 'block';
            
            // Close modal after 3 seconds and redirect to transactions
            setTimeout(function() {
                closeStoreModal();
                window.location.href = APP_URL + '/account/transactions?filter=store';
            }, 4000);
            
        } catch (error) {
            document.getElementById('error-message').textContent = error.message;
            document.getElementById('modal-error').style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Complete Purchase';
        }
    });
});
</script>

<!-- Store Payment Modal -->
<div id="store-modal" class="payment-modal-overlay" style="display: none;">
    <div class="payment-modal-content">
        <div class="payment-modal-header">
            <h2 id="modal-title" class="payment-modal-title">
                <i class="fas fa-shopping-cart mr-2"></i>Complete Your Purchase
            </h2>
            <button type="button" class="payment-modal-close" onclick="closeStoreModal()">
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
            
            <form id="store-form">
                <input type="hidden" id="product-id" name="product_id">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <!-- Order Summary -->
                <div class="payment-summary">
                    <h3 style="color: #FF6B1A; font-weight: 700; margin-bottom: 0.75rem;">Order Summary</h3>
                    <div style="margin-bottom: 0.5rem;">
                        <span id="product-name-label" style="font-weight: 600;">-</span>
                    </div>
                    <div class="payment-summary-row">
                        <span>Quantity: <span id="quantity-label">1</span></span>
                        <strong id="order-amount" style="color: #3182CE; font-size: 1.5rem;">$0.00</strong>
                    </div>
                </div>
                
                <!-- Product Options -->
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: #FF6B1A; font-weight: 700; margin-bottom: 0.75rem;">Product Options</h3>
                    
                    <div class="form-group">
                        <label>Size (if applicable)</label>
                        <select id="product_size" name="size" class="form-input">
                            <option value="">Select Size</option>
                            <option value="S">Small (S)</option>
                            <option value="M">Medium (M)</option>
                            <option value="L">Large (L)</option>
                            <option value="XL">Extra Large (XL)</option>
                            <option value="XXL">2XL</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" id="product_quantity" name="quantity" class="form-input" min="1" max="10" value="1" required onchange="updateOrderTotal()">
                    </div>
                </div>
                
                <!-- Shipping Information -->
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: #FF6B1A; font-weight: 700; margin-bottom: 0.75rem;">Shipping Information</h3>
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" id="shipping_name" name="shipping_name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" id="shipping_email" name="shipping_email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Shipping Address *</label>
                        <textarea id="shipping_address" name="shipping_address" class="form-input" rows="3" placeholder="Street Address, City, State, ZIP Code" required></textarea>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: #FF6B1A; font-weight: 700; margin-bottom: 0.75rem;">Payment Information</h3>                    <div style="background: #EBF8FF; border-left: 4px solid #3182CE; padding: 12px 16px; margin-bottom: 1rem; border-radius: 4px;">
                        <div style="display: flex; align-items: start; gap: 8px;">
                            <i class="fas fa-shield-alt" style="color: #3182CE; margin-top: 2px;"></i>
                            <div style="font-size: 0.875rem; color: #2C5282; line-height: 1.5;">
                                <strong>Secure Payment:</strong> Your card details are encrypted and processed securely by Stripe. 
                                We never store or have access to your card information.
                            </div>
                        </div>
                    </div>                    <div id="store-card-element" class="stripe-card-element"></div>
                    <div id="store-card-errors" class="stripe-card-errors"></div>
                </div>
                
                <button type="submit" id="submit-order" class="payment-submit-btn">
                    <i class="fas fa-lock mr-2"></i>Complete Purchase
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
    background: linear-gradient(135deg, #FF6B1A 0%, #3182CE 100%);
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
    background: linear-gradient(135deg, #fff5eb 0%, #fee2e2 100%);
    border-left: 4px solid #FF6B1A;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.payment-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.5rem;
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
    border-color: #FF6B1A;
}

textarea.form-input {
    resize: vertical;
    font-family: inherit;
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
    background: linear-gradient(135deg, #FF6B1A 0%, #3182CE 100%);
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
    box-shadow: 0 10px 20px rgba(255, 107, 26, 0.3);
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

<script>
// Initialize Stripe for store
const storeStripe = Stripe(STRIPE_PK);
const storeElements = storeStripe.elements();
let storeCardElement = null;
let currentProduct = null;
</script>

<!-- Secure Payment Info -->
<section class="content-section bg-gradient-to-r from-blue-50 to-purple-50">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-4xl font-bold mb-8" style="color: #805AD5;">Secure Payment Processing</h2>
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-lg">
            <i class="fas fa-lock text-4xl mb-4" style="color: #68D391;"></i>
            <p class="text-lg text-gray-700 mb-4">
                All purchases are processed securely through Stripe payment processing. 
                Your payment information is encrypted and never stored on our servers.
            </p>
            <p class="text-sm text-gray-600">
                For questions about orders, contact us at <strong>info@tourderoar.org</strong> or <strong>(972) 979-4608</strong>
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
