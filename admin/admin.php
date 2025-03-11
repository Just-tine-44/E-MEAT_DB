<?php
include('includes/header.php');
include '../config.php'; // Database connection

// Fetch total quantity for each category using stored procedure
$pork_stock = 0;
$beef_stock = 0;
$chicken_stock = 0;
$total_stock = 0;

$query = "CALL GetMeatStock()";

$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        switch (strtolower($row['category'])) {
            case 'pork':
                $pork_stock = $row['total_stock'];
                break;
            case 'beef':
                $beef_stock = $row['total_stock'];
                break;
            case 'chicken':
                $chicken_stock = $row['total_stock'];
                break;
        }
        $total_stock += $row['total_stock'];
    }
    $result->close();
    $conn->next_result(); // Move to the next result set, if any
}

// Purchase Details
$query = "CALL GetMeatPurchaseDetailed()";
$result = $conn->query($query);

$customers = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $customers[$row['customer_name']][] = $row; // Store separately per order
    }
    $result->close();
    $conn->next_result();
} else {
    die("Error fetching purchase details: " . $conn->error);
}

// Sales Overview
$query = "CALL GetSalesOverview()";
$total_sales_all_time = 0;
$total_sales_last_1_day = 0;
$total_sales_today = 0;

if ($conn->multi_query($query)) {
    do {
        if ($result = $conn->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['total_sales_all_time'])) {
                    $total_sales_all_time = $row['total_sales_all_time'];
                } elseif (isset($row['total_sales_last_1_day'])) {
                    $total_sales_last_1_day = $row['total_sales_last_1_day'];
                } elseif (isset($row['total_sales_today'])) {
                    $total_sales_today = $row['total_sales_today'];
                }
            }
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | E-MEAT</title>
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
        <!-- Dashboard Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
            <div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
                <span>E-MEAT</span>
                <i class="fas fa-chevron-right text-xs"></i>
                <span>Admin Panel</span>
            </div>
        </div>

        <!-- Stock Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Pork Stock -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm mb-1">Pork Stock</p>
                        <p class="text-3xl font-bold"><?php echo $pork_stock; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <i class="fas fa-drumstick-bite text-xl"></i>
                    </div>
                </div>
                <a href="#" class="text-xs text-blue-100 flex items-center gap-1 mt-4 opacity-80 hover:opacity-100 transition">
                    View details <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Beef Stock -->
            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm mb-1">Beef Stock</p>
                        <p class="text-3xl font-bold"><?php echo $beef_stock; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <i class="fas fa-meat text-xl"></i>
                    </div>
                </div>
                <a href="#" class="text-xs text-yellow-100 flex items-center gap-1 mt-4 opacity-80 hover:opacity-100 transition">
                    View details <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Chicken Stock -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm mb-1">Chicken Stock</p>
                        <p class="text-3xl font-bold"><?php echo $chicken_stock; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <i class="fas fa-drumstick-bite text-xl"></i>
                    </div>
                </div>
                <a href="#" class="text-xs text-green-100 flex items-center gap-1 mt-4 opacity-80 hover:opacity-100 transition">
                    View details <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Total Stock -->
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm mb-1">Total Stock</p>
                        <p class="text-3xl font-bold"><?php echo $total_stock; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <i class="fas fa-warehouse text-xl"></i>
                    </div>
                </div>
                <a href="#" class="text-xs text-red-100 flex items-center gap-1 mt-4 opacity-80 hover:opacity-100 transition">
                    View details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Sales Overview -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-chart-line text-red-500"></i> Sales Overview
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- All Time Sales -->
                <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-sm font-medium text-gray-500">Total Sales (All Time)</p>
                        <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-gray-800 flex items-center">
                        <span class="text-lg mr-1">₱</span>
                        <?php echo number_format($total_sales_all_time, 2); ?>
                    </p>
                    <div class="mt-2 text-xs text-gray-400">Since the beginning</div>
                </div>

                <!-- Last 1 Day Sales -->
                <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-sm font-medium text-gray-500">Sales (Last 24 Hours)</p>
                        <div class="bg-yellow-100 text-yellow-800 p-2 rounded-lg">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-gray-800 flex items-center">
                        <span class="text-lg mr-1">₱</span>
                        <?php echo number_format($total_sales_last_1_day, 2); ?>
                    </p>
                    <div class="mt-2 text-xs text-gray-400">Past 24 hours</div>
                </div>

                <!-- Today's Sales -->
                <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-sm font-medium text-gray-500">Today's Sales</p>
                        <div class="bg-green-100 text-green-800 p-2 rounded-lg">
                            <i class="fas fa-cash-register"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-gray-800 flex items-center">
                        <span class="text-lg mr-1">₱</span>
                        <?php echo number_format($total_sales_today, 2); ?>
                    </p>
                    <div class="mt-2 text-xs text-gray-400">Since midnight</div>
                </div>
            </div>
        </div>

        <!-- Purchase Details -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-shopping-cart text-red-500"></i> Purchase Details
            </h2>
            <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-100">
                <div class="p-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <div class="font-medium text-gray-700">Customer Purchases</div>
                    <input type="text" id="customerSearch" placeholder="Search customer..." 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer Name
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="customerTable">
                            <?php foreach ($customers as $customer_name => $purchases): ?>
                                <tr class="hover:bg-gray-50 customer-row">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="#" class="customer-link text-blue-600 hover:text-blue-800 font-medium flex items-center gap-2"
                                           data-customer='<?= htmlspecialchars(json_encode($purchases)) ?>' 
                                           data-customer-name='<?= htmlspecialchars($customer_name) ?>'>
                                            <i class="fas fa-user"></i>
                                            <?= htmlspecialchars($customer_name) ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Purchase Details -->
    <div id="purchaseModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800" id="purchaseDetailsModalLabel">Purchase Details</h3>
                <button type="button" id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto flex-grow">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Meat Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Meat Part</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="purchaseDetailsBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="p-4 border-t border-gray-200 flex justify-end">
                <button type="button" id="closeModalBtn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-800">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Customer search functionality
        document.getElementById('customerSearch').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('.customer-row');
            
            rows.forEach(row => {
                const customerName = row.textContent.toLowerCase();
                if (customerName.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Modal functionality
        const modal = document.getElementById('purchaseModal');
        const closeModal = document.getElementById('closeModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        
        closeModal.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
        
        closeModalBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
        
        // Purchase details links
        document.querySelectorAll('.customer-link').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                const customerName = this.getAttribute('data-customer-name');
                document.getElementById('purchaseDetailsModalLabel').innerText = customerName + '\'s Purchases';

                const purchases = JSON.parse(this.getAttribute('data-customer'));
                const tbody = document.getElementById('purchaseDetailsBody');
                tbody.innerHTML = '';

                purchases.forEach(function(purchase) {
                    const row = `
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">${purchase.meat_category}</td>
                            <td class="px-4 py-3">${purchase.MEAT_PART_NAME}</td>
                            <td class="px-4 py-3">${purchase.total_quantity} ${purchase.UNIT_OF_MEASURE}</td>
                            <td class="px-4 py-3">₱${parseFloat(purchase.UNIT_PRICE).toFixed(2)}</td>
                            <td class="px-4 py-3 font-medium text-green-600">₱${parseFloat(purchase.total_amount).toFixed(2)}</td>
                            <td class="px-4 py-3">${purchase.order_date}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });

                modal.classList.remove('hidden');
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