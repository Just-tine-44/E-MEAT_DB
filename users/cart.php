<?php
// filepath: /c:/xampp/htdocs/website/cart.php
include '../connection/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get cart items from session
$cart_items = $_SESSION['cart'] ?? [];
$has_items = !empty($cart_items);
$total_amount = 0;

// Fetch current stock levels for all products in cart
$product_ids = [];
foreach ($cart_items as $item) {
    $product_ids[] = $item['meat_part_id'];
}

$stock_levels = [];
if (!empty($product_ids)) {
    $product_ids_list = implode(',', $product_ids);
    
    $stmt = $conn->prepare("CALL GetStockLevelsForCart(?)");
    $stmt->bind_param("s", $product_ids_list);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $stock_levels[$row['MEAT_PART_ID']] = $row['QTY_AVAILABLE'];
    }
    $stmt->close();
    $conn->next_result(); // Clear any remaining result sets
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart | E-MEAT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image" href="../IMAGES/RED LOGO.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.5.0/remixicon.css">
    <link rel="stylesheet" href="../CCS/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #f9fafb; }
        .cart-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05); }
        .table-row:not(:last-child) { border-bottom: 1px solid #f3f4f6; }
        .quantity-input { -moz-appearance: textfield; }
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body>
    <header>
        <nav class="nav container">
            <a href="index.php" class="nav__logo">
                <img src="../IMAGES/WHITE LOGO.png" alt="Emeat Logo" class="nav__logo-img">
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
                <a href="cart.php" class="cart-icon-container">
                    <i class="ri-shopping-cart-fill"></i>
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </nav>
    </header>

    <div class="max-w-5xl mx-auto p-6 mt-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-2">Your Shopping Cart</h1>
        <p class="text-center text-gray-500 mb-10">Review your items before proceeding to checkout</p>
        
        <!-- Cart Section -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-8 cart-shadow">
            <div class="border-b border-gray-100 bg-gradient-to-r from-red-50 to-white p-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                    <i class="ri-shopping-cart-line text-red-600"></i>
                    Your Cart Items
                </h2>
            </div>

            <?php if ($has_items): ?>
            <!-- Cart Items -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-sm text-gray-600">
                            <th class="py-3 px-6 text-left font-medium">Product</th>
                            <th class="py-3 px-6 text-left font-medium">Quantity</th>
                            <th class="py-3 px-6 text-left font-medium">Unit Price</th>
                            <th class="py-3 px-6 text-right font-medium">Subtotal</th>
                            <th class="py-3 px-6 text-center font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $total_amount = 0;
                            
                            foreach ($cart_items as $index => $item) {
                                $unit_of_measure = strtolower($item['unit']);
                                $composite_id = $index . '-' . $item['meat_part_id'] . '-' . $unit_of_measure;
                                $available_qty = $stock_levels[$item['meat_part_id']] ?? 0;

                                if ($unit_of_measure === 'g') {
                                    $displayQty = intval($item['quantity']); // Whole numbers for grams
                                    $step = 50; // Step by 50 grams
                                } else {
                                    $displayQty = floatval($item['quantity']); 
                                    $step = 0.1; // Step by 0.1 kg
                                }
                                
                                // Calculate total price correctly
                                if ($unit_of_measure === 'g') {
                                    $total_price = ($item['unit_price'] * $item['quantity']) / 1000;
                                } else {
                                    $total_price = $item['quantity'] * $item['unit_price'];
                                }
                                $total_amount += $total_price;
                            ?>
                            <tr class="table-row hover:bg-gray-50" data-cart-index="<?php echo $index; ?>">
                                <!-- Product Info -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-600 mr-4">
                                            <i class="ri-knife-line text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                            <p class="text-xs text-gray-500">
                                                Sold by <span class="unit-of-measure font-medium"><?php echo strtoupper($unit_of_measure); ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Quantity -->
                                <td class="py-4 px-6">
                                    <div>
                                        <div class="flex items-center">
                                            <div class="relative flex items-center border border-gray-300 rounded-lg">
                                                <button type="button" class="qty-decrease p-1 text-gray-500 hover:text-red-600 focus:outline-none">
                                                    <i class="ri-subtract-line"></i>
                                                </button>
                                                
                                                <input type="number" class="qty-input quantity-input block w-16 py-1 px-2 text-center text-gray-700 font-medium focus:outline-none" 
                                                    data-cart-index="<?php echo $index; ?>" 
                                                    data-meat-part-id="<?php echo $item['meat_part_id']; ?>"
                                                    value="<?php echo $displayQty; ?>"
                                                    min="<?php echo ($unit_of_measure === 'g') ? '100' : '0.1'; ?>"
                                                    max="<?php echo ($unit_of_measure === 'g') ? '950' : $available_qty; ?>"
                                                    step="<?php echo $step; ?>">
                                                    
                                                <button type="button" class="qty-increase p-1 text-gray-500 hover:text-green-600 focus:outline-none">
                                                    <i class="ri-add-line"></i>
                                                </button>
                                            </div>
                                            
                                            <div class="ml-2 text-xs font-medium unit-label">
                                                <?php echo strtoupper($unit_of_measure); ?>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Available: 
                                            <span class="available-qty <?php echo ($available_qty > 0) ? 'text-green-600 font-medium' : 'text-red-600 font-medium'; ?>" 
                                                data-meat-part-id="<?php echo $item['meat_part_id']; ?>">
                                                <?php echo number_format($available_qty, 2); ?> KG
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Unit Price -->
                                <td class="py-4 px-6 unit-price">
                                    <div class="text-gray-700 font-medium">₱<?php echo number_format($item['unit_price'], 2); ?></div>
                                    <div class="text-xs text-gray-500">per KG</div>
                                </td>
                                
                                <!-- Total Price -->
                                <td class="py-4 px-6 text-right total-price">
                                    <span class="font-medium text-gray-800">₱<?php echo number_format($total_price, 2); ?></span>
                                </td>
                                
                                <!-- Delete Button -->
                                <td class="py-4 px-6 text-center">
                                    <button class="delete-item p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors"
                                            data-cart-index="<?php echo $index; ?>"
                                            title="Remove item">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Order Summary -->
            <div class="p-6 bg-gray-50 border-t border-gray-100">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="md:col-start-2">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between border-b border-gray-200 pb-3">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium text-gray-800" id="subtotal">₱<?php echo number_format($total_amount, 2); ?></span>
                            </div>
                            
                            <div class="flex justify-between border-b border-gray-200 pb-3">
                                <span class="text-gray-600">Shipping</span>
                                <span class="text-gray-800">Calculated at checkout</span>
                            </div>
                            
                            <div class="flex justify-between pt-2">
                                <span class="text-lg font-semibold text-gray-800">Total</span>
                                <span class="text-lg font-bold text-red-600" id="total-amount">₱<?php echo number_format($total_amount, 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="mt-6 space-y-3">
                            <a href="checkout.php" id="checkout-button" class="block w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors">
                                Proceed to Checkout
                            </a>
                            <a href="index.php#shop" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-3 px-4 rounded-lg text-center transition-colors">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Empty Cart -->
            <div id="empty-cart-message" class="py-12 px-6 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-shopping-cart-line text-gray-400 text-4xl"></i>
                </div>
                <h3 class="text-xl font-medium text-gray-700 mb-2">Your cart is empty</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">Looks like you haven't added any items to your cart yet.</p>
                <a href="index.php#shop" class="inline-flex items-center justify-center bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="ri-shopping-cart-line mr-2"></i> Start Shopping
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Helper function to get available quantity
    function getAvailableQuantity(element) {
        if (!element) return 0;
        const availableQtyText = element.textContent.trim();
        return parseFloat(availableQtyText.replace(/[^\d.]/g, ''));
    }

    // Function to update total amount
    function updateTotalAmount() {
        let total = 0;
        const cartItems = document.querySelectorAll('tr[data-cart-index]');
        cartItems.forEach(row => {
            const priceText = row.querySelector('.total-price span').textContent;
            const price = parseFloat(priceText.replace(/[^\d.]/g, ''));
            if (!isNaN(price)) {
                total += price;
            }
        });
        document.getElementById('total-amount').textContent = `₱${total.toFixed(2)}`;
        document.getElementById('subtotal').textContent = `₱${total.toFixed(2)}`;
    }

    // Function to update product subtotal
    function updateProductSubtotal(row, quantity) {
        const unitElement = row.querySelector(".unit-of-measure");
        const unitOfMeasure = unitElement ? unitElement.textContent.trim().toUpperCase() : "KG";
        const unitPriceElement = row.querySelector(".unit-price div");
        const unitPriceText = unitPriceElement.textContent;
        const unitPrice = parseFloat(unitPriceText.replace(/[^\d.]/g, ''));
        
        // Calculate new total price
        let newTotalPrice;
        if (unitOfMeasure === "G") {
            newTotalPrice = (unitPrice * quantity) / 1000;
        } else {
            newTotalPrice = quantity * unitPrice;
        }
        
        // Update total price in the row
        const totalPriceElement = row.querySelector(".total-price span");
        totalPriceElement.textContent = `₱${newTotalPrice.toFixed(2)}`;
        
        // Update overall total
        updateTotalAmount();
    }

    // Handle quantity input changes
    document.querySelectorAll(".qty-input").forEach(input => {
        input.addEventListener("change", function() {
            const row = this.closest("tr");
            const cartIndex = this.getAttribute("data-cart-index");
            const meatPartId = this.getAttribute("data-meat-part-id");
            const unitElement = row.querySelector(".unit-of-measure");
            const unitOfMeasure = unitElement ? unitElement.textContent.trim() : "KG";
            const newQuantity = parseFloat(this.value);
            
            // Update subtotal immediately for responsive UI
            updateProductSubtotal(row, newQuantity);
            
            // Update database and session via AJAX
            fetch("../back_process/update_session_cart.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ 
                    index: cartIndex,
                    meat_part_id: meatPartId,
                    quantity: newQuantity,
                    unit: unitOfMeasure
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to update quantity');
                }
                // Success feedback (optional)
                console.log("Quantity updated successfully");
            })
            .catch(error => {
                console.error("Error updating quantity:", error);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to update quantity',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        });
    });

    // Decrease quantity buttons
    document.querySelectorAll(".qty-decrease").forEach(button => {
        button.addEventListener("click", function() {
            const input = this.parentElement.querySelector(".qty-input");
            const row = this.closest("tr");
            const unitSpan = row.querySelector(".unit-of-measure");
            const unitOfMeasure = unitSpan ? unitSpan.textContent.trim() : "KG";
            
            let currentValue = parseFloat(input.value);
            let newValue;
            
            if (unitOfMeasure === "G") {
                newValue = Math.max(100, currentValue - 50);
            } else if (unitOfMeasure === "KG") {
                newValue = Math.max(0.1, (currentValue - 0.1)).toFixed(1);
            }
            
            if (parseFloat(newValue) !== currentValue) {
                input.value = newValue;
                input.dispatchEvent(new Event('change'));
            }
        });
    });

    // Increase quantity buttons
    document.querySelectorAll(".qty-increase").forEach(button => {
        button.addEventListener("click", function() {
            const input = this.parentElement.querySelector(".qty-input");
            const row = this.closest("tr");
            const unitSpan = row.querySelector(".unit-of-measure");
            const unitOfMeasure = unitSpan ? unitSpan.textContent.trim() : "KG";
            
            let currentValue = parseFloat(input.value);
            let newValue;
            
            const availableQtyElement = row.querySelector(".available-qty");
            const availableQty = getAvailableQuantity(availableQtyElement);
            
            if (unitOfMeasure === "G") {
                // For grams: convert to KG for comparison with available stock
                const potentialKgValue = (currentValue + 50) / 1000;
                if (potentialKgValue <= availableQty) {
                    newValue = Math.min(950, currentValue + 50);
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Maximum stock reached',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    return;
                }
            } else if (unitOfMeasure === "KG") {
                // For kg: check direct comparison
                const potentialValue = parseFloat((currentValue + 0.1).toFixed(1));
                if (potentialValue <= availableQty) {
                    newValue = potentialValue.toFixed(1);
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Maximum stock reached',
                        text: `Only ${availableQty.toFixed(1)} KG available.`,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    return;
                }
            }
            
            input.value = newValue;
            input.dispatchEvent(new Event('change'));
        });
    });

    // Handle deleting items
    document.querySelectorAll(".delete-item").forEach(function(button) {
        button.addEventListener("click", function() {
            const cartIndex = this.getAttribute("data-cart-index");
            const row = document.querySelector(`tr[data-cart-index="${cartIndex}"]`);
            
            if (!row) {
                console.error("Row not found:", cartIndex);
                return;
            }
            
            // Get additional verification data
            const qtyInput = row.querySelector('.qty-input');
            const meatPartId = qtyInput ? qtyInput.getAttribute('data-meat-part-id') : null;
            const unitElement = row.querySelector('.unit-of-measure');
            const unit = unitElement ? unitElement.textContent.trim() : 'kg';
            
            // Disable only THIS delete button
            this.disabled = true;
            this.innerHTML = '<i class="ri-loader-4-line animate-spin"></i>';

            // Confirm deletion
            Swal.fire({
                title: 'Remove item?',
                text: "Are you sure you want to remove this item from your cart?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, remove it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Make the request with additional verification data
                    fetch("../back_process/remove_from_session_cart.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ 
                            index: cartIndex,
                            meat_part_id: meatPartId,
                            unit: unit
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Visual removal animation
                            row.style.transition = "opacity 0.5s, transform 0.5s";
                            row.style.opacity = "0";
                            row.style.transform = "translateX(20px)";
                            
                            // Update cart counter in header
                            const cartCountElement = document.querySelector('.cart-count');
                            if (cartCountElement) {
                                const currentCount = parseInt(cartCountElement.textContent);
                                if (currentCount > 1) {
                                    cartCountElement.textContent = currentCount - 1;
                                } else {
                                    cartCountElement.remove();
                                }
                            }
                            
                            // After animation completes
                            setTimeout(() => {
                                row.remove();
                                updateTotalAmount();
                                
                                // Update remaining row indexes
                                updateCartIndexes();
                                
                                // If cart is now empty, reload the page to show empty cart message
                                if (document.querySelectorAll('tr[data-cart-index]').length === 0) {
                                    location.reload();
                                }
                                
                                // Success notification
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Item removed from cart',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                            }, 500);
                        } else {
                            throw new Error(data.message || 'Failed to remove item');
                        }
                    })
                    .catch(error => {
                        console.error("Error removing item:", error);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Failed to remove item from cart',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    })
                    .finally(() => {
                        // Re-enable only this button if there was an error
                        if (document.querySelector(`tr[data-cart-index="${cartIndex}"]`)) {
                            const currentButton = document.querySelector(`tr[data-cart-index="${cartIndex}"] .delete-item`);
                            if (currentButton) {
                                currentButton.disabled = false;
                                currentButton.innerHTML = '<i class="ri-delete-bin-line"></i>';
                            }
                        }
                    });
                } else {
                    // If confirmation canceled, re-enable this button
                    this.disabled = false;
                    this.innerHTML = '<i class="ri-delete-bin-line"></i>';
                }
            });
        });
    });

    // Function to update cart indexes after deletion
    function updateCartIndexes() {
        const rows = document.querySelectorAll('tr[data-cart-index]');
        rows.forEach((row, newIndex) => {
            // Update row index
            row.setAttribute('data-cart-index', newIndex);
            
            // Update quantity input index
            const qtyInput = row.querySelector('.qty-input');
            if (qtyInput) qtyInput.setAttribute('data-cart-index', newIndex);
            
            // Update delete button index
            const deleteBtn = row.querySelector('.delete-item');
            if (deleteBtn) deleteBtn.setAttribute('data-cart-index', newIndex);
        });
    }
    });
</script>
</body>
</html>