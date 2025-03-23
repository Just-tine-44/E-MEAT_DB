<?php
// filepath: c:\xampp\htdocs\website\update_session_cart.php
include '../connection/config.php';
session_start();
header('Content-Type: application/json');

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['cart']) || !isset($data['index']) || !isset($data['quantity']) || !isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit;
}

$index = intval($data['index']);
$quantity = floatval($data['quantity']);
$user_id = $_SESSION['user_id'];

// Make sure the cart item exists
if (!isset($_SESSION['cart'][$index])) {
    echo json_encode([
        'success' => false,
        'message' => 'Cart item not found'
    ]);
    exit;
}

try {
    // Get item details from session
    $meat_part_id = $_SESSION['cart'][$index]['meat_part_id'];
    $unit = $_SESSION['cart'][$index]['unit'];
    
    // Call stored procedure
    $stmt = $conn->prepare("CALL UpdateUserCart(?, ?, ?, ?, @success, @message)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iids", $user_id, $meat_part_id, $quantity, $unit);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $stmt->close();
    
    // Get output parameters
    $result = $conn->query("SELECT @success as success, @message as message");
    if (!$result) {
        throw new Exception("Failed to retrieve stored procedure results: " . $conn->error);
    }
    
    $row = $result->fetch_assoc();
    
    if (!$row['success']) {
        throw new Exception($row['message']);
    }
    
    // Update session cart on success
    $_SESSION['cart'][$index]['quantity'] = $quantity;
    
    echo json_encode([
        'success' => true,
        'message' => $row['message'],
        'quantity' => $quantity,
        'cart_count' => count($_SESSION['cart'])
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>