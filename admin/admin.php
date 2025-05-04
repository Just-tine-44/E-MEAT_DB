<?php
session_start(); // Start the session

// To match what login.php is setting:
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    $_SESSION['message'] = "You need to log in as admin to access this page";
    header("Location: ../users/login.php");
    exit();
}

// Include the sidebar first
include('new_include/sidebar.php');
include '../connection/config.php'; // Database connection

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

// Purchase Details with date filtering
try {
    $stmt = $conn->prepare("CALL GetMeatPurchaseDetailedByDate(?, ?)");
    $stmt->bind_param("ss", $db_start_date, $db_end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $customers = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $customers[$row['customer_name']][] = $row; // Store separately per order
        }
    }
    $stmt->close();
    $conn->next_result(); // Clear result sets
} catch (Exception $e) {
    error_log("Error fetching purchase details: " . $e->getMessage());
}

// Sales Overview with date filtering
try {
    $stmt = $conn->prepare("CALL GetSalesOverviewByDate(?, ?)");
    $stmt->bind_param("ss", $db_start_date, $db_end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total_sales_all_time = 0; // Will be replaced with period sales
    $total_sales_last_1_day = 0;
    $total_sales_this_week = 0;
    $total_sales_this_month = 0;
    
    if ($result && $row = $result->fetch_assoc()) {
        $total_sales_all_time = $row['total_sales_period'];
        $total_sales_last_1_day = $row['total_sales_last_1_day'];
        $total_sales_this_week = $row['total_sales_this_week'];
        $total_sales_this_month = $row['total_sales_this_month'];
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error fetching sales overview: " . $e->getMessage());
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
    <!-- jsPDF libraries for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Main Content Wrapper - position it to the right of the sidebar -->
    <div class="pl-0 lg:pl-64 transition-all duration-300"> <!-- Padding for sidebar width -->
        <!-- Page Content -->
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
            
            <!-- Date Filter Section -->
            <div class="mb-6 bg-white rounded-lg shadow-sm p-4 no-print">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3 md:mb-0">
                        <i class="fas fa-calendar-alt text-red-500 mr-2"></i> Date Range Filter
                    </h2>
                    
                    <div class="flex flex-wrap gap-2 items-center">
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
                    </div>
                </div>
                
                <div class="mt-3 inline-flex items-center bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span>
                        Showing data from <?= date('F d, Y', strtotime($start_date)) ?> 
                        <?= ($start_date != $end_date) ? ' to ' . date('F d, Y', strtotime($end_date)) : '' ?>
                    </span>
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
                <button id="printSalesBtn" class="px-4 py-2 mb-4 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm flex items-center gap-2 transition-colors no-print">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Selected Period Sales -->
                    <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-sm font-medium text-gray-500">Selected Period Sales</p>
                            <div class="bg-red-100 text-red-800 p-2 rounded-lg">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-800 flex items-center">
                            <span class="text-lg mr-1">₱</span>
                            <?php echo number_format($total_sales_all_time, 2); ?>
                        </p>
                        <div class="mt-2 text-xs text-gray-400">
                            <?= date('M d', strtotime($start_date)) ?> - <?= date('M d', strtotime($end_date)) ?>
                        </div>
                    </div>

                    <!-- Last 24 Hours Sales -->
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

                    <!-- This Week's Sales -->
                    <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-sm font-medium text-gray-500">This Week's Sales</p>
                            <div class="bg-purple-100 text-purple-800 p-2 rounded-lg">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-800 flex items-center">
                            <span class="text-lg mr-1">₱</span>
                            <?php echo number_format($total_sales_this_week, 2); ?>
                        </p>
                        <div class="mt-2 text-xs text-gray-400">Current week</div>
                    </div>

                    <!-- This Month's Sales -->
                    <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-sm font-medium text-gray-500">This Month's Sales</p>
                            <div class="bg-green-100 text-green-800 p-2 rounded-lg">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-800 flex items-center">
                            <span class="text-lg mr-1">₱</span>
                            <?php echo number_format($total_sales_this_month, 2); ?>
                        </p>
                        <div class="mt-2 text-xs text-gray-400">Current month</div>
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
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 no-print">
                    </div>
                    <div class="overflow-x-auto">
                        <div class="max-h-64 overflow-y-auto" style="max-height: calc(4 * 56px);">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer Name
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="customerTable">
                            <?php if (count($customers) > 0): ?>
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
                            <?php else: ?>
                                <tr>
                                    <td class="px-6 py-4 text-center text-gray-500">No customers found in the selected date range</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Purchase Details -->
    <div id="purchaseModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 no-print">
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
                <button type="button" id="exportCustomerPurchasesBtn" class="px-4 py-2 mr-4 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i> Export to PDF
                </button>
                <button type="button" id="closeModalBtn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-800">
                    Close
                </button>
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
                currentCustomerName = this.getAttribute('data-customer-name');
                document.getElementById('purchaseDetailsModalLabel').innerText = currentCustomerName + '\'s Purchases';

                currentCustomerData = JSON.parse(this.getAttribute('data-customer'));
                const tbody = document.getElementById('purchaseDetailsBody');
                tbody.innerHTML = '';

                currentCustomerData.forEach(function(purchase) {
                    const row = `
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">${purchase.meat_category}</td>
                            <td class="px-4 py-3">${purchase.MEAT_PART_NAME.toUpperCase()}</td>
                            <td class="px-4 py-3">${parseFloat(purchase.total_quantity).toFixed(1)} ${purchase.UNIT_OF_MEASURE}</td>
                            <td class="px-4 py-3">₱${parseFloat(purchase.UNIT_PRICE).toFixed(2)}</td>
                            <td class="px-4 py-3 font-medium text-green-600">₱${parseFloat(purchase.total_amount).toFixed(2)}</td>
                            <td class="px-4 py-3">${new Date(purchase.order_date).toLocaleDateString('en-CA')}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });

                modal.classList.remove('hidden');
            });
        });
    });
    </script>

    <script>
        // Fixed JavaScript code for the sales table
        document.getElementById('printSalesBtn').addEventListener('click', function() {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            
            // Create the print content with proper PHP variable insertion
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>E-MEAT Sales Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 40px; }
                        h1 { color: #333; margin-bottom: 10px; }
                        .report-date { color: #777; margin-bottom: 30px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
                        th { background-color: #f8f8f8; font-weight: bold; }
                        .amount { text-align: right; }
                        .note { font-size: 12px; color: #777; font-style: italic; }
                    </style>
                </head>
                <body>
                    <h1>E-MEAT Sales Report</h1>
                    <p class="report-date">Generated on: ${new Date().toLocaleString()}</p>
                    <p class="report-date">Period: <?= date('F d, Y', strtotime($start_date)) ?> to <?= date('F d, Y', strtotime($end_date)) ?></p>
                    
                    <h2>Sales Overview</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Time Period</th>
                                <th class="amount">Amount (PHP)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Selected Period Sales</td>
                                <td class="amount">₱ <?php echo number_format($total_sales_all_time, 2); ?></td>
                            </tr>
                            <tr>
                                <td>Last 24 Hours Sales</td>
                                <td class="amount">₱ <?php echo number_format($total_sales_last_1_day, 2); ?></td>
                            </tr>
                            <tr>
                                <td>This Week's Sales</td>
                                <td class="amount">₱ <?php echo number_format($total_sales_this_week, 2); ?></td>
                            </tr>
                            <tr>
                                <td>This Month's Sales</td>
                                <td class="amount">₱ <?php echo number_format($total_sales_this_month, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <p class="note">This report shows sales performance across different time periods. All amounts are in Philippine Peso (PHP).</p>
                </body>
                </html>
            `);
            
            // Trigger print when content is loaded
            printWindow.document.close();
            printWindow.onload = function() {
                printWindow.print();
                // printWindow.close(); // Uncomment if you want the window to close after printing
            };
        });
    </script>

    <script>
    // Export customer purchases to PDF directly with jsPDF
    let currentCustomerData = [];
    let currentCustomerName = '';

    document.getElementById('exportCustomerPurchasesBtn').addEventListener('click', function() {
        try {
            // Access jsPDF from window object
            const { jsPDF } = window.jspdf;
            
            // Initialize PDF document (portrait orientation)
            const doc = new jsPDF('p', 'pt', 'a4');
            const pageWidth = doc.internal.pageSize.getWidth();
            
            // Start Y position for content
            let y = 40;
            
            // Add E-MEAT header
            doc.setFontSize(18);
            doc.setFont(undefined, 'bold');
            doc.text("E-MEAT Purchase Report", pageWidth / 2, y, { align: 'center' });
            
            // Add date
            y += 20;
            doc.setFontSize(10);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(100, 100, 100);
            doc.text("Generated on: " + new Date().toLocaleString(), pageWidth / 2, y, { align: 'center' });
            
            // Add filter period
            y += 15;
            doc.text("Period: <?= date('F d, Y', strtotime($start_date)) ?> to <?= date('F d, Y', strtotime($end_date)) ?>", pageWidth / 2, y, { align: 'center' });
            
            // Add customer name
            y += 25;
            doc.setFontSize(14);
            doc.setFont(undefined, 'bold');
            doc.setTextColor(0, 0, 0);
            doc.text("Customer: " + currentCustomerName, 40, y);
            
            // Calculate totals with proper unit handling
            let kgCount = 0;
            let gramsCount = 0;
            let pcsCount = 0;
            let totalAmount = 0;
            let hasKg = false;
            let hasGrams = false;
            let hasPcs = false;
            
            currentCustomerData.forEach(purchase => {
                const unitLower = purchase.UNIT_OF_MEASURE.toLowerCase();
                
                // Handle different unit types
                if (unitLower === 'kg') {
                    kgCount += parseFloat(purchase.total_quantity);
                    hasKg = true;
                } else if (unitLower === 'g' || unitLower === 'grams') {
                    gramsCount += parseFloat(purchase.total_quantity);
                    hasGrams = true;
                } else if (unitLower === 'pcs' || unitLower === 'pc' || unitLower === 'piece' || unitLower === 'pieces') {
                    pcsCount += parseFloat(purchase.total_quantity);
                    hasPcs = true;
                }
                
                totalAmount += parseFloat(purchase.total_amount);
            });
            
            // Create a formatted total quantity string
            let totalQuantityString = "";
            if (hasKg) {
                totalQuantityString += kgCount.toFixed(2) + " KG";
            }
            if ((hasKg && hasGrams) || (hasKg && hasPcs)) {
                totalQuantityString += " + ";
            }
            if (hasGrams) {
                totalQuantityString += gramsCount.toFixed(2) + " g";
            }
            if ((hasGrams && hasPcs) && (hasKg || hasGrams)) {
                totalQuantityString += " + ";
            }
            if (hasPcs) {
                totalQuantityString += pcsCount.toFixed(2) + " pcs";
            }
            if (!totalQuantityString) {
                totalQuantityString = "0"; // Fallback if no quantities
            }
            
            // Prepare data for the table
            const tableData = [];
            currentCustomerData.forEach(purchase => {
                tableData.push([
                    purchase.meat_category,
                    purchase.MEAT_PART_NAME.toUpperCase(),
                    purchase.total_quantity + " " + purchase.UNIT_OF_MEASURE,
                    "PHP " + parseFloat(purchase.UNIT_PRICE).toFixed(2),
                    "PHP " + parseFloat(purchase.total_amount).toFixed(2),
                    new Date(purchase.order_date).toLocaleDateString()
                ]);
            });
            
            // Add total row with proper units
            tableData.push([
                "TOTAL",
                "",
                totalQuantityString,
                "",
                "PHP " + totalAmount.toFixed(2),
                ""
            ]);
            
            // Add the table
            doc.autoTable({
                startY: y + 10,
                head: [['Category', 'Meat Part', 'Quantity', 'Unit Price', 'Total Amount', 'Order Date']],
                body: tableData,
                theme: 'striped',
                headStyles: { 
                    fillColor: [220, 53, 69],  // Red color for header
                    textColor: 255, 
                    fontStyle: 'bold'
                },
                footStyles: { 
                    fillColor: [240, 240, 240], 
                    textColor: [0, 0, 0], 
                    fontStyle: 'bold'
                },
                columnStyles: {
                    0: { cellWidth: 70 },  // Category
                    1: { cellWidth: 90 },  // Meat Part
                    2: { cellWidth: 70 },  // Quantity
                    3: { cellWidth: 70, halign: 'right' },  // Unit Price
                    4: { cellWidth: 70, halign: 'right' },  // Total Amount
                    5: { cellWidth: 80 }   // Order Date
                },
                margin: { top: 10, right: 40, bottom: 60, left: 40 },
                didDrawPage: function(data) {
                    // Footer
                    doc.setFontSize(8);
                    doc.setTextColor(100);
                    doc.text(
                        "Page " + data.pageNumber, 
                        pageWidth / 2, 
                        doc.internal.pageSize.getHeight() - 10, 
                        { align: 'center' }
                    );
                }
            });
            
            // Add note at the bottom
            const finalY = doc.lastAutoTable.finalY || 150;
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text(
                "This report shows all purchases made by " + currentCustomerName + " between " + 
                "<?= date('F d, Y', strtotime($start_date)) ?> and <?= date('F d, Y', strtotime($end_date)) ?>. " +
                "All amounts are in Philippine Peso (PHP).",
                40, 
                finalY + 20
            );
            
            // Save the PDF
            doc.save("E-MEAT_" + currentCustomerName.replace(/\s+/g, '_') + "_Purchases.pdf");
            
        } catch (error) {
            console.error("Error generating PDF:", error);
            alert("There was an error generating the PDF. Please try again.");
            
            // Fallback to the window print method if jsPDF fails
            const printWindow = window.open('', '_blank');
            
            // Calculate totals with proper unit handling
            let kgCount = 0;
            let gramsCount = 0;
            let pcsCount = 0;
            let totalAmount = 0;
            let hasKg = false;
            let hasGrams = false;
            let hasPcs = false;
            
            currentCustomerData.forEach(purchase => {
                const unitLower = purchase.UNIT_OF_MEASURE.toLowerCase();
                
                // Handle different unit types
                if (unitLower === 'kg') {
                    kgCount += parseFloat(purchase.total_quantity);
                    hasKg = true;
                } else if (unitLower === 'g' || unitLower === 'grams') {
                    gramsCount += parseFloat(purchase.total_quantity);
                    hasGrams = true;
                } else if (unitLower === 'pcs' || unitLower === 'pc' || unitLower === 'piece' || unitLower === 'pieces') {
                    pcsCount += parseFloat(purchase.total_quantity);
                    hasPcs = true;
                }
                
                totalAmount += parseFloat(purchase.total_amount);
            });
            
            // Create a formatted total quantity string
            let totalQuantityString = "";
            if (hasKg) {
                totalQuantityString += kgCount.toFixed(2) + " KG";
            }
            if ((hasKg && hasGrams) || (hasKg && hasPcs)) {
                totalQuantityString += " + ";
            }
            if (hasGrams) {
                totalQuantityString += gramsCount.toFixed(2) + " g";
            }
            if ((hasGrams && hasPcs) && (hasKg || hasGrams)) {
                totalQuantityString += " + ";
            }
            if (hasPcs) {
                totalQuantityString += pcsCount.toFixed(2) + " pcs";
            }
            if (!totalQuantityString) {
                totalQuantityString = "0"; // Fallback if no quantities
            }
            
            // Create table rows
            let purchaseRows = '';
            currentCustomerData.forEach(purchase => {
                purchaseRows += `
                    <tr>
                        <td>${purchase.meat_category}</td>
                        <td>${purchase.MEAT_PART_NAME.toUpperCase()}</td>
                        <td>${purchase.total_quantity} ${purchase.UNIT_OF_MEASURE}</td>
                        <td class="amount">PHP ${parseFloat(purchase.UNIT_PRICE).toFixed(2)}</td>
                        <td class="amount">PHP ${parseFloat(purchase.total_amount).toFixed(2)}</td>
                        <td>${new Date(purchase.order_date).toLocaleDateString()}</td>
                    </tr>
                `;
            });
            
            // Create the print content
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Customer Purchase Report - ${currentCustomerName}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 40px; }
                        h1 { color: #333; margin-bottom: 10px; }
                        h2 { color: #555; margin-top: 20px; }
                        .report-date { color: #777; margin-bottom: 30px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
                        th { background-color: #f8f8f8; font-weight: bold; }
                        .amount { text-align: right; }
                        .total-row { font-weight: bold; background-color: #f0f0f0; }
                        .note { font-size: 12px; color: #777; font-style: italic; }
                    </style>
                </head>
                <body>
                    <h1>E-MEAT Purchase Report</h1>
                    <p class="report-date">Generated on: ${new Date().toLocaleString()}</p>
                    <p class="report-date">Period: <?= date('F d, Y', strtotime($start_date)) ?> to <?= date('F d, Y', strtotime($end_date)) ?></p>
                    
                    <h2>Customer: ${currentCustomerName}</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Meat Category</th>
                                <th>Meat Part</th>
                                <th>Quantity</th>
                                <th class="amount">Unit Price</th>
                                <th class="amount">Total Amount</th>
                                <th>Order Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${purchaseRows}
                            <tr class="total-row">
                                <td colspan="2">TOTAL</td>
                                <td>${totalQuantityString}</td>
                                <td></td>
                                <td class="amount">PHP ${totalAmount.toFixed(2)}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <p class="note">This report shows all purchases made by ${currentCustomerName} between <?= date('F d, Y', strtotime($start_date)) ?> and <?= date('F d, Y', strtotime($end_date)) ?>. All amounts are in Philippine Peso (PHP).</p>
                </body>
                </html>
            `);
            
            // Trigger print when content is loaded
            printWindow.document.close();
            printWindow.onload = function() {
                printWindow.print();
            };
        }
    });
    </script>
</body>
</html>