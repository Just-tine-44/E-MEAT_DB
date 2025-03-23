<?php
// filepath: c:\xampp\htdocs\website\process_session_order.php
include '../connection/config.php';
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to complete an order'
    ]);
    exit;
}

// Check if cart exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Your cart is empty'
    ]);
    exit;
}

try {
    // Get data from request
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id'];
    $payment_id = isset($data['payment_method_id']) ? intval($data['payment_method_id']) : null;
    
    // Validate payment method (only required field)
    if (!$payment_id) {
        throw new Exception('Missing payment method information');
    }
    
    // Add any required debug info to the session cart items
    foreach ($_SESSION['cart'] as &$item) {
        // Ensure unit is properly set
        if (!isset($item['unit']) || empty($item['unit'])) {
            $item['unit'] = 'kg'; // Default to kg if missing
        }
        
        // Ensure product_name exists (required for error messages)
        if (!isset($item['product_name']) || empty($item['product_name'])) {
            // Get product name from database if missing using stored procedure
            $stmt = $conn->prepare("CALL GetProductName(?, @product_name)");
            $stmt->bind_param("i", $item['meat_part_id']);
            $stmt->execute();
            $stmt->close();
            
            // Get the output parameter
            $result = $conn->query("SELECT @product_name AS product_name");
            if ($row = $result->fetch_assoc()) {
                $item['product_name'] = $row['product_name'];
            } else {
                $item['product_name'] = "Unknown Product";
            }
            $conn->next_result(); // Clear any remaining result sets
        }
    }
    unset($item); // Break reference
    
    // Convert cart to JSON format for stored procedure
    $cart_json = json_encode($_SESSION['cart']);
    
    // For debugging, log the JSON structure
    error_log("Cart JSON for stored procedure: " . $cart_json);
    
    // Call ProcessOrder stored procedure
    $stmt = $conn->prepare("CALL ProcessOrder(?, ?, ?, @success, @message, @order_id)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iis", $user_id, $payment_id, $cart_json);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $stmt->close();
    $conn->next_result(); // Clear any remaining result sets
    
    // Get output parameters using stored procedure
    $stmt = $conn->prepare("CALL GetProcessOrderResults(@success, @message, @order_id)");
    $stmt->execute();
    $stmt->close();
    $conn->next_result(); // Clear any remaining result sets
    
    // Get the output parameters
    $result = $conn->query("SELECT @success AS success, @message AS message, @order_id AS order_id");
    if (!$result) {
        throw new Exception("Failed to retrieve stored procedure results: " . $conn->error);
    }
    
    $row = $result->fetch_assoc();
    
    // Check result
    if (!$row['success']) {
        throw new Exception($row['message']);
    }
    
    // Clear the session cart on success
    unset($_SESSION['cart']);

    // Return success response with order ID
    echo json_encode([
        'success' => true,
        'order_id' => $row['order_id'],
        'message' => 'Order processed successfully'
    ]);
    
} catch (Exception $e) {
    // Log detailed error information
    error_log("Order processing error: " . $e->getMessage());
    
    // Return error message with more details for debugging
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'sql_error' => $conn->error ?? 'No SQL error'
    ]);
}
?>