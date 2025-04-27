<?php

session_start(); // Start the session

// To match what login.php is setting:
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    $_SESSION['message'] = "You need to log in as admin to access this page";
    header("Location: ../users/login.php");
    exit();
}

// Then include other files
$page_title = "Manage Riders | E-MEAT Admin";
include('new_include/sidebar.php');
include '../connection/config.php'; // Database connection

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
    
    // Server-side validation
    $isValid = true;
    
    // Validate rider name
    if (empty($rider_name) || strlen($rider_name) < 2 || !preg_match("/^[a-zA-Z\s\-']+$/", $rider_name)) {
        $errorMessage = "Invalid rider name. Must contain only letters, spaces, hyphens or apostrophes.";
        $isValid = false;
    }
    
    // Validate contact number (must be exactly 11 digits for Philippine mobile format)
    if (empty($contact) || strlen($contact) != 11 || !preg_match("/^[0-9]+$/", $contact)) {
        $errorMessage = "Invalid contact number. Must be exactly 11 digits.";
        $isValid = false;
    }
    
    if ($isValid) {
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
    <title><?= $page_title ?></title>
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
        
        /* Custom scrollbar for tables */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
        
        /* Table fixed height with scrolling */
        .table-fixed-height {
            max-height: 440px; /* Shows approximately 5 rows */
            overflow-y: auto;
        }
        
        /* Input validation styles */
        .input-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body class="text-gray-800">
    <!-- Main Content Wrapper - position it to the right of the sidebar -->
    <div class="pl-0 lg:pl-64 transition-all duration-300">
        <!-- Page Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
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
            
            <!-- Page Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Manage Riders</h1>
                        <p class="text-gray-600 mt-1 text-sm">Add, edit and assign delivery riders</p>
                    </div>
                </div>
            </div>
            
            <!-- Add/Edit Rider Form -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <h2 class="text-lg font-semibold mb-4"><?= $edit_rider ? 'Edit Rider' : 'Add New Rider' ?></h2>
                
                <form method="post" id="riderForm" class="space-y-4">
                    <?php if ($edit_rider): ?>
                        <input type="hidden" name="rider_id" value="<?= $edit_rider['rider_id'] ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label for="rider_name" class="block text-sm font-medium text-gray-700 mb-1">Rider Name</label>
                        <input type="text" id="rider_name" name="rider_name" required
                               placeholder="Enter rider's full name" 
                               value="<?= $edit_rider ? htmlspecialchars($edit_rider['rider_name']) : '' ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <p class="mt-1 text-xs text-gray-500">Only letters, spaces, hyphens and apostrophes are allowed</p>
                        <p id="name-error" class="error-message hidden"></p>
                    </div>
                    
                    <div>
                        <label for="contact" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input type="text" id="contact" name="contact" required
                               placeholder="09XXXXXXXXX" 
                               value="<?= $edit_rider ? htmlspecialchars($edit_rider['contact']) : '' ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <p class="mt-1 text-xs text-gray-500">Enter a valid 11-digit mobile number (e.g. 09123456789)</p>
                        <p id="contact-error" class="error-message hidden"></p>
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
                <div class="overflow-x-auto scrollbar-thin table-fixed-height">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
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
                                                    <?php
                                                        // Format phone number: 09XX XXX XXXX
                                                        $phone = htmlspecialchars($rider['contact']);
                                                        if (strlen($phone) == 11) {
                                                            echo substr($phone, 0, 4) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
                                                        } else {
                                                            echo $phone; // Just show as-is if not standard format
                                                        }
                                                    ?>
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
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                        No riders found. Add one using the form above.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            // Rider name validation - letters, spaces, and common name characters only
            const riderNameInput = document.getElementById('rider_name');
            const riderNameError = document.getElementById('name-error');
            
            riderNameInput.addEventListener('input', function() {
                // Allow letters, spaces, hyphens, and apostrophes (for names like O'Connor or Mary-Jane)
                const validInput = this.value.replace(/[^a-zA-Z\s\-']/g, '');
                
                if (this.value !== validInput) {
                    this.value = validInput;
                    riderNameError.textContent = "Only letters, spaces, hyphens and apostrophes are allowed";
                    riderNameError.classList.remove('hidden');
                    this.classList.add('input-error');
                } else {
                    riderNameError.classList.add('hidden');
                    this.classList.remove('input-error');
                }
            });
            
            // Contact number validation - numbers only, limit to 11 digits (standard PH mobile format)
            const contactInput = document.getElementById('contact');
            const contactError = document.getElementById('contact-error');
            
            contactInput.addEventListener('input', function() {
                // Allow only digits
                const validInput = this.value.replace(/[^0-9]/g, '');
                
                if (this.value !== validInput) {
                    this.value = validInput;
                    contactError.textContent = "Only numbers are allowed";
                    contactError.classList.remove('hidden');
                    this.classList.add('input-error');
                } else {
                    contactError.classList.add('hidden');
                    this.classList.remove('input-error');
                }
                
                // Limit to 11 digits (standard PH mobile number)
                if (this.value.length > 11) {
                    this.value = this.value.slice(0, 11);
                    contactError.textContent = "Maximum 11 digits allowed";
                    contactError.classList.remove('hidden');
                }
            });
            
            // Form submission validation
            const riderForm = document.getElementById('riderForm');
            riderForm.addEventListener('submit', function(event) {
                let hasErrors = false;
                
                // Validate rider name (at least 2 characters, letters only)
                const riderName = riderNameInput.value.trim();
                if (riderName.length < 2) {
                    riderNameError.textContent = "Name must be at least 2 characters";
                    riderNameError.classList.remove('hidden');
                    riderNameInput.classList.add('input-error');
                    hasErrors = true;
                }
                
                if (!/^[a-zA-Z\s\-']+$/.test(riderName)) {
                    riderNameError.textContent = "Name must contain only letters, spaces, hyphens and apostrophes";
                    riderNameError.classList.remove('hidden');
                    riderNameInput.classList.add('input-error');
                    hasErrors = true;
                }
                
                // Validate contact number (must be exactly 11 digits for PH format)
                const contact = contactInput.value.trim();
                if (contact.length !== 11) {
                    contactError.textContent = "Contact number must be exactly 11 digits (e.g., 09123456789)";
                    contactError.classList.remove('hidden');
                    contactInput.classList.add('input-error');
                    hasErrors = true;
                }
                
                if (!/^[0-9]+$/.test(contact)) {
                    contactError.textContent = "Contact number must contain only digits";
                    contactError.classList.remove('hidden');
                    contactInput.classList.add('input-error');
                    hasErrors = true;
                }
                
                // Check for leading '09' in Philippine mobile numbers
                if (!contact.startsWith('09')) {
                    contactError.textContent = "Philippine mobile numbers should start with '09'";
                    contactError.classList.remove('hidden');
                    contactInput.classList.add('input-error');
                    hasErrors = true;
                }
                
                // If there are validation errors, prevent form submission and show error
                if (hasErrors) {
                    event.preventDefault();
                    Swal.fire({
                        title: "Validation Error",
                        text: "Please check the form and fix the errors",
                        icon: "error"
                    });
                }
            });
        });
        
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