<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

$message = "";
$message_type = "success";

if (isset($_POST['submit'])) {
    $user_fname = $_POST['user_fname'];
    $user_lname = $_POST['user_lname'];
    $user_role = $_POST['user_role'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $username = $_POST['username'];
    $new_password = $_POST['new_password']; // No hashing

    try {
        // Check if username exists using stored procedure
        $check_stmt = $conn->prepare("CALL CheckUsernameExists(?, @exists)");
        if (!$check_stmt) {
            throw new Exception("Prepare check failed: " . $conn->error);
        }
        
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->close();
        
        // Get the result from output parameter
        $result = $conn->query("SELECT @exists as username_exists");
        if (!$result) {
            throw new Exception("Failed to retrieve username check results: " . $conn->error);
        }
        
        $row = $result->fetch_assoc();
        $username_exists = $row['username_exists'] ? true : false;
        $conn->next_result(); // Clear any remaining result sets

        if ($username_exists) {
            // Username already taken
            $message = "Username already taken!";
            $message_type = "error";
        } else {
            // Use stored procedure for inserting the user data
            $insert_stmt = $conn->prepare("CALL InsertAppUser(?, ?, ?, ?, ?, ?, ?, @success, @message)");
            if (!$insert_stmt) {
                throw new Exception("Prepare insert failed: " . $conn->error);
            }
            
            $insert_stmt->bind_param("sssssss", 
                $user_fname, 
                $user_lname, 
                $user_role, 
                $address, 
                $phone_number, 
                $username, 
                $new_password
            );
            
            $insert_stmt->execute();
            $insert_stmt->close();
            
            // Get the result from output parameters
            $result = $conn->query("SELECT @success as success, @message as message");
            if (!$result) {
                throw new Exception("Failed to retrieve insert results: " . $conn->error);
            }
            
            $row = $result->fetch_assoc();
            
            if ($row['success']) {
                $message = "Registration successful!";
                $message_type = "success";
            } else {
                $message = $row['message'] ?? "Unknown error during registration";
                $message_type = "error";
            }
            
            $conn->next_result(); // Clear any remaining result sets
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
        error_log("Registration error: " . $e->getMessage());
    } finally {
        // Close connection if still open
        if (isset($conn) && $conn) {
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | E-MEAT</title>
    <link rel="icon" type="image" href="../website/IMAGES/RED LOGO.png">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Remix Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.5.0/remixicon.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'meat-red': '#733d3d',
                        'meat-light': '#f7f0e2',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-meat-light to-white min-h-screen flex items-center justify-center p-4 py-10">
    <!-- Registration Card -->
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Card Top with Logo -->
        <div class="bg-meat-red py-5 px-6 flex justify-center">
            <img src="../website/IMAGES/RED LOGO.png" alt="E-MEAT Logo" class="h-20 bg-white rounded-full p-2">
        </div>
        
        <!-- Registration Form -->
        <div class="p-6 md:p-8">
            <h2 class="text-2xl font-bold text-meat-red text-center mb-6">Create Your Account</h2>
            
            <form action="" method="post" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="user_fname" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="ri-user-line text-gray-400"></i>
                            </div>
                            <input type="text" name="user_fname" id="user_fname" placeholder="First Name" required 
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-meat-red focus:border-meat-red transition-colors">
                        </div>
                    </div>
                    
                    <div>
                        <label for="user_lname" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="ri-user-line text-gray-400"></i>
                            </div>
                            <input type="text" name="user_lname" id="user_lname" placeholder="Last Name" required 
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-meat-red focus:border-meat-red transition-colors">
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="user_role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-shield-user-line text-gray-400"></i>
                        </div>
                        <select name="user_role" id="user_role" required
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-meat-red focus:border-meat-red transition-colors appearance-none bg-white">
                            <option value="" disabled selected>Select Role</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="ri-arrow-down-s-line text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-map-pin-line text-gray-400"></i>
                        </div>
                        <input type="text" name="address" id="address" placeholder="Your Address" required 
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-meat-red focus:border-meat-red transition-colors">
                    </div>
                </div>
                
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-phone-line text-gray-400"></i>
                        </div>
                        <input type="text" name="phone_number" id="phone_number" placeholder="Phone Number" required 
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-meat-red focus:border-meat-red transition-colors">
                    </div>
                </div>
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-user-3-line text-gray-400"></i>
                        </div>
                        <input type="text" name="username" id="username" placeholder="Choose a username" required 
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-meat-red focus:border-meat-red transition-colors">
                    </div>
                </div>
                
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-lock-password-line text-gray-400"></i>
                        </div>
                        <input type="password" name="new_password" id="new_password" placeholder="Create a password" required
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-meat-red focus:border-meat-red transition-colors">
                    </div>
                </div>
                
                <button type="submit" name="submit" 
                    class="w-full bg-meat-red hover:bg-opacity-90 text-white py-2.5 px-4 rounded-lg font-medium transition-all transform hover:scale-[1.02] duration-200 mt-4 shadow-md">
                    REGISTER NOW
                </button>
            </form>
            
            <div class="mt-6 text-center text-gray-600 text-sm">
                Already have an account? 
                <a href="login.php" class="text-meat-red font-medium hover:underline">Login now</a>
            </div>
        </div>
    </div>

    <!-- Modal for Messages -->
    <div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-meat-light px-6 py-3 flex justify-between items-center border-b">
                <h3 class="text-lg font-medium text-meat-red">
                    <?php echo $message_type === 'error' ? 'Registration Error' : 'Registration Successful'; ?>
                </h3>
                <button type="button" class="close-modal text-gray-500 hover:text-gray-800 focus:outline-none">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="px-6 py-4">
                <div class="<?php echo $message_type === 'error' ? 'text-red-600' : 'text-green-600'; ?> text-center">
                    <?php echo $message; ?>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-3 flex justify-end">
                <button type="button" 
                    class="bg-meat-red text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-opacity-90 transition-colors close-modal">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('messageModal');
        const closeButtons = document.querySelectorAll('.close-modal');
        
        // Show modal if there's a message
        <?php if (!empty($message)): ?>
            modal.classList.remove('hidden');
            
            // Add slide-in animation class
            document.querySelector('#messageModal > div').classList.add('animate-slide-in');
        <?php endif; ?>
        
        // Close modal when clicking close button
        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                modal.classList.add('hidden');
            });
        });
        
        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
        
        // Define animations
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                @keyframes slide-in {
                    from {
                        opacity: 0;
                        transform: translateY(-20px) scale(0.95);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }
                .animate-slide-in {
                    animation: slide-in 0.3s ease-out forwards;
                }
            </style>
        `);
    </script>
</body>
</html>