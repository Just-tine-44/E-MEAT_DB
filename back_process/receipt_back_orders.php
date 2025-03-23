<?php
include '../connection/config.php'; // Database connection
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? null; // If null, show all orders

try {
    // Call the stored procedure for fetching orders
    $stmt = $conn->prepare("CALL GetUserOrders(?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters - pass both user_id and order_id (which may be null)
    $stmt->bind_param("ii", $user_id, $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    $stmt->close();
    
    // Clear any remaining result sets
    $conn->next_result();
    
} catch (Exception $e) {
    // Log error but show user-friendly message
    error_log("Error fetching orders: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Unable to retrieve order information. Please try again later.</div>";
    // You could also redirect to an error page or set a message variable to display later
}
?>