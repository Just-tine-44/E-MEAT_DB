<?php
session_start(); // Start the session

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Authentication check
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    $_SESSION['message'] = "You need to log in as admin to access this page";
    header("Location: ../users/login.php");
    exit();
}

$page_title = "Sales Reports | E-MEAT Admin";
include('new_include/sidebar.php');
include '../connection/config.php'; // Database connection

// Set default date range (last 30 days)
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-30 days'));

// Check if date filter is applied
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// Helper function to format numbers as currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Initialize arrays with default values
$overview = [
    'total_orders' => 0,
    'total_sales' => 0,
    'total_customers' => 0,
    'avg_order_value' => 0
];

$daily_dates = [];
$daily_sales = [];
$product_names = [];
$product_sales = [];
$category_names = [];
$category_sales = [];
$recent_orders = [];

// Fetch basic sales data
try {
    // Get total sales, orders and customers
    $sql = "SELECT 
              COUNT(DISTINCT o.ORDERS_ID) as total_orders,
              IFNULL(SUM(od.LINE_TOTAL), 0) as total_sales,
              COUNT(DISTINCT o.APP_USER_ID) as total_customers,
              IFNULL(AVG(o.TOTAL_AMOUNT), 0) as avg_order_value
            FROM ORDERS o
            JOIN ORDERS_DETAIL od ON o.ORDERS_ID = od.ORDERS_ID
            WHERE o.ORDERS_DATE BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $overview = $result->fetch_assoc();
    }
    $stmt->close();
    
    // Get daily sales for chart
    $sql = "SELECT 
              DATE(o.ORDERS_DATE) as sale_date,
              IFNULL(SUM(od.LINE_TOTAL), 0) as total_sales
            FROM ORDERS o
            JOIN ORDERS_DETAIL od ON o.ORDERS_ID = od.ORDERS_ID
            WHERE o.ORDERS_DATE BETWEEN ? AND ?
            GROUP BY DATE(o.ORDERS_DATE)
            ORDER BY sale_date";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $daily_dates[] = date('M d', strtotime($row['sale_date']));
        $daily_sales[] = floatval($row['total_sales']);
    }
    $stmt->close();
    
    // Get top selling products
    $sql = "SELECT 
              mp.MEAT_PART_NAME as product_name,
              IFNULL(SUM(od.LINE_TOTAL), 0) as total_sales
            FROM ORDERS_DETAIL od
            JOIN ORDERS o ON o.ORDERS_ID = od.ORDERS_ID
            JOIN MEAT_PART mp ON od.MEAT_PART_ID = mp.MEAT_PART_ID
            WHERE o.ORDERS_DATE BETWEEN ? AND ?
            GROUP BY od.MEAT_PART_ID
            ORDER BY total_sales DESC
            LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $product_names[] = $row['product_name'];
        $product_sales[] = floatval($row['total_sales']);
    }
    $stmt->close();
    
    // Get sales by category
    $sql = "SELECT 
              mc.MEAT_NAME as category_name,
              IFNULL(SUM(od.LINE_TOTAL), 0) as total_sales
            FROM ORDERS_DETAIL od
            JOIN ORDERS o ON o.ORDERS_ID = od.ORDERS_ID
            JOIN MEAT_PART mp ON od.MEAT_PART_ID = mp.MEAT_PART_ID
            JOIN MEAT_CATEGORY mc ON mp.MEAT_CATEGORY_ID = mc.MEAT_CATEGORY_ID
            WHERE o.ORDERS_DATE BETWEEN ? AND ?
            GROUP BY mc.MEAT_CATEGORY_ID
            ORDER BY total_sales DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $category_names[] = $row['category_name'];
        $category_sales[] = floatval($row['total_sales']);
    }
    $stmt->close();
    
    // Get recent orders
    $sql = "SELECT 
              o.ORDERS_ID as order_id,
              CONCAT(u.USER_FNAME, ' ', u.USER_LNAME) as customer_name,
              o.ORDERS_DATE as order_date,
              s.STATUS_NAME as status_name,
              o.TOTAL_AMOUNT as total_amount
            FROM ORDERS o
            JOIN STATUS s ON o.STAT_ID = s.STAT_ID
            JOIN APP_USER u ON o.APP_USER_ID = u.APP_USER_ID
            ORDER BY o.ORDERS_DATE DESC
            LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $recent_orders[] = $row;
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
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Date Range Picker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #E53E3E;
            --primary-light: #FEF2F2;
            --secondary: #4F46E5;
            --accent: #10B981;
            --dark: #1F2937;
            --light: #F9FAFB;
            --gray-light: #F3F4F6;
            --gray: #9CA3AF;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #FAFAFA;
        }
        
        .metric-card {
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .metric-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            width: 5px;
        }
        
        .metric-card:nth-child(1)::after { background-color: var(--primary); }
        .metric-card:nth-child(2)::after { background-color: var(--secondary); }
        .metric-card:nth-child(3)::after { background-color: var(--accent); }
        .metric-card:nth-child(4)::after { background-color: #8B5CF6; }
        
        .chart-container {
            border-radius: 0.75rem;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .chart-container:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .table-header {
            background-color: var(--gray-light);
            color: var(--dark);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-pending { background-color: #FEF3C7; color: #92400E; }
        .status-processing { background-color: #DBEAFE; color: #1E40AF; }
        .status-shipped { background-color: #E0E7FF; color: #3730A3; }
        .status-delivered { background-color: #D1FAE5; color: #065F46; }
        
        @media print {
            .no-print { display: none; }
            body { background-color: white; }
            .chart-container, .metric-card {
                break-inside: avoid;
                page-break-inside: avoid;
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .animate-delay-100 { animation-delay: 0.1s; }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-300 { animation-delay: 0.3s; }
        .animate-delay-400 { animation-delay: 0.4s; }
        
        /* Date Range Picker Customization */
        .daterangepicker {
            font-family: 'Inter', sans-serif;
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .daterangepicker .ranges li.active {
            background-color: var(--primary);
        }
        
        .daterangepicker td.active, .daterangepicker td.active:hover {
            background-color: var(--primary);
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="pl-0 lg:pl-64 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
            <!-- Page Header -->
            <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="animate-fade-in">
                    <h1 class="text-3xl font-bold text-gray-900">Sales Report</h1>
                    <div class="flex items-center mt-2 text-gray-600">
                        <i class="far fa-calendar mr-2"></i>
                        <span class="text-sm">
                            <?= date('F d, Y', strtotime($start_date)) ?> - 
                            <?= date('F d, Y', strtotime($end_date)) ?>
                        </span>
                    </div>
                </div>
                
                <!-- Date Range Filter -->
                <div class="flex items-center space-x-3 no-print animate-fade-in animate-delay-100">
                    <div class="relative">
                        <form id="dateRangeForm" action="" method="GET">
                            <input type="text" id="daterange" name="daterange" 
                                   class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                                   value="<?= date('m/d/Y', strtotime($start_date)) ?> - <?= date('m/d/Y', strtotime($end_date)) ?>">
                            <input type="hidden" name="start_date" id="start_date" value="<?= $start_date ?>">
                            <input type="hidden" name="end_date" id="end_date" value="<?= $end_date ?>">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                        </form>
                    </div>
                    
                    <button id="printReport" class="flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <i class="fas fa-download mr-2"></i>
                        Export
                    </button>
                </div>
            </div>
            
            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Sales -->
                <div class="metric-card bg-white rounded-xl p-6 shadow-sm animate-fade-in">
                    <div class="flex items-center mb-4">
                        <div class="bg-red-100 w-12 h-12 flex items-center justify-center rounded-lg">
                            <i class="fas fa-chart-line text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Sales</p>
                            <h3 class="text-2xl font-bold mt-1 text-gray-900"><?= formatCurrency($overview['total_sales']) ?></h3>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-red-600 h-1.5 rounded-full" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Total Orders -->
                <div class="metric-card bg-white rounded-xl p-6 shadow-sm animate-fade-in animate-delay-100">
                    <div class="flex items-center mb-4">
                        <div class="bg-indigo-100 w-12 h-12 flex items-center justify-center rounded-lg">
                            <i class="fas fa-shopping-cart text-indigo-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Orders</p>
                            <h3 class="text-2xl font-bold mt-1 text-gray-900"><?= $overview['total_orders'] ?></h3>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-indigo-600 h-1.5 rounded-full" style="width: 65%"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Average Order -->
                <div class="metric-card bg-white rounded-xl p-6 shadow-sm animate-fade-in animate-delay-200">
                    <div class="flex items-center mb-4">
                        <div class="bg-emerald-100 w-12 h-12 flex items-center justify-center rounded-lg">
                            <i class="fas fa-receipt text-emerald-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Average Order</p>
                            <h3 class="text-2xl font-bold mt-1 text-gray-900"><?= formatCurrency($overview['avg_order_value']) ?></h3>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-emerald-600 h-1.5 rounded-full" style="width: 70%"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Total Customers -->
                <div class="metric-card bg-white rounded-xl p-6 shadow-sm animate-fade-in animate-delay-300">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-100 w-12 h-12 flex items-center justify-center rounded-lg">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Customers</p>
                            <h3 class="text-2xl font-bold mt-1 text-gray-900"><?= $overview['total_customers'] ?></h3>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-purple-600 h-1.5 rounded-full" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Daily Sales Chart -->
                <div class="lg:col-span-2 animate-fade-in animate-delay-100">
                    <div class="chart-container">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Revenue Trends</h3>
                            <div class="flex space-x-2 no-print">
                                <button data-range="7" class="range-btn px-3 py-1 text-xs font-medium rounded-md bg-red-100 text-red-700">7D</button>
                                <button data-range="30" class="range-btn px-3 py-1 text-xs font-medium rounded-md text-gray-500 hover:bg-gray-100">30D</button>
                                <button data-range="90" class="range-btn px-3 py-1 text-xs font-medium rounded-md text-gray-500 hover:bg-gray-100">90D</button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="dailySalesChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Category Distribution -->
                <div class="animate-fade-in animate-delay-200">
                    <div class="chart-container">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Category Split</h3>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-circle text-red-500 text-xs mr-1"></i>
                                <span>By Revenue</span>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Products and Recent Orders -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Top Selling Products -->
                <div class="animate-fade-in animate-delay-300">
                    <div class="chart-container">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Products</h3>
                        <div class="overflow-hidden">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="table-header px-6 py-3 text-left rounded-l-lg">Product</th>
                                        <th class="table-header px-6 py-3 text-right rounded-r-lg">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($product_names as $i => $name): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-2 h-2 rounded-full bg-red-500 mr-3"></div>
                                                <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($name) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            <?= formatCurrency($product_sales[$i]) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($product_names) === 0): ?>
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No data available</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="animate-fade-in animate-delay-400">
                    <div class="chart-container">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                            <a href="status_order.php" class="text-xs font-medium text-red-600 hover:text-red-800 no-print">View All</a>
                        </div>
                        <div class="overflow-hidden">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="table-header px-6 py-3 text-left rounded-l-lg">Order</th>
                                        <th class="table-header px-6 py-3 text-left">Customer</th>
                                        <th class="table-header px-6 py-3 text-left">Status</th>
                                        <th class="table-header px-6 py-3 text-right rounded-r-lg">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($recent_orders as $order): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="ml-2 text-xs text-gray-500"><?= date('M d', strtotime($order['order_date'])) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($order['customer_name']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                                $statusClass = 'status-pending';
                                                if (stripos($order['status_name'], 'pending') !== false) {
                                                    $statusClass = 'status-pending';
                                                } elseif (stripos($order['status_name'], 'process') !== false) {
                                                    $statusClass = 'status-processing';
                                                } elseif (stripos($order['status_name'], 'ship') !== false || stripos($order['status_name'], 'transit') !== false) {
                                                    $statusClass = 'status-shipped';
                                                } elseif (stripos($order['status_name'], 'deliver') !== false || stripos($order['status_name'], 'complete') !== false) {
                                                    $statusClass = 'status-delivered';
                                                }
                                            ?>
                                            <span class="status-badge <?= $statusClass ?>">
                                                <?= htmlspecialchars($order['status_name']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                            <?= formatCurrency($order['total_amount']) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($recent_orders) === 0): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No recent orders</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center text-sm text-gray-400 mt-8 no-print animate-fade-in animate-delay-400">
                <p>Report generated on <?= date('F d, Y h:i A') ?> • E-MEAT Analytics Dashboard</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Date Range Picker
            $('#daterange').daterangepicker({
                startDate: '<?= date('m/d/Y', strtotime($start_date)) ?>',
                endDate: '<?= date('m/d/Y', strtotime($end_date)) ?>',
                maxDate: moment(),
                locale: {
                    format: 'MM/DD/YYYY'
                },
                ranges: {
                   'Today': [moment(), moment()],
                   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                   'This Month': [moment().startOf('month'), moment().endOf('month')],
                   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, function(start, end, label) {
                document.getElementById('start_date').value = start.format('YYYY-MM-DD');
                document.getElementById('end_date').value = end.format('YYYY-MM-DD');
                document.getElementById('dateRangeForm').submit();
            });
            
            // Range buttons
            document.querySelectorAll('.range-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const days = parseInt(this.dataset.range);
                    const end = moment();
                    const start = moment().subtract(days-1, 'days');
                    
                    document.getElementById('start_date').value = start.format('YYYY-MM-DD');
                    document.getElementById('end_date').value = end.format('YYYY-MM-DD');
                    document.getElementById('dateRangeForm').submit();
                    
                    // Update active button
                    document.querySelectorAll('.range-btn').forEach(btn => {
                        btn.classList.remove('bg-red-100', 'text-red-700');
                        btn.classList.add('text-gray-500', 'hover:bg-gray-100');
                    });
                    this.classList.remove('text-gray-500', 'hover:bg-gray-100');
                    this.classList.add('bg-red-100', 'text-red-700');
                });
            });
            
            // Print Report
            document.getElementById('printReport').addEventListener('click', function() {
                window.print();
            });
            
            // Chart.js configurations
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#6B7280';
            Chart.defaults.elements.line.tension = 0.3;
            Chart.defaults.plugins.tooltip.backgroundColor = 'white';
            Chart.defaults.plugins.tooltip.titleColor = '#111827';
            Chart.defaults.plugins.tooltip.bodyColor = '#4B5563';
            Chart.defaults.plugins.tooltip.borderColor = '#E5E7EB';
            Chart.defaults.plugins.tooltip.borderWidth = 1;
            Chart.defaults.plugins.tooltip.padding = 12;
            Chart.defaults.plugins.tooltip.cornerRadius = 8;
            Chart.defaults.plugins.tooltip.displayColors = false;
            Chart.defaults.plugins.tooltip.mode = 'index';
            
            // Daily Sales Chart
            const dailySalesLabels = <?= json_encode($daily_dates) ?>;
            const dailySalesData = <?= json_encode($daily_sales) ?>;
            
            if (document.getElementById('dailySalesChart') && dailySalesLabels.length > 0) {
                const gradient = document.getElementById('dailySalesChart').getContext('2d').createLinearGradient(0, 0, 0, 225);
                gradient.addColorStop(0, 'rgba(239, 68, 68, 0.3)');
                gradient.addColorStop(1, 'rgba(239, 68, 68, 0.0)');
                
                new Chart(document.getElementById('dailySalesChart'), {
                    type: 'line',
                    data: {
                        labels: dailySalesLabels,
                        datasets: [{
                            label: 'Revenue',
                            data: dailySalesData,
                            borderColor: '#DC2626',
                            backgroundColor: gradient,
                            borderWidth: 3,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#FFF',
                            pointHoverBackgroundColor: '#FFF',
                            pointBorderColor: '#DC2626',
                            pointBorderWidth: 2,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    },
                                    padding: 10
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    padding: 10
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '₱' + context.parsed.y.toLocaleString();
                                    },
                                    title: function(context) {
                                        return 'Revenue: ' + context[0].label;
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Category Distribution Chart
            const categoryLabels = <?= json_encode($category_names) ?>;
            const categorySalesData = <?= json_encode($category_sales) ?>;
            const categoryColors = ['#DC2626', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899'];
            
            if (document.getElementById('categoryChart') && categoryLabels.length > 0) {
                new Chart(document.getElementById('categoryChart'), {
                    type: 'doughnut',
                    data: {
                        labels: categoryLabels,
                        datasets: [{
                            data: categorySalesData,
                            backgroundColor: categoryColors,
                            borderColor: '#FFFFFF',
                            borderWidth: 3,
                            hoverOffset: 6,
                            borderRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    boxWidth: 8
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${context.label}: ₱${value.toLocaleString()} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>