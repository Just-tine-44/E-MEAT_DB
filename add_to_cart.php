<?php
// filepath: c:\xampp\htdocs\website\add_to_cart.php
include 'config.php';
session_start();
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'cart_count' => 0
];

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("You must be logged in to add items to cart.");
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $user_id = $_SESSION['user_id'];
        $meat_part_id = intval($_POST['meat_part_id']);
        $qty = floatval($_POST['qty']);
        $unit = strtolower($_POST['unit']); // 'kg' or 'g'
        
        // Call stored procedure
        $stmt = $conn->prepare("CALL AddToUserCart(?, ?, ?, ?, @success, @message, @is_update, @new_qty, @product_name, @unit_price)");
        $stmt->bind_param("iids", $user_id, $meat_part_id, $qty, $unit);
        $stmt->execute();
        $stmt->close();
        
        // Get output parameters
        $result = $conn->query("SELECT @success as success, @message as message, @is_update as is_update, 
                               @new_qty as new_qty, @product_name as product_name, @unit_price as unit_price");
        $row = $result->fetch_assoc();
        
        if (!$row['success']) {
            throw new Exception($row['message']);
        }
        
        // Initialize cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if ($row['is_update']) {
            // Update existing item
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['meat_part_id'] == $meat_part_id && $item['unit'] == $unit) {
                    $_SESSION['cart'][$key]['quantity'] = $row['new_qty'];
                    break;
                }
            }
            
            $response['success'] = true;
            $response['message'] = $row['message'];
            $response['updated'] = true;
            $response['new_qty'] = $row['new_qty'];
        } else {
            // Add new item
            $_SESSION['cart'][] = [
                'meat_part_id' => $meat_part_id,
                'product_name' => $row['product_name'],
                'unit_price' => $row['unit_price'],
                'quantity' => $qty,
                'unit' => $unit,
                'added_at' => date('Y-m-d H:i:s')
            ];
            
            $response['success'] = true;
            $response['message'] = $row['message'];
            $response['added'] = true;
            $response['product_name'] = $row['product_name'];
            $response['quantity'] = $qty;
            $response['unit'] = $unit;
            $response['price'] = $row['unit_price'];
        }
        
        // Update cart count
        $response['cart_count'] = count($_SESSION['cart']);
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Add to cart error: " . $e->getMessage());
}

// Output the JSON response
echo json_encode($response);
exit;