<?php
$page_title = "Product Inventory | E-MEAT Admin";
include('includes/header.php');
include '../connection/config.php'; // Database connection

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
    echo "<div class='bg-red-100 text-red-700 p-4 rounded mb-4'>Unable to load product data. Please try again later.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Inventory</title>
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
    </style>
</head>
<body class="bg-gray-50">
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
        
        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-2">
            <button class="category-filter active px-4 py-2 rounded-full bg-red-600 text-white hover:bg-red-700 transition-all" data-category="all">
                All Categories
            </button>
            <?php if ($categories->num_rows > 0): ?>
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
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-4 text-left">Product</th>
                            <th class="py-3 px-4 text-left">Category</th>
                            <th class="py-3 px-4 text-center">Stock</th>
                            <th class="py-3 px-4 text-center">Unit</th>
                            <th class="py-3 px-4 text-right">Price</th>
                            <th class="py-3 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        <?php 
                        if ($result->num_rows > 0) {
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
                            <tr class="table-row border-b border-gray-200 hover:bg-gray-50 transition-all" data-category="<?= $row['MEAT_CATEGORY_ID'] ?>">
                                <td class="py-3 px-4 flex items-center">
                                    <div class="flex-shrink-0 h-16 w-16 rounded-md overflow-hidden bg-gray-100 mr-4">
                                        <img class="h-full w-full object-cover" 
                                             src="../website/IMAGES/MEATS/<?= $row['MEAT_PART_PHOTO'] ?>" 
                                             alt="<?= $row['MEAT_PART_NAME'] ?>">
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800"><?= $row['MEAT_PART_NAME'] ?></p>
                                        <p class="text-xs text-gray-500">ID: <?= $row['MEAT_PART_ID'] ?></p>
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
                                    <a href="edit_product.php?id=<?= $row['MEAT_PART_ID'] ?>" 
                                       class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded flex items-center justify-center gap-1 mx-auto w-24 transition-all">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center">
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

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const productName = row.querySelector('.font-medium').textContent.toLowerCase();
                const category = row.querySelector('.bg-red-50').textContent.toLowerCase();
                
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
    </script>
</body>
</html>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>