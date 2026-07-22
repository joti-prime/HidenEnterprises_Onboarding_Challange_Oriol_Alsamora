/**
 * PayPal Gateway with Apple Pay and Google Pay support
 */

// Enable PayPal checkout when the DOM is loaded
document.addEventListener('DOMLoaded', function() {
    initializePayPalCheckout();
});

// Also initialize on document ready for standard HTML documents
document.addEventListener('DOMContentLoaded', function() {
    initializePayPalCheckout();
});

/**
 * Initialize PayPal checkout with Apple Pay and Google Pay
 */
function initializePayPalCheckout() {
    // Look for checkout buttons
    const checkoutButtons = document.querySelectorAll('.paypal-ots-checkout-button');
    
    if (checkoutButtons.length === 0) {
        return;
    }
    
    // Load the PayPal SDK script
    loadPayPalScript().then(() => {
        checkoutButtons.forEach(button => {
            renderPayPalButton(button);
        });
    }).catch(error => {
        console.error('Failed to load PayPal SDK:', error);
    });
}

/**
 * Load the PayPal JavaScript SDK
 * 
 * @returns {Promise}
 */
function loadPayPalScript() {
    return new Promise((resolve, reject) => {
        // Check if script is already loaded
        if (window.paypal) {
            resolve();
            return;
        }
        
        // Get client ID from data attribute
        const clientId = document.querySelector('meta[name="paypal-client-id"]')?.getAttribute('content');
        
        if (!clientId) {
            reject(new Error('PayPal Client ID not found'));
            return;
        }
        
        // Get currency from data attribute
        const currency = document.querySelector('meta[name="payment-currency"]')?.getAttribute('content') || 'USD';
        
        // Create script element
        const script = document.createElement('script');
        script.src = `https://www.paypal.com/sdk/js?client-id=${clientId}&currency=${currency}&components=buttons,funding-eligibility,payment-fields,marks,applepay,googlepay`;
        script.async = true;
        
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load PayPal SDK'));
        
        document.head.appendChild(script);
    });
}

/**
 * Render PayPal button with Apple Pay and Google Pay
 * 
 * @param {HTMLElement} container Button container
 */
function renderPayPalButton(container) {
    // Get payment data from container attributes
    const amount = parseFloat(container.getAttribute('data-amount'));
    const currency = container.getAttribute('data-currency') || 'USD';
    const paymentUrl = container.getAttribute('data-payment-url');
    const isSubscription = container.getAttribute('data-subscription') === 'true';
    
    if (!amount || !paymentUrl) {
        console.error('Missing required payment data');
        return;
    }
    
    // Create a wrapper for PayPal buttons
    const buttonWrapper = document.createElement('div');
    buttonWrapper.className = 'paypal-button-container';
    container.appendChild(buttonWrapper);
    
    // PayPal Button Options
    const paypalOptions = {
        style: {
            layout: 'vertical',
            color: 'blue',
            shape: 'rect',
            label: 'paypal'
        },
        
        // When button is clicked, initiate payment
        createOrder: function() {
            return fetch(paymentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    payment_method: 'paypal-ots',
                    amount: amount,
                    currency: currency,
                    is_subscription: isSubscription
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.error) {
                    throw new Error(data.error);
                }
                return data.id; // Return PayPal order ID
            });
        },
        
        // Show a spinner when the payment is processing
        onApprove: function() {
            // Show loading spinner
            container.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Processing payment...</p></div>';
            
            // The payment will be processed server-side when PayPal redirects back
            return Promise.resolve();
        },
        
        // Handle payment errors
        onError: function(err) {
            console.error('PayPal Error:', err);
            
            // Show error message
            container.innerHTML = `<div class="alert alert-danger">Payment error: ${err.message || 'Unknown error'}</div>`;
        }
    };
    
    // Render PayPal buttons
    window.paypal.Buttons(paypalOptions).render(buttonWrapper);
    
    // Apple Pay support
    if (window.paypal.Applepay && window.ApplePaySession && ApplePaySession.canMakePayments()) {
        renderApplePay(container, amount, currency, paymentUrl, isSubscription);
    }
    
    // Google Pay support
    if (window.paypal.Googlepay && window.google) {
        renderGooglePay(container, amount, currency, paymentUrl, isSubscription);
    }
}

/**
 * Render Apple Pay button
 * 
 * @param {HTMLElement} container Button container
 * @param {number} amount Payment amount
 * @param {string} currency Currency code
 * @param {string} paymentUrl Payment endpoint URL
 * @param {boolean} isSubscription Whether this is a subscription payment
 */
function renderApplePay(container, amount, currency, paymentUrl, isSubscription) {
    // Create a container for Apple Pay
    const applePayContainer = document.createElement('div');
    applePayContainer.className = 'apple-pay-container mt-2';
    container.appendChild(applePayContainer);
    
    // Apple Pay button options
    const applePayOptions = {
        fundingSource: window.paypal.FUNDING.APPLEPAY,
        style: {
            label: 'pay',
            color: 'black'
        },
        
        createOrder: function() {
            return fetch(paymentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    payment_method: 'paypal-ots',
                    payment_type: 'applepay',
                    amount: amount,
                    currency: currency,
                    is_subscription: isSubscription
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.error) {
                    throw new Error(data.error);
                }
                return data.id; // Return PayPal order ID
            });
        },
        
        onApprove: function() {
            // Show loading spinner
            container.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Processing payment...</p></div>';
            
            // The payment will be processed server-side when PayPal redirects back
            return Promise.resolve();
        },
        
        onError: function(err) {
            console.error('Apple Pay Error:', err);
            
            // Show error message
            container.innerHTML = `<div class="alert alert-danger">Apple Pay error: ${err.message || 'Unknown error'}</div>`;
        }
    };
    
    // Render Apple Pay button
    window.paypal.Buttons(applePayOptions).render(applePayContainer);
}

/**
 * Render Google Pay button
 * 
 * @param {HTMLElement} container Button container
 * @param {number} amount Payment amount
 * @param {string} currency Currency code
 * @param {string} paymentUrl Payment endpoint URL
 * @param {boolean} isSubscription Whether this is a subscription payment
 */
function renderGooglePay(container, amount, currency, paymentUrl, isSubscription) {
    // Create a container for Google Pay
    const googlePayContainer = document.createElement('div');
    googlePayContainer.className = 'google-pay-container mt-2';
    container.appendChild(googlePayContainer);
    
    // Google Pay button options
    const googlePayOptions = {
        fundingSource: window.paypal.FUNDING.GOOGLEPAY,
        style: {
            color: 'black'
        },
        
        createOrder: function() {
            return fetch(paymentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    payment_method: 'paypal-ots',
                    payment_type: 'googlepay',
                    amount: amount,
                    currency: currency,
                    is_subscription: isSubscription
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.error) {
                    throw new Error(data.error);
                }
                return data.id; // Return PayPal order ID
            });
        },
        
        onApprove: function() {
            // Show loading spinner
            container.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Processing payment...</p></div>';
            
            // The payment will be processed server-side when PayPal redirects back
            return Promise.resolve();
        },
        
        onError: function(err) {
            console.error('Google Pay Error:', err);
            
            // Show error message
            container.innerHTML = `<div class="alert alert-danger">Google Pay error: ${err.message || 'Unknown error'}</div>`;
        }
    };
    
    // Render Google Pay button
    window.paypal.Buttons(googlePayOptions).render(googlePayContainer);
}