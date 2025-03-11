<?php
// filepath: c:\xampp\htdocs\website\login.php
session_start();
include 'config.php';

// Ensure $conn is defined and connected to the database
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = ""; // Initialize the message variable
$message_type = ""; // Initialize the message type

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Call the stored procedure for authentication
        $stmt = $conn->prepare("CALL AuthenticateUser(?, @user_id, @password, @user_type)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();
        
        // Get the output parameters
        $result = $conn->query("SELECT @user_id as user_id, @password as stored_password, @user_type as user_type");
        if (!$result) {
            throw new Exception("Failed to retrieve auth results: " . $conn->error);
        }
        
        $row = $result->fetch_assoc();
        $conn->next_result(); // Clear any remaining result sets
        
        $user_id = $row['user_id'] ?? null;
        $stored_password = $row['stored_password'] ?? null;
        $user_type = $row['user_type'] ?? null;

        if ($user_id && $password === $stored_password) {
            // Store user info in session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = $user_type;
            
            // Add the cart loading code here - only for non-admin users
            if ($user_type !== 'admin') {
                try {
                    // Call the stored procedure to get user's cart
                    $cart_stmt = $conn->prepare("CALL GetUserCart(?)");
                    if (!$cart_stmt) {
                        throw new Exception("Failed to prepare cart statement: " . $conn->error);
                    }
                    
                    $cart_stmt->bind_param("i", $user_id);
                    $cart_stmt->execute();
                    $result = $cart_stmt->get_result();
                    $cart_stmt->close();
                    $conn->next_result(); // Clear any remaining result sets
                    
                    // Initialize session cart
                    $_SESSION['cart'] = [];
                    
                    // Load database cart items into session
                    while ($row = $result->fetch_assoc()) {
                        $_SESSION['cart'][] = [
                            'meat_part_id' => $row['MEAT_PART_ID'],
                            'product_name' => $row['MEAT_PART_NAME'],
                            'unit_price' => $row['UNIT_PRICE'],
                            'quantity' => $row['QUANTITY'],
                            'unit' => $row['UNIT_OF_MEASURE'],
                            'added_at' => isset($row['ADDED_AT']) ? $row['ADDED_AT'] : date('Y-m-d H:i:s')
                        ];
                    }
                } catch (Exception $e) {
                    // Log error but continue - don't prevent login if cart loading fails
                    error_log("Error loading cart: " . $e->getMessage());
                    $_SESSION['cart'] = []; // Ensure cart is initialized even if loading fails
                }
            }
            
            // Redirect based on user type
            if ($user_type === 'admin') {
                header("Location: admin/admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $message = "Invalid username or password.";
            $message_type = "error"; // Set error type for styling
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
        error_log("Login error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | E-MEAT</title>
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

<body class="bg-gradient-to-br from-meat-light to-white min-h-screen flex items-center justify-center p-4">
    <!-- Login Card -->
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Card Top with Logo -->
        <div class="bg-meat-red py-6 px-6 flex justify-center">
            <img src="../website/IMAGES/RED LOGO.png" alt="E-MEAT Logo" class="h-20 bg-white rounded-full p-2">
        </div>
        
        <!-- Login Form -->
        <div class="p-8">
            <h2 class="text-2xl font-bold text-meat-red text-center mb-6">Welcome Back</h2>
            
            <form action="" method="post" class="space-y-5">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-user-line text-gray-400"></i>
                        </div>
                        <input type="text" name="username" id="username" placeholder="Enter your username" required 
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-meat-red focus:border-meat-red transition-colors">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-lock-line text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" placeholder="Enter your password" required
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-meat-red focus:border-meat-red transition-colors">
                    </div>
                </div>
                
                <button type="submit" name="submit" 
                    class="w-full bg-meat-red hover:bg-opacity-90 text-white py-2.5 px-4 rounded-lg font-medium transition-all transform hover:scale-[1.02] duration-200 shadow-md">
                    LOGIN
                </button>
            </form>
            
            <div class="mt-6 text-center text-gray-600 text-sm">
                Don't have an account? 
                <a href="register.php" class="text-meat-red font-medium hover:underline">Register now</a>
            </div>
        </div>
    </div>

    <!-- Modal for Messages -->
    <div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all animate-fade-in-down">
            <!-- Modal Header -->
            <div class="bg-meat-light px-6 py-3 flex justify-between items-center border-b">
                <h3 class="text-lg font-medium text-meat-red">
                    <?php echo $message_type === 'error' ? 'Error' : 'Success'; ?>
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
                @keyframes fade-in-down {
                    from {
                        opacity: 0;
                        transform: translateY(-20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                .animate-fade-in-down {
                    animation: fade-in-down 0.3s ease-out forwards;
                }
                
                @keyframes slide-in {
                    from {
                        opacity: 0;
                        transform: scale(0.95);
                    }
                    to {
                        opacity: 1;
                        transform: scale(1);
                    }
                }
                .animate-slide-in {
                    animation: slide-in 0.2s ease-out forwards;
                }
            </style>
        `);
    </script>
</body>
</html>