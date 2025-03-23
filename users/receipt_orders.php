<?php
// filepath: /c:/xampp/htdocs/website/receipt_orders.php
    include '../connection/config.php';
    session_start();
    
    // Enable error reporting
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $order_id = $_GET['order_id'] ?? null; // If null, show all orders
    
    // Use stored procedure to get orders
    $query = "CALL GetOrdersReceipt(?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $user_id, $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    if ($order_result === false) {
        die("Execute failed: " . $stmt->error);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipts | E-MEAT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../IMAGES/RED LOGO.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.5.0/remixicon.css">
    <link rel="stylesheet" href="../CCS/style.css?v=<?php echo time(); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #f9fafb;
        }
        .receipt-card {
            transition: all 0.3s ease;
        }
        .receipt-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .receipt-for-print {
                break-inside: avoid;
                padding: 20px;
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="no-print">
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
                <a href="order_confirmation.php" class="order-confirmation-button">
                    <i class="ri-file-list-line"></i> Your Orders
                </a>
            </div>
        </nav>
    </header>

    <main class="max-w-5xl mx-auto p-4 md:p-6 pb-20">
        <!-- Header Section -->
        <div class="text-center mb-10 no-print">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Your Order Receipts</h1>
            <p class="text-gray-500">View, print, and download your order receipts</p>
        </div>
        
        <!-- Download All Button -->
        <?php if ($order_result->num_rows > 1): ?>
        <div class="flex justify-end mb-6 no-print">
            <button onclick="downloadPDF('all')" class="flex items-center gap-2 bg-gradient-to-r from-gray-700 to-gray-900 text-white px-5 py-3 rounded-lg hover:shadow-lg transition-all duration-200">
                <i class="ri-download-cloud-line"></i> Download All Receipts
            </button>
        </div>
        <?php endif; ?>

        <?php if ($order_result->num_rows > 0): ?>
            <div class="space-y-8">
                <?php while ($order = $order_result->fetch_assoc()): ?>
                    <?php
                    // Close previous result set
                    $conn->next_result();

                    // Use stored procedure to get items for this order
                    $items_query = "CALL GetOrderItemsReceipt(?)";
                    $stmt = $conn->prepare($items_query);
                    if ($stmt === false) {
                        die("Prepare failed: " . $conn->error);
                    }
                    $stmt->bind_param("i", $order['ORDERS_ID']);
                    $stmt->execute();
                    $items_result = $stmt->get_result();
                    if ($items_result === false) {
                        die("Execute failed: " . $stmt->error);
                    }
                    ?>

                    <div class="receipt-card bg-white rounded-xl shadow-md overflow-hidden receipt-section" id="receipt-<?= $order['ORDERS_ID'] ?>">
                        <!-- Receipt Header -->
                        <div class="bg-gradient-to-r from-red-500 to-red-700 text-white p-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h2 class="text-2xl font-bold flex items-center gap-2">
                                        <i class="ri-bill-line"></i> Receipt #<?= htmlspecialchars($order['ORDERS_ID']) ?>
                                    </h2>
                                    <p class="text-white/80 mt-1">
                                        <i class="ri-calendar-line"></i> 
                                        <?= date('F j, Y', strtotime($order['ORDERS_DATE'])) ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                        <?= $order['STAT_ID'] == 1 ? 'bg-yellow-500' : 
                                           ($order['STAT_ID'] == 4 ? 'bg-green-600' : 'bg-blue-600') ?>">
                                        <?= htmlspecialchars($order['STATUS_NAME']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Receipt Body -->
                        <div class="p-6">
                            <!-- Customer & Order Info -->
                            <div class="grid md:grid-cols-3 gap-6 mb-8">
                                <!-- Customer Info -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                                        <i class="ri-user-3-line"></i> Customer
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        <?= htmlspecialchars($order['CUSTOMER_NAME'] ?? $_SESSION['username'] ?? 'Customer') ?>
                                    </p>
                                    <?php if (!empty($order['CUSTOMER_EMAIL'])): ?>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($order['CUSTOMER_EMAIL']) ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Shipping Info -->
                                <!-- Delivery Rider Info -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                                        <i class="ri-motorcycle-line"></i> Delivery Rider
                                    </h3>
                                    <?php if (!empty($order['RIDER_NAME'])): ?>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Name: </span>
                                            <?= htmlspecialchars($order['RIDER_NAME']) ?>
                                        </p>
                                        <?php if (!empty($order['RIDER_CONTACT'])): ?>
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Contact: </span>
                                                <?= htmlspecialchars($order['RIDER_CONTACT']) ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-600">No rider assigned yet</p>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Payment Info -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                                        <i class="ri-bank-card-line"></i> Payment
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Method: </span>
                                        <?= htmlspecialchars($order['PAYMENT_METHOD'] ?? "Cash on Delivery") ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Status: </span>
                                        <span class="<?= $order['STAT_ID'] == 1 ? 'text-yellow-600' : 'text-green-600' ?>">
                                            <?= htmlspecialchars($order['STATUS_NAME']) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Order Items -->
                            <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-1">
                                <i class="ri-shopping-basket-2-line"></i> Order Items
                            </h3>
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="py-3 px-4 text-xs text-gray-600 uppercase tracking-wider">Item</th>
                                            <th class="py-3 px-4 text-xs text-gray-600 uppercase tracking-wider">Quantity</th>
                                            <th class="py-3 px-4 text-xs text-gray-600 uppercase tracking-wider">Unit Price</th>
                                            <th class="py-3 px-4 text-xs text-gray-600 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <?php while ($item = $items_result->fetch_assoc()): ?>
                                            <?php
                                            $quantity = floatval($item['QTY']);
                                            $unit_price = floatval($item['UNIT_PRICE']);
                                            $unit_of_measure = trim(strtolower($item['UNIT_OF_MEASURE']));

                                            $display_quantity = $quantity;
                                            $unit_label = strtoupper($unit_of_measure);

                                            // Calculate total price with unit conversion
                                            if ($unit_of_measure === 'g') {
                                                $total_price = ($unit_price * $quantity) / 1000;
                                            } else {
                                                $total_price = $unit_price * $quantity;
                                            }
                                            ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="py-3 px-4">
                                                    <div class="flex items-center">
                                                        <div class="w-8 h-8 flex-shrink-0 rounded-full bg-red-50 flex items-center justify-center text-red-600 mr-3">
                                                            <i class="ri-knife-line"></i>
                                                        </div>
                                                        <div>
                                                            <div class="font-medium text-gray-800"><?= htmlspecialchars($item['MEAT_PART_NAME']) ?></div>
                                                            <div class="text-xs text-gray-500"><?= ucfirst($unit_of_measure) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 whitespace-nowrap">
                                                    <?php if ($unit_of_measure === 'g'): ?>
                                                        <span class="text-gray-700"><?= number_format($display_quantity) ?> <?= $unit_label ?></span>
                                                    <?php else: ?>
                                                        <span class="text-gray-700"><?= number_format($display_quantity, 1) ?> <?= $unit_label ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3 px-4 whitespace-nowrap">
                                                    <span class="text-gray-700">₱<?= number_format($unit_price, 2) ?></span>
                                                </td>
                                                <td class="py-3 px-4 whitespace-nowrap font-medium text-gray-800">
                                                    ₱<?= number_format($total_price, 2) ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Order Summary -->
                            <!-- Order Summary -->
                            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <?php
                                    // Calculate actual subtotal from items
                                    $subtotal = 0;
                                    // Reset pointer to beginning of result set
                                    $items_result->data_seek(0); 
                                    while ($item = $items_result->fetch_assoc()) {
                                        $quantity = floatval($item['QTY']);
                                        $unit_price = floatval($item['UNIT_PRICE']);
                                        $unit_of_measure = trim(strtolower($item['UNIT_OF_MEASURE']));
                                        
                                        // Calculate item total based on unit
                                        if ($unit_of_measure === 'g') {
                                            $item_total = ($unit_price * $quantity) / 1000; // Convert grams to kg
                                        } else {
                                            $item_total = $unit_price * $quantity;
                                        }
                                        $subtotal += $item_total;
                                    }
                                    
                                    $shipping_fee = 50.00; // Fixed shipping fee
                                    ?>
                                    <div class="text-gray-600">Subtotal</div>
                                    <div class="text-right text-gray-800">₱<?= number_format($subtotal, 2) ?></div>
                                    
                                    <div class="text-gray-600">Shipping</div>
                                    <div class="text-right text-gray-800">₱<?= number_format($shipping_fee, 2) ?></div>
                                    
                                    <div class="text-gray-800 font-semibold pt-2 border-t">Total</div>
                                    <div class="text-right pt-2 border-t">
                                        <span class="text-xl font-bold text-red-600">₱<?= number_format($subtotal + $shipping_fee, 2) ?></span>
                                        <?php if (isset($order['PAYMENT_METHOD']) && strtolower($order['PAYMENT_METHOD']) === 'gcash'): ?>
                                            <div class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full mt-1 inline-block">Already Paid via GCash</div>
                                        <?php elseif (isset($order['PAYMENT_METHOD']) && strtolower($order['PAYMENT_METHOD']) === 'cash on delivery'): ?>
                                            <div class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full mt-1 inline-block">To be paid on delivery</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Add this after the Order Summary section -->
                            <?php if (isset($order['PAYMENT_METHOD']) && strtolower($order['PAYMENT_METHOD']) === 'gcash'): ?>
                            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 flex-shrink-0 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3">
                                        <i class="ri-smartphone-line"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-blue-700">GCash Payment Details</h4>
                                        <p class="text-sm text-blue-600">Payment successfully processed via GCash.</p>
                                        <?php if (!empty($order['GCASH_NUMBER'])): ?>
                                            <p class="text-xs text-blue-500 mt-1">
                                                Account: <?= substr($order['GCASH_NUMBER'], 0, 4) . '•••' . substr($order['GCASH_NUMBER'], -4) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Thank You Note -->
                            <div class="mt-8 text-center border-t border-dashed border-gray-200 pt-6">
                                <p class="text-gray-600 mb-1">Thank you for shopping with us!</p>
                                <p class="text-xs text-gray-500">For any questions about your order, please contact our support team.</p>
                                
                                <!-- E-MEAT Logo -->
                                <div class="flex justify-center mt-4">
                                    <div class="flex items-center">
                                        <img src="../IMAGES/RED LOGO.png" alt="E-MEAT Logo" class="h-8 w-8 mr-2">
                                        <span class="text-red-600 font-bold">E-MEAT</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Receipt Actions -->
                        <div class="bg-gray-100 p-4 no-print">
                            <div class="flex justify-between">
                                <button onclick="printOrder(<?= $order['ORDERS_ID'] ?>)" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="ri-printer-line"></i> Print Receipt
                                </button>
                                <button onclick="downloadPDF(<?= $order['ORDERS_ID'] ?>)" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="ri-download-line"></i> Download PDF
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="ri-receipt-2-line text-3xl text-gray-400"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-700 mb-2">No Receipts Found</h2>
                <p class="text-gray-500 mb-6">You don't have any receipts to view yet.</p>
                <a href="index.php#shop" class="inline-flex items-center justify-center bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="ri-shopping-cart-line mr-2"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </main>
    
    <script>
        window.jspdf = window.jspdf || {};

        function printOrder(orderId) {
            let orderSection = document.getElementById(`receipt-${orderId}`);
            if (!orderSection) {
                alert("Receipt not found!");
                return;
            }

            let originalContent = document.body.innerHTML;
            
            // Add print-specific classes
            orderSection.classList.add('receipt-for-print');
            
            document.body.innerHTML = `
                <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
                    ${orderSection.outerHTML}
                </div>
            `;
            
            window.print();
            
            document.body.innerHTML = originalContent;
        }

        async function downloadPDF(orderId) {
            if (typeof window.jspdf.jsPDF !== 'function') {
                alert("PDF library not loaded. Please try again or check your internet connection.");
                return;
            }
            
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });
            
            try {
                if (orderId === 'all') {
                    let orderSections = document.querySelectorAll('.receipt-section');
                    if (orderSections.length === 0) {
                        alert("No receipts found!");
                        return;
                    }

                    let isFirstPage = true;

                    for (let section of orderSections) {
                        // Hide action buttons before capturing
                        const actionButtons = section.querySelectorAll('.no-print');
                        actionButtons.forEach(el => el.style.display = 'none');

                        const canvas = await html2canvas(section, {
                            scale: 2,
                            logging: false,
                            useCORS: true
                        });
                        
                        const imgData = canvas.toDataURL('image/png');
                        
                        // Restore action buttons
                        actionButtons.forEach(el => el.style.display = '');

                        if (!isFirstPage) {
                            doc.addPage();
                        }

                        const imgWidth = 190;
                        const imgHeight = canvas.height * imgWidth / canvas.width;
                        
                        doc.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight);
                        isFirstPage = false;
                    }

                    doc.save("All_Receipts.pdf");
                } else {
                    const section = document.getElementById(`receipt-${orderId}`);
                    if (!section) {
                        alert("Receipt not found!");
                        return;
                    }

                    // Hide action buttons before capturing
                    const actionButtons = section.querySelectorAll('.no-print');
                    actionButtons.forEach(el => el.style.display = 'none');

                    const canvas = await html2canvas(section, {
                        scale: 2,
                        logging: false,
                        useCORS: true
                    });
                    
                    const imgData = canvas.toDataURL('image/png');
                    
                    // Restore action buttons
                    actionButtons.forEach(el => el.style.display = '');

                    const imgWidth = 190;
                    const imgHeight = canvas.height * imgWidth / canvas.width;
                    
                    doc.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight);
                    doc.save(`Receipt_${orderId}.pdf`);
                }
            } catch (error) {
                console.error("Error generating PDF:", error);
                alert("Error generating PDF. Please try again later.");
            }
        }
    </script>
</body>
</html>