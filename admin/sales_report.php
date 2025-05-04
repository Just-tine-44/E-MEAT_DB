<?php
session_start();
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    $_SESSION['message'] = "You need to log in as admin to access this page";
    header("Location: ../users/login.php");
    exit();
}

$page_title = "Sales Reports | E-MEAT Admin";
include('new_include/sidebar.php');
include '../connection/config.php';
date_default_timezone_set('Asia/Manila');

// Initialize date filters
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-30 days'));
$filter_type = 'last30days';

// Handle filter selection
if (isset($_GET['filter_type'])) {
    $filter_type = $_GET['filter_type'];
    
    switch ($filter_type) {
        case 'today':
            $start_date = $end_date = date('Y-m-d');
            break;
        case 'thisweek':
            $start_date = date('Y-m-d', strtotime('monday this week'));
            break;
        case 'thismonth':
            $start_date = date('Y-m-d', strtotime('first day of this month'));
            break;
        case 'custom':
            if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                $start_date = $_GET['start_date'];
                $end_date = $_GET['end_date'];
            }
            break;
    }
} elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $filter_type = 'custom';
}

$db_start_date = $start_date . ' 00:00:00';
$db_end_date = $end_date . ' 23:59:59';

// Helper function for currency formatting
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Initialize data arrays
$overview = ['total_orders' => 0, 'total_sales' => 0, 'total_customers' => 0, 'avg_order_value' => 0];
$top_products = [];
$daily_dates = [];
$daily_sales = [];
$customer_purchases = [];

// Fetch sales data using stored procedures
try {
    // 1. Get sales overview data
    $stmt = $conn->prepare("CALL GetSales(?, ?)");
    $stmt->bind_param("ss", $db_start_date, $db_end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $overview = $result->fetch_assoc();
    }
    $stmt->close();
    $conn->next_result(); // Clear stored procedure result
    
    // 2. Get daily sales for chart
    $stmt = $conn->prepare("CALL GetDailySales(?, ?)");
    $stmt->bind_param("ss", $db_start_date, $db_end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $daily_dates[] = date('M d', strtotime($row['sale_date']));
        $daily_sales[] = floatval($row['total_sales']);
    }
    $stmt->close();
    $conn->next_result(); // Clear stored procedure result
    
    // 3. Get top selling products
    $limit = 10; // Number of top products to show
    $stmt = $conn->prepare("CALL GetTopProducts(?, ?, ?)");
    $stmt->bind_param("ssi", $db_start_date, $db_end_date, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $top_products[] = $row;
    }
    $stmt->close();
    $conn->next_result(); // Clear stored procedure result
    
    // 4. Get customer purchases
    $limit = 10; // Number of top customers to show
    $stmt = $conn->prepare("CALL GetCustomerPurchases(?, ?, ?)");
    $stmt->bind_param("ssi", $db_start_date, $db_end_date, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $customer_purchases[] = $row;
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error in sales_report: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body class="bg-gray-50 font-sans">
    <div class="pl-0 lg:pl-64 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
            
            <!-- Page Header with Date Filters -->
            <div class="mb-6">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4">
                    <h1 class="text-2xl font-bold text-gray-900">Sales Report</h1>
                    
                    <div class="mt-3 lg:mt-0 bg-white rounded-lg shadow-sm p-2 flex flex-wrap gap-2">
                        <a href="?filter_type=today" class="px-3 py-1.5 rounded-lg text-sm border <?= $filter_type == 'today' ? 'bg-red-600 text-white' : 'border-gray-200 hover:bg-gray-50' ?>">Today</a>
                        <a href="?filter_type=thisweek" class="px-3 py-1.5 rounded-lg text-sm border <?= $filter_type == 'thisweek' ? 'bg-red-600 text-white' : 'border-gray-200 hover:bg-gray-50' ?>">This Week</a>
                        <a href="?filter_type=thismonth" class="px-3 py-1.5 rounded-lg text-sm border <?= $filter_type == 'thismonth' ? 'bg-red-600 text-white' : 'border-gray-200 hover:bg-gray-50' ?>">This Month</a>
                        
                        <form id="dateRangeForm" action="" method="GET" class="flex items-center">
                            <input type="hidden" name="filter_type" value="custom">
                            <input type="date" name="start_date" value="<?= $start_date ?>" class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 w-32">
                            <span class="mx-1 text-gray-500">to</span>
                            <input type="date" name="end_date" value="<?= $end_date ?>" class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 w-32">
                            <button type="submit" class="ml-2 bg-red-600 text-white text-sm px-3 py-1.5 rounded-lg">Apply</button>
                        </form>
                        
                        <button id="printReport" class="bg-gray-800 text-white text-sm px-3 py-1.5 rounded-lg flex items-center">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>
                
                <div class="inline-flex items-center bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <span>
                        <?= date('F d, Y', strtotime($start_date)) ?> 
                        <?= ($start_date != $end_date) ? ' - ' . date('F d, Y', strtotime($end_date)) : '' ?>
                    </span>
                </div>
            </div>
            
            <!-- Sales Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Sales -->
                <div class="bg-white rounded-lg shadow-sm p-4 border-t-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Sales</p>
                            <p class="text-2xl font-bold mt-1"><?= formatCurrency($overview['total_sales']) ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Total Orders -->
                <div class="bg-white rounded-lg shadow-sm p-4 border-t-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Orders</p>
                            <p class="text-2xl font-bold mt-1"><?= number_format($overview['total_orders']) ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Average Order -->
                <div class="bg-white rounded-lg shadow-sm p-4 border-t-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Avg. Order Value</p>
                            <p class="text-2xl font-bold mt-1"><?= formatCurrency($overview['avg_order_value']) ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Customers -->
                <div class="bg-white rounded-lg shadow-sm p-4 border-t-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Customers</p>
                            <p class="text-2xl font-bold mt-1"><?= number_format($overview['total_customers']) ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sales Chart -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <h2 class="text-lg font-semibold mb-4">Sales Trend</h2>
                <div style="height: 280px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            
            <!-- Two Column Layout for Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Products -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h2 class="font-semibold">Top Selling Products</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty Sold</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (count($top_products) > 0): ?>
                                    <?php foreach ($top_products as $index => $product): ?>
                                    <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-medium mr-2"><?= $index + 1 ?></span>
                                            <span class="text-sm font-medium"><?= htmlspecialchars($product['product_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm">
                                        <?= number_format($product['quantity_sold']) ?> 
                                        <span class="text-xs text-gray-500"><?= strtoupper($product['unit_measure']) ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium"><?= formatCurrency($product['total_sales']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Customer Purchases -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h2 class="font-semibold">Customer Purchases</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Orders</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (count($customer_purchases) > 0): ?>
                                    <?php foreach ($customer_purchases as $customer): ?>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-medium mr-2">
                                                    <?= strtoupper(substr($customer['customer_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium"><?= htmlspecialchars($customer['customer_name']) ?></div>
                                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($customer['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full"><?= $customer['order_count'] ?></span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-medium"><?= formatCurrency($customer['total_spent']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="text-center text-sm text-gray-500 mt-6">
                <p>Report generated on <?= date('F d, Y h:i A') ?></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Date range validation
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateInput = document.querySelector('input[name="end_date"]');
            
            endDateInput.addEventListener('change', function() {
                if (endDateInput.value < startDateInput.value) {
                    endDateInput.value = startDateInput.value;
                    alert('End date cannot be before start date');
                }
            });
            
            startDateInput.addEventListener('change', function() {
                if (startDateInput.value > endDateInput.value) {
                    startDateInput.value = endDateInput.value;
                    alert('Start date cannot be after end date');
                }
            });
            
            // Print functionality
            document.getElementById('printReport').addEventListener('click', function() {
                window.print();
            });
            
            // Sales Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($daily_dates) ?>,
                    datasets: [{
                        label: 'Daily Sales',
                        data: <?= json_encode($daily_sales) ?>,
                        backgroundColor: 'rgba(229, 62, 62, 0.1)',
                        borderColor: 'rgba(229, 62, 62, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: 'rgba(229, 62, 62, 1)',
                        pointRadius: 4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Sales: ₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>