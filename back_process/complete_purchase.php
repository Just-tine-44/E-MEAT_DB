<?php
include '../connection/config.php'; // Database connection
session_start();

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $shipper = $_POST['shipper'];
    $payment_method = $_POST['payment_method'];
    $admin_id = $_SESSION['user_id'] ?? null; // Get the logged-in admin ID

    if (!$admin_id) {
        $response['error'] = "Unauthorized action.";
        echo json_encode($response);
        exit;
    }

    // Call the stored procedure
    $query = "CALL sp_update_order_checkout(?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $order_id, $shipper, $payment_method, $admin_id);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = "Failed to update order.";
    }

    $stmt->close();
}

echo json_encode($response);
?>