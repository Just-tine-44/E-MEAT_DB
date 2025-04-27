<?php
include '../connection/config.php'; // Database connection

// Get all meat parts from database
$sql = "SELECT m.MEAT_PART_NAME as name, m.MEAT_CATEGORY_ID as category_id 
        FROM MEAT_PART m";  
$result = $conn->query($sql);

$existingParts = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $existingParts[] = [
            'name' => $row['name'],
            'category_id' => $row['category_id']
        ];
    }
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($existingParts);

$conn->close();
?>