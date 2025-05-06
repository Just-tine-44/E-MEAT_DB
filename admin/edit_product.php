<?php
session_start();

// To match what login.php is setting:
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    $_SESSION['message'] = "You need to log in as admin to access this page";
    header("Location: ../users/login.php");
    exit();
}

$page_title = "Edit Product | E-MEAT Admin";
include('new_include/sidebar.php');
include '../connection/config.php'; // Database connection

// // Check if the product ID is provided in the URL
// if (!isset($_GET['id'])) {
//     echo "<script>alert('No product selected.'); window.location.href = 'product_list.php';</script>";
//     exit();
// }

$meat_part_id = $_GET['id'];
$product = null;

try {
    // Fetch the existing product details using stored procedure
    $stmt = $conn->prepare("CALL GetProductDetailsForEdit(?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $meat_part_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
    } else {
        echo "<script>alert('Product not found.'); window.location.href = 'product_list.php';</script>";
        exit();
    }
    $stmt->close();
    
    // Clear any remaining result sets
    $conn->next_result();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_unit_price = $_POST['unit_price'];
        $additional_stock = isset($_POST['additional_stock']) ? floatval($_POST['additional_stock']) : 0;
        
        // Update product details
        $update_success = false;
        $message = "";
        
        if ($additional_stock > 0) {
            // Use Add_Stock_To_MEAT_PART procedure if additional stock is provided
            $update_stmt = $conn->prepare("CALL Add_Stock_To_MEAT_PART(?, ?, ?)");
            $update_stmt->bind_param("idd", $meat_part_id, $additional_stock, $new_unit_price);
            
            if ($update_stmt->execute()) {
                $update_success = true;
                $message = "Stock added and price updated successfully!";
            } else {
                $message = "Failed to update product details: {$update_stmt->error}";
            }
        } else {
            // Just update the price if no additional stock using the enhanced procedure
            $update_stmt = $conn->prepare("CALL Update_MEAT_PART_Price(?, ?)");
            $update_stmt->bind_param("id", $meat_part_id, $new_unit_price);
            
            if ($update_stmt->execute()) {
                $result = $update_stmt->get_result();
                if ($result) {
                    $row = $result->fetch_assoc();
                    if (isset($row['success'])) {
                        $update_success = (bool)$row['success'];
                        $message = $row['message'];
                    } else {
                        $update_success = true;
                        $message = "Product updated successfully!";
                    }
                } else {
                    $update_success = true;
                    $message = "Product updated successfully!";
                }
            } else {
                $message = "Failed to update product details: {$update_stmt->error}";
            }
        }
        
        $update_stmt->close();
        $conn->next_result();
        
        if ($update_success) {
            echo "<script>alert('{$message}'); window.location.href = 'product_list.php';</script>";
        } else {
            echo "<script>alert('{$message}');</script>";
        }
    }
} catch (Exception $e) {
    error_log("Error in edit_product.php: " . $e->getMessage());
    echo "<script>alert('An error occurred: " . addslashes($e->getMessage()) . "');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | E-MEAT Admin</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <!-- Main Content Wrapper - offset from the sidebar -->
    <div class="pl-0 md:pl-60 lg:pl-64 w-full transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <span>Edit Product</span>
                    <span class="text-lg bg-red-100 text-red-800 py-1 px-3 rounded-full">#<?= $meat_part_id ?></span>
                </h1>
                <div class="flex items-center gap-2 text-sm text-gray-500 mt-2">
                    <a href="admin.php" class="hover:text-red-600 transition-colors">Dashboard</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <a href="product_list.php" class="hover:text-red-600 transition-colors">Products</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-red-600">Edit</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Product Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                            <h2 class="text-white text-xl font-semibold"><?= htmlspecialchars($product['MEAT_PART_NAME']) ?></h2>
                            <p class="text-red-100 mt-1 text-sm">Update product details</p>
                        </div>
                        
                        <form method="POST" onsubmit="return validateForm()">
                            <div class="p-6 space-y-6">
                                <!-- Current Quantity Available (Read Only) -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">
                                        Current Quantity Available (Read Only)
                                    </label>
                                    <div class="flex items-center">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-cubes text-gray-400"></i>
                                            </div>
                                            <input type="text" value="<?= htmlspecialchars($product['QTY_AVAILABLE']) ?> <?= htmlspecialchars($product['UNIT_OF_MEASURE']) ?>"
                                                class="block w-full pl-10 py-3 border border-gray-200 bg-gray-100 rounded-lg shadow-sm text-gray-700" 
                                                readonly>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Last updated: <?= isset($product['LAST_UPDATED']) ? date('M j, Y g:i A', strtotime($product['LAST_UPDATED'])) : 'Not available' ?>
                                    </p>
                                    
                                    <!-- Range Indicator -->
                                    <div class="mt-2">
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span>Min: 0</span>
                                            <span>Max: 400</span>
                                        </div>
                                        <div class="h-2 bg-gray-200 rounded mt-1 overflow-hidden">
                                            <div id="qty-range-indicator" class="h-full transition-all duration-300" 
                                                style="width: <?= min(100, max(0, ($product['QTY_AVAILABLE'] / 400) * 100)) ?>%; 
                                                      background-color: <?= $product['QTY_AVAILABLE'] < 10 ? '#ef4444' : ($product['QTY_AVAILABLE'] < 75 ? '#f59e0b' : '#22c55e') ?>;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Add New Stock Section -->
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <label for="additional_stock" class="block text-sm font-medium text-gray-700">
                                        Add Additional Stock
                                    </label>
                                    <div class="flex items-center mt-2">
                                        <div class="relative flex-1">
                                            <input type="number" id="additional_stock" name="additional_stock" 
                                                placeholder="Enter amount to add (20-400)"
                                                min="20" max="400" step="0.1"
                                                class="block w-full pl-3 pr-12 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-medium">
                                                    <?= htmlspecialchars($product['UNIT_OF_MEASURE']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <button type="button" id="add-stock-btn" class="ml-3 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                            <i class="fas fa-plus mr-1"></i> Add
                                        </button>
                                    </div>
                                    
                                    <!-- Range Indicator for Additional Stock -->
                                    <div class="mt-2">
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span>Min: 20</span>
                                            <span>Max: 400</span>
                                        </div>
                                        <div class="h-2 bg-gray-200 rounded mt-1 overflow-hidden">
                                            <div id="additional-stock-indicator" class="h-full bg-green-500 transition-all duration-300" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    
                                    <p class="text-xs text-gray-500 mt-1">
                                        Enter the quantity of new stock to add (minimum 20, maximum 400 <?= htmlspecialchars($product['UNIT_OF_MEASURE']) ?>)
                                    </p>
                                </div>
                                
                                <!-- Unit Price -->
                                <div class="space-y-2 mt-4 pt-4 border-t border-gray-200">
                                    <label for="unit_price" class="block text-sm font-medium text-gray-700">
                                        Unit Price
                                    </label>
                                    <div class="flex items-center">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-medium">₱</span>
                                            </div>
                                            <input type="number" step="0.01" id="unit_price" name="unit_price" 
                                                value="<?= htmlspecialchars($product['UNIT_PRICE']) ?>"
                                                min="50" max="1000"
                                                placeholder="50-1000"
                                                class="block w-full pl-8 pr-20 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none" 
                                                required oninput="checkPrice()">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-medium">
                                                    per <?= htmlspecialchars($product['UNIT_OF_MEASURE']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <p id="price-error" class="mt-1 text-sm text-red-600 hidden">
                                        Price must be between ₱50 and ₱1000.
                                    </p>
                                    
                                    <!-- Price Range Indicator -->
                                    <div class="mt-2">
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span>Min: ₱50</span>
                                            <span>Max: ₱1000</span>
                                        </div>
                                        <div class="h-2 bg-gray-200 rounded mt-1 overflow-hidden">
                                            <div id="price-range-indicator" class="h-full bg-blue-500 transition-all duration-300" 
                                                style="width: <?= min(100, max(0, (($product['UNIT_PRICE'] - 50) / (1000 - 50)) * 100)) ?>%"></div>
                                        </div>
                                    </div>
                                    
                                    <p class="text-xs text-gray-500 mt-1">
                                        Sales price per unit shown to customers
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-4">
                                <a href="product_list.php" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 font-medium flex items-center gap-2 transition-colors shadow-sm">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>Cancel</span>
                                </a>
                                <button type="submit" id="submit-form" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 font-medium flex items-center gap-2 transition-colors shadow-sm">
                                    <i class="fas fa-check"></i>
                                    <span>Save Changes</span>
                                </button>
                            </div>
                            
                            <!-- Hidden field for additional stock -->
                            <input type="hidden" id="additional_stock_hidden" name="additional_stock" value="0">
                        </form>
                    </div>
                </div>
                
                <!-- Product Info & Preview -->
                <div>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden sticky top-6">
                        <!-- Product Info -->
                        <div class="p-6 space-y-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-800"><?= htmlspecialchars($product['MEAT_PART_NAME']) ?></h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="text-lg font-semibold text-red-600">₱<?= number_format($product['UNIT_PRICE'], 2) ?></div>
                                    <div class="text-sm text-gray-500">per <?= htmlspecialchars($product['UNIT_OF_MEASURE']) ?></div>
                                </div>
                            </div>
                            
                            <!-- Inventory Status -->
                            <div class="border-t border-gray-100 pt-4">
                                <div class="text-sm text-gray-500 mb-2">Inventory Status</div>
                                <?php
                                    $status_class = 'bg-green-100 text-green-800';
                                    $status_text = 'In Stock';
                                    
                                    if ($product['QTY_AVAILABLE'] <= 0) {
                                        $status_class = 'bg-red-100 text-red-800';
                                        $status_text = 'Out of Stock';
                                    } elseif ($product['QTY_AVAILABLE'] < 10) {
                                        $status_class = 'bg-yellow-100 text-yellow-800';
                                        $status_text = 'Low Stock';
                                    }
                                ?>
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-700">Available</span>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?= $status_class ?>">
                                        <?= $status_text ?>
                                    </span>
                                </div>
                                <div class="mt-2 bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                    <?php
                                        $percent = min(100, ($product['QTY_AVAILABLE'] / 50) * 100); // Assuming 50 is "full stock"
                                        $bar_class = 'bg-green-500';
                                        
                                        if ($percent < 20) {
                                            $bar_class = 'bg-red-500';
                                        } elseif ($percent < 50) {
                                            $bar_class = 'bg-yellow-500';
                                        }
                                    ?>
                                    <div class="<?= $bar_class ?> h-2.5" style="width: <?= $percent ?>%"></div>
                                </div>
                                <div class="text-center text-sm mt-1 text-gray-600">
                                    <?= $product['QTY_AVAILABLE'] ?> <?= htmlspecialchars($product['UNIT_OF_MEASURE']) ?> remaining
                                </div>
                            </div>
                            
                            <!-- Last Updated Information -->
                            <div class="border-t border-gray-100 pt-4">
                                <div class="text-sm text-gray-500 mb-2">Last Stock Update</div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">
                                        <?= isset($product['LAST_UPDATED']) ? date('M j, Y', strtotime($product['LAST_UPDATED'])) : 'Not available' ?>
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <?= isset($product['LAST_UPDATED']) ? date('g:i A', strtotime($product['LAST_UPDATED'])) : '' ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Additional Info -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <div class="text-sm font-medium text-gray-700 mb-3">Product ID: #<?= $meat_part_id ?></div>
                                <div class="space-y-2 text-xs text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-tag w-4 text-center text-gray-400"></i>
                                        <span><?= htmlspecialchars($product['MEAT_PART_NAME']) ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-balance-scale w-4 text-center text-gray-400"></i>
                                        <span>Sold by <?= htmlspecialchars($product['UNIT_OF_MEASURE']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store original price for comparison
        const originalPrice = parseFloat('<?= $product["UNIT_PRICE"] ?>');
        
        // Validation functions
        function checkPrice() {
            const price = parseFloat(document.getElementById('unit_price').value);
            const error = document.getElementById('price-error');
            const input = document.getElementById('unit_price');
            const minPrice = 50; // Suggested minimum price
            const maxPrice = 1000; // Suggested maximum price
            
            if (isNaN(price) || price < minPrice || price > maxPrice) {
                error.textContent = `Price must be between ₱${minPrice} and ₱${maxPrice}.`;
                error.classList.remove('hidden');
                input.classList.add('border-red-500');
                
                // Update indicator if it exists
                const indicator = document.getElementById('price-range-indicator');
                if (indicator) {
                    indicator.style.width = '0%';
                    indicator.style.backgroundColor = '#ef4444'; // red
                }
                
                return false;
            } else {
                error.classList.add('hidden');
                input.classList.remove('border-red-500');
                
                // Format to 2 decimal places
                input.value = price.toFixed(2);
                
                // Update indicator if it exists
                const indicator = document.getElementById('price-range-indicator');
                if (indicator) {
                    const percentage = ((price - minPrice) / (maxPrice - minPrice)) * 100;
                    indicator.style.width = Math.min(100, Math.max(0, percentage)) + '%';
                    
                    // Change color based on price point
                    if (percentage < 25) {
                        indicator.style.backgroundColor = '#22c55e'; // green for lower prices
                    } else if (percentage > 75) {
                        indicator.style.backgroundColor = '#f97316'; // orange for higher prices
                    } else {
                        indicator.style.backgroundColor = '#3b82f6'; // blue for mid-range
                    }
                }
                
                return true;
            }
        }
        
        function validateForm() {
            // Check if there are any changes before submitting
            const newPrice = parseFloat(document.getElementById('unit_price').value);
            const additionalStock = parseFloat(document.getElementById('additional_stock_hidden').value) || 0;
            
            // If no price change and no stock addition, prevent submission
            if (Math.abs(newPrice - originalPrice) < 0.01 && additionalStock === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Changes',
                    text: 'You haven\'t made any changes to the product.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }
            
            return checkPrice();
        }

        // Add live update for additional stock range indicator
        document.addEventListener('DOMContentLoaded', function() {
            const additionalStockInput = document.getElementById('additional_stock');
            const additionalStockIndicator = document.getElementById('additional-stock-indicator');
            
            if (additionalStockInput && additionalStockIndicator) {
                additionalStockInput.addEventListener('input', function() {
                    const value = parseFloat(this.value);
                    const min = 20;
                    const max = 400;
                    
                    if (!isNaN(value) && value > 0) {
                        // Calculate percentage (constrained between 0-100%)
                        const percentage = Math.min(100, Math.max(0, ((value - min) / (max - min)) * 100));
                        
                        // Update width based on percentage
                        additionalStockIndicator.style.width = percentage + '%';
                        
                        // Change color based on the value
                        if (value < min) {
                            additionalStockIndicator.style.backgroundColor = '#ef4444'; // red for below minimum
                        } else if (value > max) {
                            additionalStockIndicator.style.backgroundColor = '#ef4444'; // red for above maximum
                        } else if (value < 50) {
                            additionalStockIndicator.style.backgroundColor = '#22c55e'; // green for lower range
                        } else if (value > 300) {
                            additionalStockIndicator.style.backgroundColor = '#f59e0b'; // orange for higher range
                        } else {
                            additionalStockIndicator.style.backgroundColor = '#22c55e'; // green for mid range
                        }
                    } else {
                        // Reset to 0% if empty or invalid
                        additionalStockIndicator.style.width = '0%';
                    }
                });
            }

            // Add stock button handler - only one instance
            const addStockBtn = document.getElementById('add-stock-btn');
            if (addStockBtn) {
                addStockBtn.addEventListener('click', function() {
                    const additionalStock = document.getElementById('additional_stock');
                    const value = parseFloat(additionalStock.value);
                    
                    // Modified validation - check min/max limits
                    if (isNaN(value) || value <= 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Amount',
                            text: 'Please enter a valid quantity to add',
                            confirmButtonColor: '#EF4444'
                        });
                        return;
                    }
                    
                    // Add minimum/maximum validation
                    if (value < 20) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Below Minimum',
                            text: 'The minimum stock addition allowed is 20 units',
                            confirmButtonColor: '#F59E0B'
                        });
                        return;
                    }
                    
                    if (value > 400) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Exceeds Maximum',
                            text: 'The maximum stock addition allowed is 400 units',
                            confirmButtonColor: '#F59E0B'
                        });
                        return;
                    }
                    
                    // Also check if total would exceed maximum
                    const currentQty = parseFloat('<?= $product["QTY_AVAILABLE"] ?>');
                    const newQty = currentQty + value;
                    
                    // Optional: If you want to enforce a maximum total inventory
                    if (newQty > 1000) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Inventory Limit Exceeded',
                            text: 'This addition would exceed the maximum allowed inventory of 1000 units',
                            confirmButtonColor: '#F59E0B'
                        });
                        return;
                    }
                    
                    // Show confirmation dialog
                    Swal.fire({
                        icon: 'question',
                        title: 'Confirm Stock Addition',
                        html: `Are you sure you want to add <b>${value} <?= $product["UNIT_OF_MEASURE"] ?></b> to current stock?<br>` +
                              `New total will be <b>${newQty.toFixed(2)} <?= $product["UNIT_OF_MEASURE"] ?></b>`,
                        showCancelButton: true,
                        confirmButtonColor: '#10B981',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Yes, Add Stock',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Update the hidden input and submit the form
                            document.getElementById('additional_stock_hidden').value = value;
                            
                            // Check if price is valid before submitting
                            if (checkPrice()) {
                                document.getElementById('submit-form').click();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Invalid Price',
                                    text: 'Please correct the unit price before adding stock.',
                                    confirmButtonColor: '#EF4444'
                                });
                            }
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>