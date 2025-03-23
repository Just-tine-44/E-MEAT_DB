<?php
// filepath: /c:/xampp/htdocs/website/order_confirmation.php
include '../connection/config.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle order_id parameter from checkout completion
if (isset($_GET['order_id'])) {
    $_SESSION['last_order_id'] = $_GET['order_id'];
}

$user_id = $_SESSION['user_id'];

// Call the stored procedure
$stmt = $conn->prepare("CALL OrderConfirmationPHPquery(?)");
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Fetch orders
$order_result = $stmt->get_result();

// Fetch order details
$stmt->next_result();
$order_details_result = $stmt->get_result();

// Organizing order details by Order ID
$order_details = [];
while ($row = $order_details_result->fetch_assoc()) {
    $order_details[$row['ORDERS_ID']][] = $row;
}

// Define status colors for consistent usage
$status_colors = [
    'PENDING' => [
        'bg' => 'bg-amber-500',
        'light_bg' => 'bg-amber-50',
        'text' => 'text-amber-800',
        'border' => 'border-amber-200',
        'icon' => 'ri-time-line'
    ],
    'PROCESSING' => [
        'bg' => 'bg-blue-500',
        'light_bg' => 'bg-blue-50',
        'text' => 'text-blue-800',
        'border' => 'border-blue-200',
        'icon' => 'ri-settings-4-line'
    ],
    'INTRANSIT' => [
        'bg' => 'bg-indigo-500',
        'light_bg' => 'bg-indigo-50',
        'text' => 'text-indigo-800',
        'border' => 'border-indigo-200',
        'icon' => 'ri-truck-line'
    ],
    'DELIVERED' => [
        'bg' => 'bg-emerald-500',
        'light_bg' => 'bg-emerald-50',
        'text' => 'text-emerald-800',
        'border' => 'border-emerald-200',
        'icon' => 'ri-check-double-line'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | E-MEAT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../IMAGES/RED LOGO.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.5.0/remixicon.css">
    <link rel="stylesheet" href="../CCS/style.css?v=<?php echo time(); ?>">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#EF4444',
                        'primary-dark': '#DC2626'
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        /* Hide scrollbar for Chrome, Safari and Opera */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        
        /* Hide scrollbar for IE, Edge and Firefox */
        .hide-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        
        .order-card {
            transition: all 0.2s ease;
        }
        
        .order-card:hover {
            transform: translateY(-4px);
        }
        
        .progress-bar {
            transition: width 1s ease;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">
<header>
        <nav class="nav container">
            <a href="index.php" class="nav__logo">
                <img src="../IMAGES/WHITE LOGO.png" alt="Emeat Logo" class="nav__logo-img">
                EMEAT
            </a>
            <ul class="nav__menu">
                <li><a href="index.php#home">Home</a></li>
                <li><a href="index.php#feature">About</a></li>
                <li><a href="index.php#shop">Shop</a></li>
                <li><a href="index.php#contact">Contact</a></li>
            </ul>
            <div class="nav-icons">
                <a href="index.php#shop"><i class="ri-search-line"></i></a>
                <a href="cart.php"><i class="ri-shopping-cart-line"></i></a>
                <a href="receipt_orders.php" class="order-confirmation-button">
                    <i class="ri-file-list-3-line"></i> Receipt
                </a>
            </div>
        </nav>
</header>
    <main class="max-w-7xl mx-auto px-4 py-8 pb-20">
        <!-- Page Title with Order Count -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold mb-2">My Orders</h1>
            <p class="text-gray-500 text-sm">
                <?php 
                $total_orders = $order_result->num_rows;
                echo $total_orders > 0 
                    ? "You have placed $total_orders " . ($total_orders == 1 ? "order" : "orders") . " with us" 
                    : "You haven't placed any orders yet";
                ?>
            </p>
        </div>
        
        <!-- Order Status Filter Pills -->
        <?php if ($total_orders > 0): ?>
        <div class="flex items-center justify-center mb-8">
            <div class="inline-flex bg-gray-100 p-1 rounded-full">
                <button class="px-4 py-1.5 rounded-full bg-white shadow-sm text-sm font-medium">All Orders</button>
                <?php
                // Reset pointer to beginning
                $order_result->data_seek(0);
                
                // Count status numbers
                $status_counts = ['PENDING' => 0, 'PROCESSING' => 0, 'INTRANSIT' => 0, 'DELIVERED' => 0];
                while ($order = $order_result->fetch_assoc()) {
                    if (isset($status_counts[$order['STATUS_NAME']])) {
                        $status_counts[$order['STATUS_NAME']]++;
                    }
                }
                
                // Reset pointer again for the main loop
                $order_result->data_seek(0);
                
                // Only show status pills for statuses that have orders
                foreach ($status_counts as $status => $count) {
                    if ($count > 0):
                ?>
                <button class="px-4 py-1.5 rounded-full text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                    <?= ucfirst(strtolower($status)) ?> (<?= $count ?>)
                </button>
                <?php endif; } ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($order_result->num_rows > 0): ?>
        <div class="grid gap-6 md:gap-8">
            <?php while ($order = $order_result->fetch_assoc()): 
                $status = $order['STATUS_NAME'];
                $color = $status_colors[$status] ?? [
                    'bg' => 'bg-gray-500',
                    'light_bg' => 'bg-gray-50',
                    'text' => 'text-gray-800',
                    'border' => 'border-gray-200',
                    'icon' => 'ri-information-line'
                ];
                
                // Calculate progress percentage based on status
                $progress = 25; // Default is 25% for PENDING
                switch ($status) {
                    case 'PROCESSING': $progress = 50; break;
                    case 'INTRANSIT': $progress = 75; break;
                    case 'DELIVERED': $progress = 100; break;
                }
            ?>
            <div x-data="{ open: false }" class="order-card bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
                <!-- Order Header -->
                <div class="relative">
                    <!-- Status Progress Bar -->
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-100">
                        <div class="h-full <?= $color['bg'] ?> progress-bar" style="width: <?= $progress ?>%"></div>
                    </div>
                    
                    <!-- Main Header Content -->
                    <div @click="open = !open" class="p-5 cursor-pointer flex justify-between items-center">
                        <div>
                            <div class="flex items-center gap-3 mb-1.5">
                                <!-- Status Icon -->
                                <div class="<?= $color['light_bg'] ?> <?= $color['text'] ?> w-8 h-8 rounded-full flex items-center justify-center">
                                    <i class="<?= $color['icon'] ?>"></i>
                                </div>
                                
                                <!-- Order ID and Date -->
                                <div>
                                    <h3 class="font-semibold">Order #<?= htmlspecialchars($order['ORDERS_ID']) ?></h3>
                                    <p class="text-xs text-gray-500">
                                        <?= date('M j, Y · g:i A', strtotime($order['ORDERS_DATE'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <!-- Status Badge -->
                            <span class="hidden sm:flex items-center px-3 py-1 rounded-full text-xs font-medium <?= $color['light_bg'] ?> <?= $color['text'] ?>">
                                <i class="<?= $color['icon'] ?> mr-1"></i>
                                <?= ucfirst(strtolower($status)) ?>
                            </span>
                            
                            <?php 
                            // Calculate total
                            $total_amount = 0;
                            if (isset($order_details[$order['ORDERS_ID']])) {
                                foreach ($order_details[$order['ORDERS_ID']] as $detail) {
                                    $quantity = floatval($detail['QTY']);
                                    $unit_of_measure = strtolower(trim($detail['UNIT_OF_MEASURE']));
                                    $unit_price = floatval($detail['UNIT_PRICE']);
                                    
                                    if ($unit_of_measure === 'g') {
                                        $total_amount += ($unit_price * $quantity) / 1000;
                                    } else {
                                        $total_amount += $quantity * $unit_price;
                                    }
                                }
                            }
                            $total_with_shipping = $total_amount + 50;
                            ?>
                            
                            <!-- Order Total -->
                            <span class="font-medium">₱<?= number_format($total_with_shipping, 2) ?></span>
                            
                            <!-- Toggle Arrow -->
                            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 transition-transform" :class="{'rotate-180': open}">
                                <i class="ri-arrow-down-s-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Order Details (Collapsible) -->
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="border-t border-gray-100">
                    <!-- Status Timeline -->
                    <div class="p-5 pb-3">
                        <div class="flex justify-between items-center relative">
                            <?php 
                            $steps = [
                                ['status' => 'PENDING', 'label' => 'Order Received'],
                                ['status' => 'PROCESSING', 'label' => 'Processing'],
                                ['status' => 'INTRANSIT', 'label' => 'On its way'],
                                ['status' => 'DELIVERED', 'label' => 'Delivered']
                            ];
                            
                            // Horizontal line connecting the steps
                            ?>
                            <div class="absolute left-0 top-4 h-0.5 bg-gray-200 w-full -z-10"></div>
                            
                            <?php
                            $currentStatus = $order['STATUS_NAME'];
                            $active = true;
                            
                            foreach ($steps as $index => $step):
                                $isActive = $active;
                                if ($step['status'] == $currentStatus) {
                                    $active = false;
                                }
                                
                                $stepColorClass = $isActive ? $status_colors[$step['status']]['bg'] : 'bg-gray-200';
                                $textColorClass = $isActive ? 'text-gray-800' : 'text-gray-400';
                            ?>
                            <div class="flex flex-col items-center">
                                <div class="<?= $stepColorClass ?> w-8 h-8 rounded-full flex items-center justify-center text-white mb-1">
                                    <?php if($isActive): ?>
                                        <i class="ri-check-line"></i>
                                    <?php else: ?>
                                        <?= $index + 1 ?>
                                    <?php endif; ?>
                                </div>
                                <span class="text-xs font-medium <?= $textColorClass ?>"><?= $step['label'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="p-5 pt-3">
                        <h4 class="font-medium text-sm text-gray-500 mb-3">Order Items</h4>
                        
                        <!-- Desktop/Tablet View -->
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="w-full">
                                <thead class="text-xs uppercase text-gray-500 border-b border-gray-100">
                                    <tr>
                                        <th class="py-3 text-left">Item</th>
                                        <th class="py-3 text-center">Qty</th>
                                        <th class="py-3 text-right">Unit Price</th>
                                        <th class="py-3 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php
                                    if (isset($order_details[$order['ORDERS_ID']])) {
                                        foreach ($order_details[$order['ORDERS_ID']] as $detail) {
                                            $quantity = floatval($detail['QTY']);
                                            $unit_of_measure = strtolower(trim($detail['UNIT_OF_MEASURE']));
                                            $unit_price = floatval($detail['UNIT_PRICE']);
                                            
                                            // Calculate total price
                                            if ($unit_of_measure === 'g') {
                                                $total_price = ($unit_price * $quantity) / 1000;
                                            } else {
                                                $total_price = $quantity * $unit_price;
                                            }
                                    ?>
                                    <tr>
                                        <td class="py-4">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 rounded-full bg-red-50 flex-shrink-0 flex items-center justify-center text-red-500 mr-3">
                                                    <i class="ri-knife-line"></i>
                                                </div>
                                                <div>
                                                    <h5 class="font-medium"><?= htmlspecialchars($detail['MEAT_PART_NAME']) ?></h5>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 text-center">
                                            <?= number_format($quantity, $unit_of_measure === 'g' ? 0 : 1) ?> <?= strtoupper($unit_of_measure) ?>
                                        </td>
                                        <td class="py-4 text-right">₱<?= number_format($unit_price, 2) ?></td>
                                        <td class="py-4 text-right font-medium">₱<?= number_format($total_price, 2) ?></td>
                                    </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Mobile View -->
                        <div class="sm:hidden space-y-4">
                            <?php
                            if (isset($order_details[$order['ORDERS_ID']])) {
                                foreach ($order_details[$order['ORDERS_ID']] as $detail) {
                                    $quantity = floatval($detail['QTY']);
                                    $unit_of_measure = strtolower(trim($detail['UNIT_OF_MEASURE']));
                                    $unit_price = floatval($detail['UNIT_PRICE']);
                                    
                                    // Calculate total price
                                    if ($unit_of_measure === 'g') {
                                        $total_price = ($unit_price * $quantity) / 1000;
                                    } else {
                                        $total_price = $quantity * $unit_price;
                                    }
                            ?>
                            <div class="flex items-start border border-gray-100 p-3 rounded-xl">
                                <div class="w-10 h-10 rounded-full bg-red-50 flex-shrink-0 flex items-center justify-center text-red-500 mr-3">
                                    <i class="ri-knife-line"></i>
                                </div>
                                <div class="flex-grow">
                                    <h5 class="font-medium"><?= htmlspecialchars($detail['MEAT_PART_NAME']) ?></h5>
                                    <div class="text-sm text-gray-500">
                                        <?= number_format($quantity, $unit_of_measure === 'g' ? 0 : 1) ?> <?= strtoupper($unit_of_measure) ?> × ₱<?= number_format($unit_price, 2) ?>
                                    </div>
                                </div>
                                <div class="font-medium">₱<?= number_format($total_price, 2) ?></div>
                            </div>
                            <?php
                                }
                            }
                            ?>
                        </div>
                        
                        <!-- Order Summary -->
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Subtotal</span>
                                <span>₱<?= number_format($total_amount, 2) ?></span>
                            </div>
                            <div class="flex justify-between items-center text-sm mt-2">
                                <span class="text-gray-500">Shipping</span>
                                <span>₱50.00</span>
                            </div>
                            <div class="flex justify-between items-center font-medium mt-3 pt-3 border-t border-dashed border-gray-200">
                                <span>Total</span>
                                <span class="text-lg text-primary">₱<?= number_format($total_with_shipping, 2) ?></span>
                            </div>
                            
                            <!-- Payment Method -->
                            <?php if (isset($order['PAYMENT_METHOD'])): 
                                $payment = strtolower($order['PAYMENT_METHOD']);
                                $paymentIcon = $payment === 'gcash' ? 'ri-smartphone-line' : 'ri-cash-line';
                                $paymentText = $payment === 'gcash' ? 'Paid via GCash' : 'Cash on Delivery';
                                $bgColor = $payment === 'gcash' ? 'bg-blue-50 text-blue-800' : 'bg-amber-50 text-amber-800';
                            ?>
                            <div class="mt-4 flex items-center gap-2 text-sm">
                                <span class="<?= $bgColor ?> rounded-full px-3 py-1 font-medium flex items-center gap-1">
                                    <i class="<?= $paymentIcon ?>"></i>
                                    <?= $paymentText ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="p-5 border-t border-gray-100 flex flex-wrap gap-2">
                        <a href="receipt_orders.php?order_id=<?= $order['ORDERS_ID'] ?>" class="flex-1 bg-primary text-white py-2.5 px-4 rounded-lg text-center text-sm font-medium hover:bg-primary-dark transition-colors">
                            <i class="ri-file-list-3-line mr-1"></i> View Receipt
                        </a>
                        
                        <a href="#" class="flex items-center justify-center min-w-[40px] px-2 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="ri-question-line"></i>
                            <span class="ml-1 hidden md:inline">Get Help</span>
                        </a>
                        
                        <?php if ($status === 'DELIVERED'): ?>
                        <a href="#" class="hidden sm:flex items-center justify-center px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="ri-star-line mr-1"></i>
                            <span>Write a Review</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <?php else: ?>
        <!-- Empty State - No Orders -->
        <div class="text-center py-16 px-4">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="ri-shopping-bag-3-line text-4xl text-gray-400"></i>
            </div>
            <h2 class="text-2xl font-medium text-gray-800 mb-2">No orders yet</h2>
            <p class="text-gray-500 max-w-md mx-auto mb-8">
                You haven't placed any orders with us yet. Start shopping to enjoy our premium meat selections!
            </p>
            <a href="index.php#shop" class="inline-block bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-lg font-medium transition-colors">
                Browse Products
            </a>
        </div>
        <?php endif; ?>
    </main>
    <script>
        document.addEventListener('alpine:init', () => {
            // Any additional functionality can go here
        });
    </script>
</body>
</html>