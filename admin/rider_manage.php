<?php
// Start session at the very beginning - BEFORE any output or includes
// session_start();

// Then include other files
include('includes/header.php');
include '../config.php'; // Database connection

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    die("Access Denied. Only admins can access this page.");
}

// Handle rider operations
$successMessage = "";
$errorMessage = "";

// Delete rider
if (isset($_GET['delete'])) {
    $rider_id = intval($_GET['delete']);
    
    // Use stored procedure for deletion
    $stmt = $conn->prepare("CALL DeleteRider(?, @success, @message, @count)");
    $stmt->bind_param("i", $rider_id);
    $stmt->execute();
    
    // Get results from stored procedure
    $result = $conn->query("SELECT @success AS success, @message AS message, @count AS count");
    $row = $result->fetch_assoc();
    
    if ($row['success']) {
        $successMessage = $row['message'];
    } else {
        $errorMessage = $row['message'];
    }
    $stmt->close();
}

// Add or update rider
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rider_name = $_POST['rider_name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    
    if (isset($_POST['rider_id']) && $_POST['rider_id'] > 0) {
        // Update existing rider using stored procedure
        $rider_id = intval($_POST['rider_id']);
        $stmt = $conn->prepare("CALL UpdateRider(?, ?, ?, @success, @message)");
        $stmt->bind_param("iss", $rider_id, $rider_name, $contact);
        $stmt->execute();
        
        // Get results
        $result = $conn->query("SELECT @success AS success, @message AS message");
        $row = $result->fetch_assoc();
        
        if ($row['success']) {
            $successMessage = $row['message'];
        } else {
            $errorMessage = $row['message'];
        }
        $stmt->close();
    } else {
        // Add new rider using stored procedure
        $stmt = $conn->prepare("CALL AddRider(?, ?, @success, @message, @new_id)");
        $stmt->bind_param("ss", $rider_name, $contact);
        $stmt->execute();
        
        // Get results
        $result = $conn->query("SELECT @success AS success, @message AS message, @new_id AS new_id");
        $row = $result->fetch_assoc();
        
        if ($row['success']) {
            $successMessage = $row['message'];
        } else {
            $errorMessage = $row['message'];
        }
        $stmt->close();
    }
    
    // Clear any result sets
    while ($conn->more_results()) {
        $conn->next_result();
    }
}

// Get rider to edit using stored procedure
$edit_rider = null;
if (isset($_GET['edit'])) {
    $rider_id = intval($_GET['edit']);
    
    // Use stored procedure
    $stmt = $conn->prepare("CALL GetRiderById(?)");
    $stmt->bind_param("i", $rider_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    
    if ($edit_result->num_rows > 0) {
        $edit_rider = $edit_result->fetch_assoc();
    }
    $stmt->close();
    
    // Clear any result sets
    while ($conn->more_results()) {
        $conn->next_result();
    }
}

// Fetch all riders using stored procedure
$stmt = $conn->prepare("CALL GetRiderAll('id_asc')");
$stmt->execute();
$riders_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Riders</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Notifications -->
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
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Manage Riders</h1>
            <p class="text-gray-500 mt-1">Add, edit and delete delivery riders</p>
        </div>
        
        <!-- Add/Edit Rider Form -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4"><?= $edit_rider ? 'Edit Rider' : 'Add New Rider' ?></h2>
            
            <form method="post" class="space-y-4">
                <?php if ($edit_rider): ?>
                    <input type="hidden" name="rider_id" value="<?= $edit_rider['rider_id'] ?>">
                <?php endif; ?>
                
                <div>
                    <label for="rider_name" class="block text-sm font-medium text-gray-700 mb-1">Rider Name</label>
                    <input type="text" id="rider_name" name="rider_name" 
                           value="<?= $edit_rider ? htmlspecialchars($edit_rider['rider_name']) : '' ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                
                <div>
                    <label for="contact" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                    <input type="text" id="contact" name="contact" 
                           value="<?= $edit_rider ? htmlspecialchars($edit_rider['contact']) : '' ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                
                <div class="flex space-x-2">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <?= $edit_rider ? 'Update Rider' : 'Add Rider' ?>
                    </button>
                    
                    <?php if ($edit_rider): ?>
                        <a href="rider_manage.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Riders List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Rider Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($riders_result && $riders_result->num_rows > 0): ?>
                        <?php while ($rider = $riders_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $rider['rider_id'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-700">
                                            <i class="fas fa-motorcycle"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($rider['rider_name']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <a href="tel:<?= htmlspecialchars($rider['contact']) ?>" class="hover:text-red-600">
                                            <?= htmlspecialchars($rider['contact']) ?>
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                    <a href="?edit=<?= $rider['rider_id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="javascript:void(0)" class="text-red-600 hover:text-red-900 delete-rider" data-id="<?= $rider['rider_id'] ?>" data-name="<?= htmlspecialchars($rider['rider_name']) ?>">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No riders found. Add one using the form above.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // Debug session info
        console.log("Session user_id: <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set' ?>");
        console.log("Session user_type: <?= isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'Not set' ?>");
        
        // Delete confirmation
        document.querySelectorAll('.delete-rider').forEach(function(element) {
            element.addEventListener('click', function() {
                const riderId = this.getAttribute('data-id');
                const riderName = this.getAttribute('data-name');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete rider "${riderName}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `?delete=${riderId}`;
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>