<?php
// filepath: c:\xampp\htdocs\website\remove_from_session_cart.php
include 'config.php';
session_start();
header('Content-Type: application/json');

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

// Validate required parameters
if (!isset($_SESSION['cart']) || !isset($data['index'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request: Missing required parameters'
    ]);
    exit;
}

$index = intval($data['index']);
$meat_part_id = isset($data['meat_part_id']) ? intval($data['meat_part_id']) : null;
$unit = isset($data['unit']) ? strtolower($data['unit']) : null;

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// Make sure the cart item exists
if ($index < 0 || $index >= count($_SESSION['cart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Cart item not found at index ' . $index
    ]);
    exit;
}

// Double verification by checking meat_part_id and unit if provided
if ($meat_part_id !== null && $unit !== null) {
    $cart_item = $_SESSION['cart'][$index];
    
    // Verify that the item at this index matches what we expect
    if ($cart_item['meat_part_id'] != $meat_part_id || strtolower($cart_item['unit']) != strtolower($unit)) {
        // Look for the correct item in the cart
        $found = false;
        foreach ($_SESSION['cart'] as $i => $item) {
            if ($item['meat_part_id'] == $meat_part_id && strtolower($item['unit']) == strtolower($unit)) {
                $index = $i;  // Update the index to the correct one
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            echo json_encode([
                'success' => false,
                'message' => 'Item verification failed - product details did not match'
            ]);
            exit;
        }
    }
}

// Get item details before removing from session
$item = $_SESSION['cart'][$index];
$meat_part_id = $item['meat_part_id'] ?? 0;
$unit = $item['unit'] ?? '';
$product_name = $item['product_name'] ?? 'Unknown product';

try {
    // Only perform database operations if user is logged in
    if ($isLoggedIn) {
        // Call stored procedure
        $stmt = $conn->prepare("CALL RemoveFromUserCart(?, ?, ?, @success, @message, @rows_affected)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("iis", $user_id, $meat_part_id, $unit);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $stmt->close();
        
        // Get output parameters
        $result = $conn->query("SELECT @success as success, @message as message, @rows_affected as rows_affected");
        if (!$result) {
            throw new Exception("Failed to retrieve stored procedure results: " . $conn->error);
        }
        
        $row = $result->fetch_assoc();
        $conn->next_result(); // Clear any remaining result sets
        
        // Log the database operation result
        if (!$row['success']) {
            // If database removal failed but item exists in session, continue with session removal
            error_log("Database removal warning: " . $row['message'] . " (user: {$user_id}, meat_part_id: {$meat_part_id}, unit: {$unit})");
        } else {
            error_log("Removed {$row['rows_affected']} item(s) from database cart for user {$user_id}");
        }
    }
    
    // Store the item for response
    $removed_item = $_SESSION['cart'][$index];
    
    // Remove item from session
    unset($_SESSION['cart'][$index]);
    
    // Re-index the array to prevent gaps
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Item removed: ' . $product_name,
        'cart_count' => count($_SESSION['cart']),
        'removed_index' => $index,
        'product_id' => $meat_part_id,
        'unit' => $unit,
        'db_updated' => $isLoggedIn && ($row['success'] ?? false)
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Error removing cart item: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error_details' => [
            'index' => $index,
            'product_id' => $meat_part_id ?? 'Not provided',
            'unit' => $unit ?? 'Not provided'
        ]
    ]);
}
?>