<?php
session_start(); // Start the session

// To match what login.php is setting:
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    $_SESSION['message'] = "You need to log in as admin to access this page";
    header("Location: ../users/login.php");
    exit();
}

$page_title = "Order Management | E-MEAT Admin";
include('new_include/sidebar.php');
include '../connection/config.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    die("Access Denied. Only admins can access this page.");
}

// Flash message handling
$successMessage = "";
$errorMessage = "";

if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear the message after displaying it once
}

if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear the message after displaying it once
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $new_status = isset($_POST['status']) ? intval($_POST['status']) : 0;
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];

    // Debugging log
    error_log("Updating Order ID: " . $order_id . ", Status: " . $new_status . ", Admin ID: " . $user_id);

    // Call stored procedure
    $update_sql = "CALL sp_update_order_status(?, ?, ?, ?)";
    $stmt = $conn->prepare($update_sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("iiis", $order_id, $new_status, $user_id, $user_type);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order status updated successfully."; // Store in session
        // Instead of header redirect, use JavaScript
        echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
        exit;
    } else {
        $_SESSION['error_message'] = "Error updating status: " . $stmt->error; // Store in session
        // Instead of header redirect, use JavaScript
        echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
        exit;
    }
    $stmt->close();
}

// In your rider assignment handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_rider'])) {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;
    
    if ($order_id && $rider_id) {
        // Call stored procedure with output parameters
        $rider_sql = "CALL sp_update_rider_assigned(?, ?, @success, @message)";
        $rider_stmt = $conn->prepare($rider_sql);
        $rider_stmt->bind_param("ii", $rider_id, $order_id);
        $rider_stmt->execute();
        
        // Get output parameters
        $result = $conn->query("SELECT @success as success, @message as message");
        $output = $result->fetch_assoc();
        
        if ($output['success']) {
            $_SESSION['success_message'] = $output['message'];
        } else {
            $_SESSION['error_message'] = $output['message'];
        }
        
        echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
        exit;
    }
}

// Fetch all riders ONLY (not orders or status)
$riders_query = "CALL GetAllRiders()";
$riders = $conn->query($riders_query);
if ($riders === false) {
    die("Error fetching riders: " . $conn->error);
}
$conn->next_result(); // Move to the next result set, if any

// Fetch all orders using stored procedure
$sql = "CALL GetAllOrders()";
$result = $conn->query($sql);
if ($result === false) {
    die("Error fetching orders: " . $conn->error);
}
$conn->next_result(); // Move to the next result set, if any

// Fetch all status options using stored procedure
$status_query = "CALL GetAllStatusOptions()";
$status_result = $conn->query($status_query);
if ($status_result === false) {
    die("Error fetching status options: " . $conn->error);
}

$status_options = [];
while ($status = $status_result->fetch_assoc()) {
    $status_options[$status['STAT_ID']] = $status['STATUS_NAME'];
}
$conn->next_result(); // Move to the next result set, if any
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        
        /* Custom scrollbar for tables */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
        
        /* Status badge styling */
        .status-badge {
            min-width: 100px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            padding: 0.375rem 0.625rem;
        }
        
        /* Timeline styling */
        .timeline-track {
            height: 2px;
            background: #e5e7eb;
            position: relative;
        }
        
        .timeline-progress {
            height: 100%;
            background: #10b981;
            position: absolute;
            left: 0;
            top: 0;
        }
        
        .timeline-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid white;
        }
        
        .timeline-dot.completed {
            background: #10b981;
        }
        
        .timeline-dot.current {
            background: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
        
        .timeline-dot.pending {
            background: #e5e7eb;
        }
        
        /* Switch toggle styling */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #68D391;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #68D391;
        }
        
        /* Card hover effect */
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* Animation for row hover */
        tr.order-row {
            transition: all 0.2s ease-in-out;
        }
        
        tr.order-row:hover td {
            background-color: #f3f4f6;
        }
        
        /* Disable rider selection when already assigned */
        select:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            background-color: #f3f4f6;
        }
        
        .rider-assigned {
            transition: all 0.2s ease;
        }
        
        /* Make modal appear with animation */
        .modal {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .modal.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            transform: scale(0.95);
            transition: transform 0.3s;
        }
        
        .modal.show .modal-content {
            transform: scale(1);
        }

        /* Animation for rider form */
        .rider-form {
            transition: all 0.3s ease-in-out;
        }

        .fadeIn {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(-10px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .table-fixed-height {
            max-height: 570px; /* Adjust this value based on your row height */
            overflow-y: auto;
        }
    </style>
</head>
<body class="text-gray-800">
    <!-- Main Content Wrapper - position it to the right of the sidebar -->
    <div class="pl-0 lg:pl-64 transition-all duration-300">
        <!-- Page Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
            <!-- SweetAlert2 Notifications -->
            <script>
            <?php if ($successMessage): ?>
                Swal.fire({
                    title: "Success!",
                    text: "<?= $successMessage ?>",
                    icon: "success",
                    position: 'top-end',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#10b981',
                    color: '#ffffff'
                });
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                Swal.fire({
                    title: "Error!",
                    text: "<?= $errorMessage ?>",
                    icon: "error",
                    position: 'top-end',
                    toast: true,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    background: '#ef4444',
                    color: '#ffffff'
                });
            <?php endif; ?>
            </script>
            
            <!-- Dashboard Header -->
            <div class="mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">Order Management</h1>
                        <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded-full flex items-center">
                            <span class="w-2 h-2 bg-red-500 rounded-full mr-1"></span>
                            Live
                        </span>
                    </div>
                    <p class="text-gray-600 mt-1 text-sm">View and manage customer orders</p>
                </div>
                
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <!-- Search box -->
                    <div class="relative flex-1 md:w-64">
                        <input type="text" id="orderSearch" placeholder="Search customer..." 
                            class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none bg-white shadow-sm">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                    
                    <!-- Filter dropdown -->
                    <div class="relative">
                        <button id="filterBtn" class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 shadow-sm">
                            <i class="fas fa-filter text-gray-400"></i>
                            <span>Filter</span>
                        </button>
                        <div id="filterDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg z-10 hidden border border-gray-200">
                            <div class="p-2 border-b border-gray-100">
                                <p class="text-xs font-semibold text-gray-500 uppercase">Order Status</p>
                            </div>
                            <div class="p-2 space-y-1">
                                <button class="status-filter active w-full text-left px-3 py-2 rounded text-sm hover:bg-gray-100"
                                        data-status="all">
                                    <span class="flex items-center">
                                        <span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span>
                                        All Orders
                                    </span>
                                </button>
                                <?php foreach ($status_options as $stat_id => $status_name): ?>
                                    <button class="status-filter w-full text-left px-3 py-2 rounded text-sm hover:bg-gray-100"
                                            data-status="<?= $stat_id ?>">
                                        <span class="flex items-center">
                                            <span class="w-2 h-2 rounded-full 
                                            <?php 
                                                if ($stat_id == 1) echo 'bg-yellow-500';      // Pending
                                                else if ($stat_id == 2) echo 'bg-blue-500';   // Processing
                                                else if ($stat_id == 3) echo 'bg-indigo-500'; // In Transit
                                                else if ($stat_id == 4) echo 'bg-green-500';  // Delivered
                                                else if ($stat_id == 5) echo 'bg-emerald-500';  // Received
                                                else echo 'bg-gray-500';                      // Any other status
                                            ?> mr-2"></span>
                                            <?= $status_name ?>
                                        </span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Status Cards (Mobile View) -->
            <div class="grid grid-cols-1 gap-4 mb-6 md:hidden">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $result->data_seek(0); // Reset pointer ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden order-row order-card transition-all duration-200" 
                             data-status="<?= $row['STAT_ID'] ?>">
                             
                            <div class="p-4 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-800 font-medium">
                                            <?= strtoupper(substr($row['USER_FNAME'], 0, 1) . substr($row['USER_LNAME'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900"><?= htmlspecialchars($row['USER_FNAME'] . " " . $row['USER_LNAME']) ?></h3>
                                            <div class="text-xs text-gray-500">Order #<?= $row['ORDERS_ID'] ?></div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-green-600">₱<?= number_format($row['TOTAL_AMOUNT'], 2) ?></div>
                                        <div class="text-xs text-gray-500">
                                            <?php 
                                                $date = new DateTime($row['ORDERS_DATE']); 
                                                echo $date->format('M d, Y'); 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-4">
                                <!-- Status Badge -->
                                <?php
                                    $badgeClass = 'bg-blue-100 text-blue-800'; // Default
                                    $badgeIcon = 'fa-spinner';
                                    if ($row['STAT_ID'] == 1) { // Pending
                                        $badgeClass = 'bg-yellow-100 text-yellow-800';
                                        $badgeIcon = 'fa-clock';
                                    } else if ($row['STAT_ID'] == 2) { // Processing
                                        $badgeClass = 'bg-blue-100 text-blue-800';
                                        $badgeIcon = 'fa-cog';
                                    } else if ($row['STAT_ID'] == 3) { // In Transit
                                        $badgeClass = 'bg-indigo-100 text-indigo-800';
                                        $badgeIcon = 'fa-truck-fast';
                                    } else if ($row['STAT_ID'] == 4) { // Delivered
                                        $badgeClass = 'bg-green-100 text-green-800';
                                        $badgeIcon = 'fa-check';
                                    } else if ($row['STAT_ID'] == 5) { // Received
                                        $badgeClass = 'bg-emerald-100 text-emerald-800'; // Changed to emerald for Received
                                        $badgeIcon = 'fa-circle-check'; // Changed icon to circle-check
                                    }
                                ?>
                                <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-medium rounded-full <?= $badgeClass ?>">
                                    <i class="fas <?= $badgeIcon ?> mr-1.5"></i>
                                    <?= htmlspecialchars($row['STATUS_NAME']) ?>
                                </span>
                                
                                <!-- Order Timeline (simple version for mobile) -->
                                <div class="my-4 relative">
                                    <div class="timeline-track">
                                        <div class="timeline-progress" style="width: <?= min(100, ($row['STAT_ID'] / 4) * 100) ?>%;"></div>
                                        
                                        <?php for($i = 1; $i <= 4; $i++): ?>
                                            <?php 
                                                $dotClass = 'timeline-dot ';
                                                $dotClass .= $row['STAT_ID'] > $i ? 'completed' : 
                                                           ($row['STAT_ID'] == $i ? 'current' : 'pending');
                                            ?>
                                            <div class="<?= $dotClass ?>" style="left: <?= (($i-1) / 3) * 100 ?>%;"></div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <!-- Collapsible Status Update Form -->
                                <div class="mt-4 border-t border-gray-100 pt-4">
                                    <div class="flex flex-col gap-3">
                                        <form method="post" class="status-form">
                                            <input type="hidden" name="order_id" value="<?= $row['ORDERS_ID'] ?>">
                                            <div class="flex gap-2">
                                                <select name="status" class="status-select text-sm border border-gray-300 rounded-lg px-3 py-2 flex-1 focus:ring-2 focus:ring-red-500 focus:outline-none <?= ($row['STAT_ID'] == 5) ? 'bg-gray-100 cursor-not-allowed' : '' ?>" data-original-status="<?= $row['STAT_ID'] ?>" <?= ($row['STAT_ID'] == 5) ? 'disabled' : '' ?>>
                                                    <?php foreach ($status_options as $stat_id => $status_name): ?>
                                                        <option value="<?= $stat_id ?>" <?= ($row['STAT_ID'] == $stat_id) ? 'selected' : '' ?>>
                                                            <?= $status_name ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" name="update_status" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition-colors flex items-center gap-1 <?= ($row['STAT_ID'] == 5) ? 'bg-gray-400 hover:bg-gray-400 cursor-not-allowed' : '' ?>" <?= ($row['STAT_ID'] == 5) ? 'disabled' : '' ?>>
                                                    <i class="fas fa-save"></i> Update
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <!-- Rider Assignment (hidden when pending, disabled when assigned) -->
                                        <?php if (empty($row['rider_name'])): ?>
                                            <form method="post" class="rider-form mt-2" <?= ($row['STAT_ID'] != 2) ? 'style="display:none;"' : '' ?>>
                                                <input type="hidden" name="order_id" value="<?= $row['ORDERS_ID'] ?>">
                                                <div class="flex gap-2">
                                                    <select name="rider_id" class="text-sm border border-gray-300 rounded-lg px-3 py-2 flex-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                                        <option value="">Select Rider</option>
                                                        <?php if($riders): $riders->data_seek(0); ?>
                                                            <?php while ($rider = $riders->fetch_assoc()): ?>
                                                                <option value="<?= $rider['rider_id'] ?>">
                                                                    <?= htmlspecialchars($rider['rider_name']) ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                    <button type="submit" name="assign_rider" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition-colors flex items-center gap-1">
                                                        <i class="fas fa-motorcycle"></i> Assign
                                                    </button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <!-- Display rider info only in mobile view since there's no separate column -->
                                            <div class="rider-assigned flex items-center mt-2 bg-blue-50 px-3 py-2 rounded-lg border border-blue-100">
                                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3">
                                                    <i class="fas fa-motorcycle"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-sm"><?= htmlspecialchars($row['rider_name']) ?></p>
                                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($row['rider_contact']) ?></p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-box text-gray-200 text-6xl mb-4"></i>
                            <h3 class="text-gray-600 font-medium mb-1">No Orders Found</h3>
                            <p class="text-gray-400 text-sm">New orders will appear here once customers place them</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Orders Table (Desktop View) -->
            <div class="hidden md:block bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto scrollbar-thin table-fixed-height">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order Date
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Last Update
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rider
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $result->data_seek(0); // Reset pointer to beginning ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition-all order-row" data-status="<?= $row['STAT_ID'] ?>">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-800 font-medium">
                                                    <?= strtoupper(substr($row['USER_FNAME'], 0, 1) . substr($row['USER_LNAME'], 0, 1)) ?>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['USER_FNAME'] . " " . $row['USER_LNAME']) ?></div>
                                                    <div class="text-xs text-gray-500">Order #<?= $row['ORDERS_ID'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-green-600">₱<?= number_format($row['TOTAL_AMOUNT'], 2) ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-700">
                                                <?php 
                                                    $date = new DateTime($row['ORDERS_DATE']); 
                                                    echo $date->format('M d, Y'); 
                                                ?>
                                            </div>
                                            <div class="text-xs text-gray-500"><?= $date->format('h:i A'); ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php
                                                $badgeClass = 'bg-blue-100 text-blue-800'; // Default
                                                $badgeIcon = 'fa-spinner';
                                                if ($row['STAT_ID'] == 1) { // Pending
                                                    $badgeClass = 'bg-yellow-100 text-yellow-800';
                                                    $badgeIcon = 'fa-clock';
                                                } else if ($row['STAT_ID'] == 2) { // Processing
                                                    $badgeClass = 'bg-blue-100 text-blue-800';
                                                    $badgeIcon = 'fa-cog';
                                                } else if ($row['STAT_ID'] == 3) { // In Transit
                                                    $badgeClass = 'bg-indigo-100 text-indigo-800';
                                                    $badgeIcon = 'fa-truck-fast';
                                                } else if ($row['STAT_ID'] == 4) { // Delivered
                                                    $badgeClass = 'bg-green-100 text-green-800';
                                                    $badgeIcon = 'fa-check';
                                                } else if ($row['STAT_ID'] == 5) { // Received (changed from Cancelled)
                                                    $badgeClass = 'bg-emerald-100 text-emerald-800'; // Changed to emerald for Received
                                                    $badgeIcon = 'fa-circle-check'; // Changed icon to circle-check
                                                }
                                            ?>
                                            <div class="mb-2">
                                                <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-full <?= $badgeClass ?>">
                                                    <i class="fas <?= $badgeIcon ?> mr-1.5"></i>
                                                    <?= htmlspecialchars($row['STATUS_NAME']) ?>
                                                </span>
                                            </div>
                                            
                                            <!-- Order Timeline -->
                                            <div class="mt-3 relative">
                                                <div class="timeline-track">
                                                    <div class="timeline-progress" style="width: <?= min(100, ($row['STAT_ID'] / 4) * 100) ?>%;"></div>
                                                    
                                                    <?php for($i = 1; $i <= 4; $i++): ?>
                                                        <?php 
                                                            $dotClass = 'timeline-dot ';
                                                            $dotClass .= $row['STAT_ID'] > $i ? 'completed' : 
                                                                      ($row['STAT_ID'] == $i ? 'current' : 'pending');
                                                        ?>
                                                        <div class="<?= $dotClass ?>" style="left: <?= (($i-1) / 3) * 100 ?>%;"></div>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($row['LAST_UPDATE']): ?>
                                                <div class="text-sm text-gray-700">
                                                    <?php 
                                                        $updateDate = new DateTime($row['LAST_UPDATE']); 
                                                        echo $updateDate->format('M d, Y'); 
                                                    ?>
                                                </div>
                                                <div class="text-xs text-gray-500"><?= $updateDate->format('h:i A'); ?></div>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400 italic">Not updated</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if (!empty($row['rider_name'])): ?>
                                                <div class="rider-assigned flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-2">
                                                        <i class="fas fa-motorcycle"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium"><?= htmlspecialchars($row['rider_name']) ?></div>
                                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($row['rider_contact']) ?></div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                                    <i class="fas fa-circle-exclamation"></i> No rider assigned
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col gap-2">
                                                <!-- Status Update Form -->
                                                <form method="post" class="status-form">
                                                    <input type="hidden" name="order_id" value="<?= $row['ORDERS_ID'] ?>">
                                                    <div class="flex items-center space-x-2">
                                                        <select name="status" class="status-select text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-red-500 focus:outline-none flex-grow <?= ($row['STAT_ID'] == 5) ? 'bg-gray-100 cursor-not-allowed' : '' ?>" data-original-status="<?= $row['STAT_ID'] ?>" <?= ($row['STAT_ID'] == 5) ? 'disabled' : '' ?>>
                                                            <?php foreach ($status_options as $stat_id => $status_name): ?>
                                                                <option value="<?= $stat_id ?>" <?= ($row['STAT_ID'] == $stat_id) ? 'selected' : '' ?>>
                                                                    <?= $status_name ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" name="update_status" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap <?= ($row['STAT_ID'] == 5) ? 'bg-gray-400 hover:bg-gray-400 cursor-not-allowed' : '' ?>" <?= ($row['STAT_ID'] == 5) ? 'disabled' : '' ?>>
                                                            Update
                                                        </button>
                                                    </div>
                                                </form>

                                                <!-- Rider Assignment Form (hidden when pending, disabled when assigned) -->
                                                <?php if (empty($row['rider_name'])): ?>
                                                    <form method="post" class="rider-form" <?= ($row['STAT_ID'] != 2) ? 'style="display:none;"' : '' ?>>
                                                        <input type="hidden" name="order_id" value="<?= $row['ORDERS_ID'] ?>">
                                                        <div class="flex items-center space-x-2">
                                                            <select name="rider_id" class="text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none flex-grow">
                                                                <option value="">Select Rider</option>
                                                                <?php if($riders): $riders->data_seek(0); ?>
                                                                    <?php while ($rider = $riders->fetch_assoc()): ?>
                                                                        <option value="<?= $rider['rider_id'] ?>">
                                                                            <?= htmlspecialchars($rider['rider_name']) ?>
                                                                        </option>
                                                                    <?php endwhile; ?>
                                                                <?php endif; ?>
                                                            </select>
                                                            <button type="submit" name="assign_rider" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap">
                                                                Assign
                                                            </button>
                                                        </div>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-box text-gray-200 text-6xl mb-4"></i>
                                            <h3 class="text-gray-600 font-medium mb-1">No Orders Found</h3>
                                            <p class="text-gray-400 text-sm">New orders will appear here once customers place them</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
<script>
    $(document).ready(function() {
        // Initially hide rider assignment for pending orders
        $('.order-row').each(function() {
            var statusId = $(this).data('status');
            
            // Hide rider form for all statuses except Processing (2)
            if (statusId != 2) {
                $(this).find('.rider-form').hide();
            }
        });
        
        // Status change handler - only to show/hide rider form
        $('.status-select').on('change', function() {
            var currentStatusId = parseInt($(this).val());
            var originalStatusId = parseInt($(this).data('original-status'));
            var orderRow = $(this).closest('.order-row');
            
            // Show rider assignment ONLY when status is "Processing" (ID 2)
            if (currentStatusId == 2) {
                // Show the rider assignment form with animation
                orderRow.find('.rider-form').fadeIn(300).addClass('fadeIn');
                
                // Only show notification if this is a new change to Processing
                if (originalStatusId != 2) {
                    Swal.fire({
                        title: "Ready for Rider Assignment",
                        text: "Now you can assign a rider to this order",
                        icon: "info",
                        position: 'top-end',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            } 
            // Hide the rider form if changing to anything other than Processing
            else {
                orderRow.find('.rider-form').fadeOut(200);
            }
        });
        
        // Toggle filter dropdown
        $('#filterBtn').on('click', function(e) {
            e.stopPropagation();
            $('#filterDropdown').toggleClass('hidden');
        });
        
        // Hide filter dropdown when clicking elsewhere
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#filterBtn').length && !$(e.target).closest('#filterDropdown').length) {
                $('#filterDropdown').addClass('hidden');
            }
        });
        
        // Search functionality - search by customer name
        $('#orderSearch').on('keyup', function() {
            var searchText = $(this).val().toLowerCase();
            $('.order-row').each(function() {
                var customerName = $(this).find('td:nth-child(1)').text().toLowerCase();
                
                if (customerName.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // For mobile view
            $('.order-card').each(function() {
                var customerName = $(this).find('h3').text().toLowerCase();
                
                if (customerName.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Status filter functionality
        $('.status-filter').on('click', function() {
            // Toggle active state
            $('.status-filter').removeClass('active bg-red-600 text-white').addClass('hover:bg-gray-100');
            $(this).removeClass('hover:bg-gray-100').addClass('active bg-red-600 text-white');
            $('#filterDropdown').addClass('hidden');
            
            var status = $(this).data('status');
            
            if (status === 'all') {
                $('.order-row, .order-card').show();
            } else {
                $('.order-row, .order-card').each(function() {
                    if ($(this).data('status') == status) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
        
        // Use regular confirmation for status update
        $('.status-form').on('submit', function(e) {
            var form = $(this);
            var currentStatusId = parseInt(form.find('select').val());
            var currentStatus = form.find('select option:selected').text();
            var orderId = form.find('input[name="order_id"]').val();
            
            // If button is already disabled, prevent submission
            if (form.find('button[name="update_status"]').prop('disabled')) {
                e.preventDefault();
                return false;
            }
            
            // Use standard confirm dialog
            if (!confirm("Are you sure you want to update order #" + orderId + " to " + currentStatus + " status?")) {
                e.preventDefault();
                return false;
            }
        });

        // Use standard confirmation for rider assignment
        $('.rider-form').on('submit', function(e) {
            var form = $(this);
            var selectElement = form.find('select');
            var selectedOption = selectElement.find('option:selected');
            var selectedValue = selectedOption.val();
            var selectedRider = selectedOption.text();
            var orderId = form.find('input[name="order_id"]').val();
            
            // Check if a rider is selected
            if (!selectedValue || selectedValue === '') {
                alert("Please select a rider first");
                e.preventDefault();
                return false;
            }
            
            // Use standard confirm dialog
            if (!confirm("Are you sure you want to assign " + selectedRider + " to order #" + orderId + "?")) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
</body>
</html>

<?php $conn->close(); ?>