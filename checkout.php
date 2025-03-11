<?php
// filepath: /c:/xampp/htdocs/website/checkout.php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get the user ID
$user_id = $_SESSION['user_id'];

// Check if cart exists in session
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<p class='text-center text-gray-600 p-8'>Your cart is empty. <a href='index.php#shop' class='text-blue-500'>Continue shopping</a></p>";
    exit();
}

// Use session cart data instead of database query
$order_items = [];
$total_amount = 0;
$item_count = 0;

foreach ($_SESSION['cart'] as $item) {
    $order_items[] = [
        'MEAT_PART_ID' => $item['meat_part_id'],
        'MEAT_PART_NAME' => $item['product_name'],
        'QTY' => $item['quantity'],
        'UNIT_OF_MEASURE' => $item['unit'],
        'UNIT_PRICE' => $item['unit_price']
    ];
    
    $unit_of_measure = strtolower($item['unit']);
    
    // Calculate total price
    if ($unit_of_measure === 'g') {
        $total_price = ($item['unit_price'] * $item['quantity']) / 1000;
    } else {
        $total_price = $item['quantity'] * $item['unit_price'];
    }

    $total_amount += $total_price;
    $item_count++;
}


// Fetch payment options from database
$payments_query = "SELECT PAYMENT_ID, PAYMENT_METHOD FROM PAYMENT";
$payments = $conn->query($payments_query);

// Calculate shipping fee
$shipping_fee = 50.00;
$final_total = $total_amount + $shipping_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | E-MEAT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="icon" type="image/png" href="IMAGES/RED LOGO.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.5.0/remixicon.css">
    <link rel="stylesheet" href="CCS/style.css?v=<?php echo time(); ?>">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #f9fafb;
        }
        .shipping-option:checked + label {
            border-color: #ef4444;
            background-color: #fef2f2;
        }
        .payment-option:checked + label {
            border-color: #ef4444;
            background-color: #fef2f2;
        }
        .checkout-btn {
            transition: all 0.3s ease;
        }
        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="bg-gray-50">
    <header>
        <nav class="nav container">
            <a href="index.php" class="nav__logo">
                <img src="IMAGES/WHITE LOGO.png" alt="Emeat Logo" class="nav__logo-img">
                EMEAT
            </a>
            <ul class="nav__menu">
                <li><a href="index.php#home">Home</a></li>
                <li><a href="index.php#feature">About</a></li>
                <li><a href="index.php#shop">Shop</a></li>
                <li><a href="index.php#contact">Contact</a></li>
            </ul>
            <div class="nav-icons">
                <a href="index.php#shop"><i class="ri-search-line"></i></a>
                <a href="cart.php"><i class="ri-shopping-cart-line"></i></a>
            </div>
        </nav>
    </header>

    <!-- Progress Bar -->
    <div class="max-w-5xl mx-auto mt-8 px-4">
        <div class="flex items-center justify-between">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 bg-red-600 text-white rounded-full flex items-center justify-center">
                    <i class="ri-shopping-cart-line"></i>
                </div>
                <span class="text-sm mt-1 text-gray-600">Cart</span>
            </div>
            <div class="flex-1 h-1 bg-red-200 mx-2">
                <div class="h-full bg-red-600 w-full"></div>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 bg-red-600 text-white rounded-full flex items-center justify-center">
                    <i class="ri-bank-card-line"></i>
                </div>
                <span class="text-sm mt-1 text-gray-600">Checkout</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 mx-2">
                <div class="h-full bg-gray-200"></div>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center">
                    <i class="ri-check-line"></i>
                </div>
                <span class="text-sm mt-1 text-gray-400">Complete</span>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto p-4 md:p-6 mb-20">
        <h1 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-8">Checkout</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Order Summary -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-800">Order Summary</h2>
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-1 rounded-full">
                                <?= $item_count ?> <?= $item_count === 1 ? 'Item' : 'Items' ?>
                            </span>
                        </div>
                        
                        <!-- Order Items -->
                        <div class="space-y-4 mb-6">
                            <?php foreach ($order_items as $item): ?>
                                <?php
                                $unit_of_measure = strtolower($item['UNIT_OF_MEASURE']);
                                
                                // Calculate total price
                                if ($unit_of_measure === 'g') {
                                    $total_price = ($item['UNIT_PRICE'] * $item['QTY']) / 1000;
                                } else {
                                    $total_price = $item['QTY'] * $item['UNIT_PRICE'];
                                }
                                ?>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <div class="w-12 h-12 flex-shrink-0 rounded-full bg-red-50 flex items-center justify-center text-red-600 mr-4">
                                        <i class="ri-knife-line"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-800"><?= htmlspecialchars($item['MEAT_PART_NAME']) ?></h3>
                                        <p class="text-sm text-gray-500">
                                            <?= $item['QTY'] ?> <?= strtoupper($item['UNIT_OF_MEASURE']) ?> x ₱<?= number_format($item['UNIT_PRICE'], 2) ?>
                                        </p>
                                    </div>
                                    <div class="font-semibold text-gray-800">
                                        ₱<?= number_format($total_price, 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- ADD this new shipping fee display section instead -->
                        <div class="mb-6">
                            <h3 class="text-md font-semibold text-gray-700 mb-3">
                                <i class="ri-truck-line mr-2 text-gray-500"></i>Shipping Details
                            </h3>
                            
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 mr-3">
                                            <i class="ri-truck-line"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800">Standard Delivery</p>
                                            <p class="text-sm text-gray-500">Delivery in 1-3 business days</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-gray-800">₱<?= number_format($shipping_fee, 2) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                            
                            <!-- Payment Options -->
                            <div class="mb-6">
                                <h3 class="text-md font-semibold text-gray-700 mb-3">
                                    <i class="ri-bank-card-line mr-2 text-gray-500"></i>Payment Method
                                </h3>
                                
                                <div class="space-y-3">
                                    <?php $payments->data_seek(0); ?>
                                    <?php while ($payment = $payments->fetch_assoc()): ?>
                                        <div class="relative">
                                            <input type="radio" name="payment-method" id="payment-<?= $payment['PAYMENT_ID'] ?>" 
                                                value="<?= $payment['PAYMENT_ID'] ?>" class="payment-option sr-only"
                                                <?= ($payment['PAYMENT_ID'] == 1) ? 'checked' : '' ?>
                                                data-payment-type="<?= strtolower($payment['PAYMENT_METHOD']) === 'gcash' ? 'gcash' : '' ?>">
                                            <label for="payment-<?= $payment['PAYMENT_ID'] ?>" 
                                                class="block p-4 border rounded-lg cursor-pointer hover:border-red-200 hover:bg-red-50 transition">
                                                <div class="flex items-center">
                                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 mr-3">
                                                        <i class="<?= $payment['PAYMENT_METHOD'] === 'Cash on Delivery' ? 'ri-money-dollar-box-line' : 
                                                            ($payment['PAYMENT_METHOD'] === 'Credit Card' ? 'ri-bank-card-line' : 
                                                            ($payment['PAYMENT_METHOD'] === 'GCash' ? 'ri-smartphone-line' : 'ri-wallet-3-line')) ?>"></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-800"><?= htmlspecialchars($payment['PAYMENT_METHOD']) ?></p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="border-t border-gray-200 p-6">
                        <div class="flex justify-between mb-4">
                            <a href="cart.php" class="flex items-center font-medium text-gray-600 hover:text-red-600 transition-colors">
                                <i class="ri-arrow-left-line mr-1"></i> Return to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Order Total -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md sticky top-6">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Total</h2>
                        
                        <div class="space-y-2 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium">₱<?= number_format($total_amount, 2) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-medium">₱<?= number_format($shipping_fee, 2) ?></span>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-3 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-800 font-medium">Total</span>
                                <span class="text-2xl text-red-600 font-bold">₱<?= number_format($final_total, 2) ?></span>
                            </div>
                            <p class="text-gray-500 text-xs text-right">Including taxes</p>
                        </div>
                        
                        <!-- GCash Payment Form - Initially Hidden -->
                        <div id="gcash-payment-form" class="hidden mb-6 border-2 border-blue-300 rounded-lg p-4 bg-blue-50 shadow-md">
                            <h3 class="text-md font-semibold text-blue-700 mb-3 flex items-center">
                                <i class="ri-smartphone-line mr-2"></i>GCash Payment
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label for="gcash-number" class="block text-sm font-medium text-gray-700 mb-1">GCash Number</label>
                                    <input type="tel" id="gcash-number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                        placeholder="09XX XXX XXXX" maxlength="11" pattern="[0-9]{11}">
                                    <p class="text-xs text-gray-500 mt-1">Enter your 11-digit GCash number</p>
                                </div>
                                <div>
                                    <label for="gcash-amount" class="block text-sm font-medium text-gray-700 mb-1">Amount to Pay</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₱</span>
                                        <input type="text" id="gcash-amount" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md bg-gray-100" 
                                            value="<?= number_format($final_total, 2) ?>" readonly>
                                    </div>
                                </div>
                                <div class="bg-white p-3 rounded-md border border-gray-200">
                                    <p class="text-sm text-gray-600 mb-1">Instructions:</p>
                                    <ol class="text-xs text-gray-500 list-decimal pl-4">
                                        <li>Enter your GCash number</li>
                                        <li>Click "Complete Order" below</li>
                                        <li>We'll verify your payment</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        
                        <button id="complete-purchase-btn" class="checkout-btn w-full bg-gradient-to-r from-red-500 to-red-700 text-white py-4 rounded-lg font-bold hover:from-red-600 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 shadow-lg">
                            <div class="flex items-center justify-center">
                                <i class="ri-secure-payment-line mr-2"></i> Complete Order
                            </div>
                        </button>
                        
                        <div class="flex items-center justify-center mt-4 text-xs text-gray-500 space-x-2">
                            <i class="ri-lock-line"></i>
                            <span>Secure checkout</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

  
    <!-- Order Processing Modal -->
    <div id="processing-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] hidden">
        <div class="bg-white rounded-lg p-8 shadow-2xl max-w-md w-full text-center">
            <div class="mb-6">
                <div class="animate-spin rounded-full h-20 w-20 border-t-4 border-b-4 border-red-600 mx-auto"></div>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3">Processing Your Order</h3>
            <p class="text-gray-600">Please wait while we process your order. This may take a few moments.</p>
        </div>
    </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded and ready');
            const completePurchaseBtn = document.getElementById('complete-purchase-btn');
            const processingModal = document.getElementById('processing-modal');
            const gcashPaymentForm = document.getElementById('gcash-payment-form');
            
            if (!gcashPaymentForm) {
                console.error('GCash payment form element not found!');
            } else {
                console.log('GCash payment form found');
            }
            
            const paymentOptions = document.querySelectorAll('.payment-option');
            console.log('Payment options found:', paymentOptions.length);
            
            // Find GCash payment option by text content
            paymentOptions.forEach(option => {
                const label = document.querySelector(`label[for="payment-${option.value}"]`);
                if (label) {
                    const labelText = label.textContent.trim().toLowerCase();
                    console.log(`Payment option ${option.value} label:`, labelText);
                    
                    if (labelText.includes('gcash')) {
                        console.log('Found GCash payment option with ID:', option.value);
                        option.setAttribute('data-payment-type', 'gcash');
                    }
                }
                
                // Add change event handler to show/hide GCash form
                option.addEventListener('change', function() {
                    console.log('Payment changed to:', this.value);
                    
                    if (this.getAttribute('data-payment-type') === 'gcash' || 
                        label && label.textContent.trim().toLowerCase().includes('gcash')) {
                        console.log('Showing GCash form');
                        gcashPaymentForm.classList.remove('hidden');
                    } else {
                        console.log('Hiding GCash form');
                        gcashPaymentForm.classList.add('hidden');
                    }
                });
            });
            
            // Check if GCash is already selected on page load
            const selectedPayment = document.querySelector('input[name="payment-method"]:checked');
            if (selectedPayment) {
                console.log('Initially selected payment:', selectedPayment.value);
                const label = document.querySelector(`label[for="payment-${selectedPayment.value}"]`);
                if (label && label.textContent.trim().toLowerCase().includes('gcash')) {
                    console.log('Showing GCash form initially');
                    gcashPaymentForm.classList.remove('hidden');
                }
            }
            
            completePurchaseBtn.addEventListener('click', function() {
        // Get selected shipper and payment method
        const paymentMethod = document.querySelector('input[name="payment-method"]:checked');
        
        // Check if GCash is selected and validate the number
        const paymentLabel = document.querySelector(`label[for="payment-${paymentMethod.value}"]`);
        const isGCash = paymentLabel && paymentLabel.textContent.trim().toLowerCase().includes('gcash');
        
        if (isGCash) {
            const gcashNumber = document.getElementById('gcash-number').value;
            if (!gcashNumber || gcashNumber.length !== 11) {
                alert('Please enter a valid 11-digit GCash number.');
                return;
            }
        }
        
        // Show processing modal
        processingModal.classList.remove('hidden');
        
        // Set a minimum display time for the modal (at least 1.5 seconds)
        const minDisplayTime = 1500;
        const startTime = Date.now();
        
        // Build request body
        let requestData = {
            payment_method_id: paymentMethod.value
        };
        
        // Add GCash number if that payment method is selected
        if (isGCash) {
            requestData.gcash_number = document.getElementById('gcash-number').value;
        }
        
        // Send purchase completion request to new session-based handler
        fetch('process_session_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Calculate how much time has passed
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minDisplayTime - elapsedTime);
            
            // Only proceed after the minimum display time
            setTimeout(() => {
                // Hide processing modal
                processingModal.classList.add('hidden');
                
                if (data.success) {
                    window.location.href = 'order_confirmation.php?order_id=' + data.order_id;
                } else {
                    alert('Failed to complete purchase: ' + (data.message || 'Unknown error'));
                }
            }, remainingTime);
        })
        .catch(error => {
            console.error("Error:", error);
            
            // Keep the modal visible for at least the minimum time
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minDisplayTime - elapsedTime);
            
            setTimeout(() => {
                processingModal.classList.add('hidden');
                alert('An error occurred while processing your order. Please try again.');
            }, remainingTime);
        });
    });

        // Your existing radio button code remains unchanged
        const shippingOptions = document.querySelectorAll('.shipping-option');
        shippingOptions.forEach(option => {
            option.addEventListener('change', function() {
                // You could implement shipping fee calculations here if needed
            });
        });
    });
    </script>
</body>
</html>