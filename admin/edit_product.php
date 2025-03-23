<?php
$page_title = "Edit Product | E-MEAT Admin";
include('includes/header.php');
include '../connection/config.php'; // Database connection

// Check if the product ID is provided in the URL
if (!isset($_GET['id'])) {
    echo "<script>alert('No product selected.'); window.location.href = 'product_list.php';</script>";
    exit();
}

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
        $new_qty_available = $_POST['qty_available'];
        $new_unit_price = $_POST['unit_price'];
        
        // Update quantity and price using existing stored procedure
        $update_success = false;
        
        // Update quantity and price
        $update_stmt = $conn->prepare("CALL Update_MEAT_PART(?, ?, ?)");
        $update_stmt->bind_param("iid", $meat_part_id, $new_qty_available, $new_unit_price);
        
        if ($update_stmt->execute()) {
            $update_success = true;
        } else {
            echo "<script>alert('Failed to update product details: {$update_stmt->error}');</script>";
        }
        $update_stmt->close();
        $conn->next_result();
        
        if ($update_success) {
            echo "<script>alert('Product updated successfully!'); window.location.href = 'product_list.php';</script>";
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                <span>Edit Product</span>
                <span class="text-lg bg-red-100 text-red-800 py-1 px-3 rounded-full">#<?= $meat_part_id ?></span>
            </h1>
            <div class="flex items-center gap-2 text-sm text-gray-500 mt-2">
                <a href="dashboard.php" class="hover:text-red-600 transition-colors">Dashboard</a>
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
                            <!-- Quantity Available -->
                            <div class="space-y-2">
                                <label for="qty_available" class="block text-sm font-medium text-gray-700">
                                    Quantity Available
                                </label>
                                <div class="flex items-center">
                                    <div class="relative flex-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-cubes text-gray-400"></i>
                                        </div>
                                        <input type="number" id="qty_available" name="qty_available" 
                                               value="<?= htmlspecialchars($product['QTY_AVAILABLE']) ?>" 
                                               class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none" 
                                               required oninput="checkQty()">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 font-medium">
                                                <?= htmlspecialchars($product['UNIT_OF_MEASURE']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <p id="qty-error" class="mt-1 text-sm text-red-600 hidden">
                                    Quantity must be greater than 0.
                                </p>
                                <p class="text-xs text-gray-500">
                                    Current inventory level that customers can purchase
                                </p>
                            </div>
                            
                            <!-- Unit Price -->
                            <div class="space-y-2">
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
                                    Price must be greater than 0.
                                </p>
                                <p class="text-xs text-gray-500">
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
                            <button type="submit" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 font-medium flex items-center gap-2 transition-colors shadow-sm">
                                <i class="fas fa-check"></i>
                                <span>Save Changes</span>
                            </button>
                        </div>
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
                                    <span>Sold by <?= htmlspecialchars($product['UNIT_OF_MEASURE']) ?> and grams</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validation functions
        function checkQty() {
            const qty = document.getElementById('qty_available').value;
            const error = document.getElementById('qty-error');
            
            if (qty <= 0 || qty === '' || qty === '-0') {
                error.classList.remove('hidden');
                return false;
            } else {
                error.classList.add('hidden');
                return true;
            }
        }
        
        function checkPrice() {
            const price = document.getElementById('unit_price').value;
            const error = document.getElementById('price-error');
            
            if (price <= 0 || price === '' || price === '-0') {
                error.classList.remove('hidden');
                return false;
            } else {
                error.classList.add('hidden');
                return true;
            }
        }
        
        function validateForm() {
            const qtyValid = checkQty();
            const priceValid = checkPrice();
            return qtyValid && priceValid;
        }
    </script>
</body>
</html>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>