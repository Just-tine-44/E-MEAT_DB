<?php

session_start(); // Start the session

// To match what login.php is setting:
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    $_SESSION['message'] = "You need to log in as admin to access this page";
    header("Location: ../users/login.php");
    exit();
}

$page_title = "Product Inventory | E-MEAT Admin";
include('new_include/sidebar.php'); // Sidebar (navigation)
include('../connection/config.php'); // Database connection

try {
    // Fetch all meat products using stored procedure
    $stmt = $conn->prepare("CALL GetAllMeatProducts()");
    if (!$stmt) {
        throw new Exception("Prepare failed for products: " . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    // Clear any remaining result sets
    $conn->next_result();
    
    // Get unique categories for filters using stored procedure
    $cat_stmt = $conn->prepare("CALL GetAllMeatCategories()");
    if (!$cat_stmt) {
        throw new Exception("Prepare failed for categories: " . $conn->error);
    }
    
    $cat_stmt->execute();
    $categories = $cat_stmt->get_result();
    $cat_stmt->close();
    
    // Clear any remaining result sets
    $conn->next_result();
    
} catch (Exception $e) {
    // Log error but show user-friendly message
    error_log("Error fetching product data: " . $e->getMessage());
    $error_message = "Unable to load product data. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .transition-all {
            transition: all 0.3s ease;
        }
        
        .table-row:hover {
            background-color: #f9fafb;
        }
        
        /* Custom scrollbar styles */
        .overflow-y-auto::-webkit-scrollbar {
            width: 8px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Main Content Wrapper - this is the key part that fixes the layout -->
    <div class="pl-0 lg:pl-64 transition-all duration-300 min-h-screen">
        <!-- Page Content -->
        <div class="container mx-auto px-4 py-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Product Inventory</h1>
                    <p class="text-gray-500 text-sm mt-1">Manage your meat product inventory</p>
                </div>
                
                <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                    <div class="relative">
                        <input id="searchInput" type="text" placeholder="Search products..." 
                               class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                    
                    <a href="add_product.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        <span>Add Product</span>
                    </a>
                </div>
            </div>
            
            <!-- Error Message (if any) -->
            <?php if(isset($error_message)): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="mb-6 flex flex-wrap gap-2">
                <button class="category-filter active px-4 py-2 rounded-full bg-red-600 text-white hover:bg-red-700 transition-all" data-category="all">
                    All Categories
                </button>
                <?php if (isset($categories) && $categories->num_rows > 0): ?>
                    <?php while($cat = $categories->fetch_assoc()): ?>
                        <button class="category-filter px-4 py-2 rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 transition-all" 
                                data-category="<?= $cat['MEAT_CATEGORY_ID'] ?>">
                            <?= ucfirst(strtolower($cat['MEAT_NAME'])) ?>
                        </button>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
            
            <!-- Products Table with Modern Design -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <div class="max-h-[400px] overflow-y-auto"> 
                        <table class="w-full">
                            <thead class="sticky top-0 z-10">
                                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                    <th class="py-3 px-4 text-left">Product</th>
                                    <th class="py-3 px-4 text-left">Category</th>
                                    <th class="py-3 px-4 text-center">Stock</th>
                                    <th class="py-3 px-4 text-center">Unit</th>
                                    <th class="py-3 px-4 text-right">Price</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm">
                                <?php 
                                if (isset($result) && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        // Determine stock status for styling
                                        $stockClass = '';
                                        $stockText = '';
                                        
                                        if ($row['QTY_AVAILABLE'] <= 0) {
                                            $stockClass = 'bg-red-100 text-red-800';
                                            $stockText = 'Out of Stock';
                                        } elseif ($row['QTY_AVAILABLE'] <= 5) {
                                            $stockClass = 'bg-yellow-100 text-yellow-800';
                                            $stockText = 'Low Stock';
                                        } else {
                                            $stockClass = 'bg-green-100 text-green-800';
                                            $stockText = 'In Stock';
                                        }
                                ?>
                                    <tr class="table-row border-b border-gray-200 hover:bg-gray-50 transition-all" 
                                        data-category="<?= $row['MEAT_CATEGORY_ID'] ?>"
                                        data-product-id="<?= $row['MEAT_PART_ID'] ?>">
                                        <td class="py-3 px-4 flex items-center">
                                            <div class="flex-shrink-0 h-16 w-16 rounded-md overflow-hidden bg-gray-100 mr-4 product-image">
                                                <img class="h-full w-full object-cover" 
                                                    src="../website/IMAGES/MEATS/<?= $row['MEAT_PART_PHOTO'] ?>" 
                                                    alt="<?= $row['MEAT_PART_NAME'] ?>">
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800 product-name"><?= $row['MEAT_PART_NAME'] ?></p>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="bg-red-50 text-red-700 py-1 px-3 rounded-full text-xs">
                                                <?= $row['MEAT_NAME'] ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="<?= $stockClass ?> py-1 px-3 rounded-full text-xs font-medium">
                                                <?= $stockText ?><br>
                                                <span class="font-bold"><?= $row['QTY_AVAILABLE'] ?></span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?= $row['UNIT_OF_MEASURE'] ?>
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold text-gray-800">
                                            ₱<?= number_format($row['UNIT_PRICE'], 2) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="status-badge bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs font-medium">
                                                Enabled
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex flex-col gap-2">
                                                <a href="edit_product.php?id=<?= $row['MEAT_PART_ID'] ?>" 
                                                class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded flex items-center justify-center gap-1 mx-auto w-24 transition-all">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                
                                                <!-- Toggle Button -->
                                                <button class="toggle-status bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded flex items-center justify-center gap-1 mx-auto w-24 transition-all">
                                                    <i class="fas fa-ban"></i> Disable
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="7" class="py-8 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                                                <p class="text-gray-500">No products found</p>
                                                <a href="add_product.php" class="mt-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                                                    Add your first product
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const productName = row.querySelector('.product-name')?.textContent.toLowerCase() || '';
                const categoryElement = row.querySelector('.bg-red-50');
                const category = categoryElement ? categoryElement.textContent.toLowerCase() : '';
                
                if (productName.includes(searchValue) || category.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Category filter
        const filterButtons = document.querySelectorAll('.category-filter');
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Visual feedback
                filterButtons.forEach(btn => {
                    btn.classList.remove('active', 'bg-red-600', 'text-white');
                    btn.classList.add('bg-gray-200', 'text-gray-700');
                });
                
                this.classList.add('active', 'bg-red-600', 'text-white');
                this.classList.remove('bg-gray-200', 'text-gray-700');
                
                // Filter functionality
                const category = this.getAttribute('data-category');
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    if (category === 'all' || row.getAttribute('data-category') === category) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Enable/Disable Product Toggle
        document.addEventListener('DOMContentLoaded', function() {
            // Get all toggle buttons
            const toggleButtons = document.querySelectorAll('.toggle-status');
            
            // Add click event to each button
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Get the product row
                    const row = this.closest('tr');
                    const productId = row.getAttribute('data-product-id');
                    const productName = row.querySelector('.product-name').textContent;
                    const imageContainer = row.querySelector('.product-image');
                    const statusBadge = row.querySelector('.status-badge');
                    
                    // Check current status
                    const isEnabled = statusBadge.classList.contains('bg-green-100');
                    
                    // Confirm before toggling
                    if (!confirm(`Are you sure you want to ${isEnabled ? 'disable' : 'enable'} "${productName}"?`)) {
                        return;
                    }
                    
                    // Toggle the status
                    if (isEnabled) {
                        // Disable the product
                        statusBadge.classList.remove('bg-green-100', 'text-green-800');
                        statusBadge.classList.add('bg-gray-100', 'text-gray-800');
                        statusBadge.textContent = 'Disabled';
                        
                        // Change button style
                        this.classList.remove('bg-red-500', 'hover:bg-red-600');
                        this.classList.add('bg-blue-500', 'hover:bg-blue-600');
                        this.innerHTML = '<i class="fas fa-check"></i> Enable';
                        
                        // Add visual indication that product is disabled
                        imageContainer.classList.add('opacity-50');
                        row.querySelector('.product-name').classList.add('text-gray-400');
                        row.querySelector('.product-name').classList.remove('text-gray-800');
                    } else {
                        // Enable the product
                        statusBadge.classList.remove('bg-gray-100', 'text-gray-800');
                        statusBadge.classList.add('bg-green-100', 'text-green-800');
                        statusBadge.textContent = 'Enabled';
                        
                        // Change button style
                        this.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                        this.classList.add('bg-red-500', 'hover:bg-red-600');
                        this.innerHTML = '<i class="fas fa-ban"></i> Disable';
                        
                        // Remove visual indication
                        imageContainer.classList.remove('opacity-50');
                        row.querySelector('.product-name').classList.remove('text-gray-400');
                        row.querySelector('.product-name').classList.add('text-gray-800');
                    }
                    
                    // Store the status in local storage so it persists between page loads
                    const disabledProducts = JSON.parse(localStorage.getItem('disabledProducts') || '{}');
                    
                    if (isEnabled) {
                        // Disable the product in storage
                        disabledProducts[productId] = true;
                    } else {
                        // Enable the product in storage
                        delete disabledProducts[productId];
                    }
                    
                    localStorage.setItem('disabledProducts', JSON.stringify(disabledProducts));
                    
                    // Inform the user
                    alert(`Product "${productName}" has been ${isEnabled ? 'disabled' : 'enabled'}.`);
                });
            });
            
            // Apply saved disabled states on page load
            function applySavedStates() {
                const disabledProducts = JSON.parse(localStorage.getItem('disabledProducts') || '{}');
                
                for (const productId in disabledProducts) {
                    const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                    if (row) {
                        const toggleButton = row.querySelector('.toggle-status');
                        const statusBadge = row.querySelector('.status-badge');
                        const imageContainer = row.querySelector('.product-image');
                        
                        // Update badge
                        statusBadge.classList.remove('bg-green-100', 'text-green-800');
                        statusBadge.classList.add('bg-gray-100', 'text-gray-800');
                        statusBadge.textContent = 'Disabled';
                        
                        // Update button
                        toggleButton.classList.remove('bg-red-500', 'hover:bg-red-600');
                        toggleButton.classList.add('bg-blue-500', 'hover:bg-blue-600');
                        toggleButton.innerHTML = '<i class="fas fa-check"></i> Enable';
                        
                        // Update visual style
                        imageContainer.classList.add('opacity-50');
                        row.querySelector('.product-name').classList.add('text-gray-400');
                        row.querySelector('.product-name').classList.remove('text-gray-800');
                    }
                }
            }
            
            // Apply saved states when page loads
            applySavedStates();
        });
    </script>
</body>
</html>