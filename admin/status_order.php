<?php
session_start();


include('includes/header.php');
include '../config.php'; // Database connection

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    die("Access Denied. Only admins can access this page.");
}

// Handle status update
$successMessage = "";
$errorMessage = "";

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
        $successMessage = "Order status updated successfully.";
    } else {
        $errorMessage = "Error updating status: " . $stmt->error;
    }
    $stmt->close();
}

// ADD RIDER ASSIGNMENT HANDLER HERE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_rider'])) {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;
    
    // Debugging log
    error_log("Assigning Rider to Order ID: " . $order_id . ", Rider ID: " . $rider_id);

    if ($order_id && $rider_id) {
        // Call stored procedure to update the order with the assigned rider
        $rider_sql = "CALL sp_update_rider_assigned(?, ?)";
        $rider_stmt = $conn->prepare($rider_sql);
        if ($rider_stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $rider_stmt->bind_param("ii", $rider_id, $order_id);

        if ($rider_stmt->execute()) {
            $successMessage = "Rider assigned successfully.";
        } else {
            $errorMessage = "Error assigning rider: " . $rider_stmt->error;
        }
        $rider_stmt->close();
    } else {
        $errorMessage = "Invalid order or rider selection.";
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
    <title>Order Management</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
        }
        
        /* Custom scrollbar for tables */
        .scrollbar-thin::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 20px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
        
        /* Status badge styling */
        .status-badge {
            min-width: 100px;
            display: inline-block;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="max-w-7xl mx-auto px-4 py-8">
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
        
        <!-- Dashboard Header with Search -->
        <div class="mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Order Management</h1>
                <p class="text-gray-500 mt-1">Manage and update customer orders status</p>
            </div>
            
            <!-- Search positioned at top right -->
            <div class="relative w-full md:w-64">
                <input type="text" id="orderSearch" placeholder="Search by customer name..." 
                    class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none bg-white">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="mb-6">
            <div class="flex flex-wrap gap-2">
                <button class="status-filter active px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-all"
                        data-status="all">
                    All Orders
                </button>
                <?php foreach ($status_options as $stat_id => $status_name): ?>
                    <button class="status-filter px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-all"
                            data-status="<?= $stat_id ?>">
                        <?= $status_name ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Orders Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
            <div class="overflow-x-auto scrollbar-thin">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Modified By
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Last Update
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Assigned Rider
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-all order-row" data-status="<?= $row['STAT_ID'] ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-800">
                                                <?= strtoupper(substr($row['USER_FNAME'], 0, 1) . substr($row['USER_LNAME'], 0, 1)) ?>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['USER_FNAME'] . " " . $row['USER_LNAME']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-green-600">₱<?= number_format($row['TOTAL_AMOUNT'], 2) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-700">
                                            <?php 
                                                $date = new DateTime($row['ORDERS_DATE']); 
                                                echo $date->format('M d, Y'); 
                                            ?>
                                        </div>
                                        <div class="text-xs text-gray-500"><?= $date->format('h:i A'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                            // Determine badge color based on status
                                            $badgeClass = 'bg-blue-100 text-blue-800'; // Default
                                            if ($row['STAT_ID'] == 1) { // Pending
                                                $badgeClass = 'bg-yellow-100 text-yellow-800';
                                            } else if ($row['STAT_ID'] == 2) { // Processing
                                                $badgeClass = 'bg-blue-100 text-blue-800';
                                            } else if ($row['STAT_ID'] == 3) { // Shipped
                                                $badgeClass = 'bg-indigo-100 text-indigo-800';
                                            } else if ($row['STAT_ID'] == 4) { // Delivered
                                                $badgeClass = 'bg-green-100 text-green-800';
                                            } else if ($row['STAT_ID'] == 5) { // Cancelled
                                                $badgeClass = 'bg-red-100 text-red-800';
                                            }
                                        ?>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full status-badge <?= $badgeClass ?>">
                                            <?= htmlspecialchars($row['STATUS_NAME']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($row['MODIFIED_BY']): ?>
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($row['ADMIN_FNAME'] . " " . $row['ADMIN_LNAME']) ?></div>
                                            <div class="text-xs text-gray-500">Admin</div>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-500">Not updated yet</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($row['LAST_UPDATE']): ?>
                                            <div class="text-sm text-gray-700">
                                                <?php 
                                                    $updateDate = new DateTime($row['LAST_UPDATE']); 
                                                    echo $updateDate->format('M d, Y'); 
                                                ?>
                                            </div>
                                            <div class="text-xs text-gray-500"><?= $updateDate->format('h:i A'); ?></div>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($row['rider_name'])): ?>
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($row['rider_name']) ?></div>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($row['rider_contact']) ?></div>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-500">No rider assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="post" class="status-form">
                                            <input type="hidden" name="order_id" value="<?= $row['ORDERS_ID'] ?>">
                                            <div class="flex items-center justify-center space-x-2">
                                                <select name="status" class="text-sm border border-gray-300 rounded-lg px-2 py-1 focus:ring-2 focus:ring-red-500 focus:outline-none">
                                                    <?php foreach ($status_options as $stat_id => $status_name): ?>
                                                        <option value="<?= $stat_id ?>" <?= ($row['STAT_ID'] == $stat_id) ? 'selected' : '' ?>>
                                                            <?= $status_name ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" name="update_status" class="bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-1 rounded-lg transition-colors">
                                                    Update
                                                </button>
                                            </div>
                                        </form>

                                        <!-- Add Rider assignment form -->
                                        <form method="post" class="rider-form mt-2 md:mt-0">
                                            <input type="hidden" name="order_id" value="<?= $row['ORDERS_ID'] ?>">
                                            <div class="flex items-center justify-center space-x-2">
                                                <select name="rider_id" class="text-sm border border-gray-300 rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                                    <option value="">Select Rider</option>
                                                    <?php if($riders): $riders->data_seek(0); ?>
                                                        <?php while ($rider = $riders->fetch_assoc()): ?>
                                                            <option value="<?= $rider['rider_id'] ?>" <?= (isset($row['RIDER_ID']) && $row['RIDER_ID'] == $rider['rider_id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($rider['rider_name']) ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    <?php endif; ?>
                                                </select>
                                                <button type="submit" name="assign_rider" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded-lg transition-colors">
                                                    Assign
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-shopping-cart text-gray-300 text-5xl mb-4"></i>
                                        <p class="text-gray-500 mb-2">No orders found</p>
                                        <p class="text-sm text-gray-400">New orders will appear here once customers place them</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            // Search functionality
            // Search functionality
            $('#orderSearch').on('keyup', function() {
                var searchText = $(this).val().toLowerCase();
                $('.order-row').each(function() {
                    var customerName = $(this).find('td:nth-child(1)').text().toLowerCase();
                    
                    // Only search in customer name
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
                $('.status-filter').removeClass('active bg-red-600 text-white').addClass('bg-gray-200 text-gray-700');
                $(this).removeClass('bg-gray-200 text-gray-700').addClass('active bg-red-600 text-white');
                
                var status = $(this).data('status');
                
                if (status === 'all') {
                    $('.order-row').show();
                } else {
                    $('.order-row').each(function() {
                        if ($(this).data('status') == status) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            });
            
            // Add confirmation before form submission
            $('.status-form').on('submit', function(e) {
                var currentStatus = $(this).find('select option:selected').text();
                var orderId = $(this).find('input[name="order_id"]').val();
                
                if (!confirm("Are you sure you want to update order #" + orderId + " to " + currentStatus + "?")) {
                    e.preventDefault();
                }
            });

            // Add this inside your $(document).ready(function() { ... });
            // Add confirmation before rider form submission
            $('.rider-form').on('submit', function(e) {
                var selectedRider = $(this).find('select option:selected').text();
                var orderId = $(this).find('input[name="order_id"]').val();
                
                if (selectedRider === 'Select Rider') {
                    alert("Please select a rider first.");
                    e.preventDefault();
                    return false;
                }
                
                if (!confirm("Are you sure you want to assign " + selectedRider + " to order #" + orderId + "?")) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>