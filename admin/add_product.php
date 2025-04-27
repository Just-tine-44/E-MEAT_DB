<?php
session_start(); // Must be at the very top before any output

// To match what login.php is setting:
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    $_SESSION['message'] = "You need to log in as admin to access this page";
    header("Location: ../users/login.php");
    exit();
}

$page_title = "Add New Product | E-MEAT Admin";
include('new_include/sidebar.php');
include '../connection/config.php'; // Database connection

// Initialize variables to track form submission status
$form_submitted = false;
$submission_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $meat_category_id = $_POST['meat_category'] ?? '';
    
    // Check if custom input was used
    if (isset($_POST['meat_part_name_custom']) && !empty($_POST['meat_part_name_custom'])) {
        $meat_part_name = $_POST['meat_part_name_custom'];
    } else {
        $meat_part_name = $_POST['meat_part_name'] ?? '';
    }
    
    $qty_available = $_POST['qty_available'] ?? '';
    $unit_of_measure = $_POST['unit_of_measure'] ?? '';
    $unit_price = $_POST['unit_price'] ?? '';
    
    // Retrieve user ID from session
    if (isset($_SESSION['user_id'])) {
        $app_user_id = $_SESSION['user_id'];
    } else {
        $submission_error = "User not logged in.";
    }

    // Only proceed if no errors so far
    if (empty($submission_error)) {
        // Handle file upload
        if (isset($_FILES['meat_part_photo']) && $_FILES['meat_part_photo']['error'] === UPLOAD_ERR_OK) {
            // Get file info
            $file_name = $_FILES['meat_part_photo']['name']; // Original file name
            $file_tmp = $_FILES['meat_part_photo']['tmp_name'];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            // Set allowed file extensions
            $allowed_ext = ['jpg', 'jpeg', 'png'];

            if (in_array(strtolower($file_ext), $allowed_ext)) {
                $upload_dir = "../website/IMAGES/MEATS/";
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (is_writable($upload_dir)) {
                    // Avoid overwriting files: Add timestamp if the file already exists
                    $target_file = $upload_dir . $file_name;
                    if (file_exists($target_file)) {
                        $file_name = time() . "_" . $file_name;
                        $target_file = $upload_dir . $file_name;
                    }

                    if (move_uploaded_file($file_tmp, $target_file)) {
                        $meat_part_photo = $file_name; // Save original filename

                        // Call the stored procedure
                        $stmt = $conn->prepare("CALL InsertMeatPart(?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("iissdss", $app_user_id, $meat_category_id, $meat_part_name, $meat_part_photo, $qty_available, $unit_of_measure, $unit_price);

                        if ($stmt->execute()) {
                            // Set flag for success - don't redirect, just show modal
                            $form_submitted = true;
                            $_SESSION['product_added'] = true;
                        } else {
                            $submission_error = "Failed to insert product: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $submission_error = "Failed to upload image.";
                    }
                } else {
                    $submission_error = "Upload directory is not writable.";
                }
            } else {
                $submission_error = "Invalid file type. Allowed types: " . implode(", ", $allowed_ext);
            }
        } else {
            $submission_error = "No image uploaded or an error occurred.";
        }
    }
}
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
    <!-- Custom styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
        }
        
        .file-upload-wrapper {
            position: relative;
            width: 100%;
            height: 180px;
            border: 2px dashed #cbd5e1;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: all 0.3s ease;
        }
        
        .file-upload-wrapper:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8;
        }
        
        .file-upload-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }
        
        .file-upload-text {
            z-index: 5;
            pointer-events: none;
            text-align: center;
        }
        
        .preview-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }
        
        .form-success {
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .input-icon {
            position: absolute;
            top: 50%;
            left: 0.75rem;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <!-- Main Content Wrapper - position it to the right of the sidebar -->
    <div class="pl-0 lg:pl-64 transition-all duration-300">
        <!-- Page Content -->
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Add New Product</h1>
                <div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
                    <a href="admin.php" class="hover:text-red-600">Dashboard</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-red-600">Add Product</span>
                </div>
            </div>
            
            <!-- Success Modal -->
            <?php if ($form_submitted || (isset($_SESSION['product_added']) && $_SESSION['product_added'] === true)): ?>
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" id="successModal">
                    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full p-6 transform transition-all form-success">
                        <div class="text-center">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-5">
                                <i class="fas fa-check text-2xl text-green-600"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">Product Added Successfully!</h3>
                            <p class="text-gray-600 mb-6">Your new product has been added.</p>
                            <div class="flex justify-center">
                                <button type="button" onclick="closeModal()" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-red-500">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $_SESSION['product_added'] = false; ?>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Form Section -->
                <div class="md:col-span-2">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-100">
                            <h2 class="text-xl font-semibold text-gray-800">Product Information</h2>
                            <p class="text-sm text-gray-500 mt-1">Fill in the details about the new meat product</p>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6" id="form-d" onsubmit="return validateQty()">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Meat Category -->
                                <div>
                                    <label for="meat_category" class="block text-sm font-medium text-gray-700 mb-2">Meat Category</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-drumstick-bite text-gray-400"></i>
                                        </div>
                                        <select name="meat_category" id="meat_category" required 
                                                class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 appearance-none bg-white">
                                            <option value="" disabled selected>Select category</option>
                                            <option value="1">Beef</option>
                                            <option value="2">Pork</option>
                                            <option value="3">Chicken</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Meat Part Name -->
                                <div>
                                    <label for="meat_part_name" class="block text-sm font-medium text-gray-700 mb-2">Meat Part Name</label>
                                    <div class="relative" id="meat_part_container">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-tag text-gray-400"></i>
                                        </div>
                                        <select name="meat_part_name" id="meat_part_name" required 
                                                class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 appearance-none bg-white">
                                            <option value="" disabled selected>Select meat part</option>
                                            <!-- Options will be populated via JavaScript -->
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Upload Photo -->
                            <div>
                                <label for="meat_part_photo" class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
                                <div class="file-upload-wrapper" id="uploadWrapper">
                                    <input type="file" name="meat_part_photo" id="meat_part_photo" required accept=".jpg, .jpeg, .png"
                                           class="file-upload-input">
                                    <div class="file-upload-text">
                                        <div class="mb-2 text-gray-400">
                                            <i class="fas fa-cloud-upload-alt text-3xl"></i>
                                        </div>
                                        <p class="text-sm font-medium text-gray-500">Drag & drop your image or click to browse</p>
                                        <p class="text-xs text-gray-400 mt-1">Supports JPG, JPEG, PNG</p>
                                    </div>
                                    <img id="imagePreview" class="preview-image hidden" />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Quantity Available -->
                                <div>
                                    <label for="qty_available" class="block text-sm font-medium text-gray-700 mb-2">Quantity Available</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-cubes text-gray-400"></i>
                                        </div>
                                        <input type="number" name="qty_available" id="qty_available" required min="75" max="400"
                                            placeholder="75-400" oninput="checkQty()"
                                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    <p id="qty-error" class="mt-1 text-sm text-red-600 hidden">Quantity must be between 75 and 400.</p>
                                    
                                    <!-- Range Indicator -->
                                    <div class="mt-2">
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span>Min: 75</span>
                                            <span>Max: 400</span>
                                        </div>
                                        <div class="h-2 bg-gray-200 rounded mt-1 overflow-hidden">
                                            <div id="qty-range-indicator" class="h-full bg-green-500" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Unit of Measure -->
                                <div>
                                    <label for="unit_of_measure" class="block text-sm font-medium text-gray-700 mb-2">Unit of Measure</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-balance-scale text-gray-400"></i>
                                        </div>
                                        <select name="unit_of_measure" id="unit_of_measure" required 
                                                class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 appearance-none">
                                            <option value="kg">Kg</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Unit Price -->
                                <div>
                                    <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-2">Unit Price</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">₱</span>
                                        </div>
                                        <input type="number" step="0.01" name="unit_price" id="unit_price" required min="1"
                                               placeholder="0.00" oninput="checkPrice()"
                                               class="block w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    <p id="price-error" class="mt-1 text-sm text-red-600 hidden">Unit price must be greater than 1.</p>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="pt-4">
                                <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-lg hover:bg-red-700 transition duration-300 flex items-center justify-center space-x-2 font-medium">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Add Product</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Help Section -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Adding Products</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 h-7 w-7 rounded-full bg-red-100 flex items-center justify-center">
                                    <span class="text-red-600 text-sm font-medium">1</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700">Select meat category</h4>
                                    <p class="text-xs text-gray-500">Choose the correct category for proper classification</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 h-7 w-7 rounded-full bg-red-100 flex items-center justify-center">
                                    <span class="text-red-600 text-sm font-medium">2</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700">Select a meat part name</h4>
                                    <p class="text-xs text-gray-500">Choose from the available options or add a custom part name</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 h-7 w-7 rounded-full bg-red-100 flex items-center justify-center">
                                    <span class="text-red-600 text-sm font-medium">3</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700">Upload a high-quality image</h4>
                                    <p class="text-xs text-gray-500">Clear, well-lit photos increase chances of sale</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 h-7 w-7 rounded-full bg-red-100 flex items-center justify-center">
                                    <span class="text-red-600 text-sm font-medium">4</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700">Set accurate quantity & price</h4>
                                    <p class="text-xs text-gray-500">Ensure quantity and pricing are correct</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 bg-blue-50 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 text-blue-500">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-blue-800">Need help?</h4>
                                    <p class="text-xs text-blue-600">For more details on product management, check the <a href="#" class="underline">admin guide</a>.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('meat_part_photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            const wrapper = document.getElementById('uploadWrapper');
            const preview = document.getElementById('imagePreview');
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                
                // Change the wrapper style when there's an image
                const uploadText = wrapper.querySelector('.file-upload-text');
                uploadText.style.opacity = '0';
            }
            
            reader.readAsDataURL(file);
        });
        
        function checkQty() {
            var qty = document.getElementById('qty_available').value;
            var error = document.getElementById('qty-error');
            var input = document.getElementById('qty_available');
            var indicator = document.getElementById('qty-range-indicator');
            
            // Parse as integer
            qty = parseInt(qty);
            
            if (isNaN(qty) || qty < 75 || qty > 400) {
                error.classList.remove('hidden');
                input.classList.add('border-red-500');
                
                // Update indicator - set to empty if invalid
                indicator.style.width = '0%';
                indicator.style.backgroundColor = '#ef4444'; // red
                
                return false;
            } else {
                error.classList.add('hidden');
                input.classList.remove('border-red-500');
                
                // Update indicator - show position in range
                const percentage = ((qty - 75) / (400 - 75)) * 100;
                indicator.style.width = percentage + '%';
                
                // Change color based on value (optional)
                if (percentage < 25) {
                    indicator.style.backgroundColor = '#eab308'; // yellow
                } else if (percentage > 75) {
                    indicator.style.backgroundColor = '#f97316'; // orange
                } else {
                    indicator.style.backgroundColor = '#22c55e'; // green
                }
                
                return true;
            }
        }
        
        function checkPrice() {
            var price = document.getElementById('unit_price').value;
            var error = document.getElementById('price-error');
            if (price < 1 || price === '' || price === '-0') {
                error.classList.remove('hidden');
                return false;
            } else {
                error.classList.add('hidden');
                return true;
            }
        }
        
        function validateQty() {
            var qtyValid = checkQty();
            var priceValid = checkPrice();
            if (qtyValid && priceValid) {
                return true;
            } else {
                return false;
            }
        }
        
        // Close modal function
        function closeModal() {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.classList.add('opacity-0');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        }
        
        // Show modal on page load if it exists
        document.addEventListener('DOMContentLoaded', function() {
            const successModal = document.getElementById('successModal');
            if (successModal) {
                successModal.style.display = 'flex';
            }
        });
        
        // Meat parts data organized by category ID
        const meatParts = {
            "1": [ // Beef
                "Beef Strips", "Briskets", "Cow Chucks", "Cube Beefs", "Flank Steaks", 
                "Rib Eye Steaks", "Rib Steak", "Serloin Beef", "T-Bone Steak"
            ],
            "2": [ // Pork
                "Bacon", "Crown Roast", "Ground Pork", "Lloin Cubes", "Pork Butt", 
                "Pork Chops", "Pork Ribs", "Pork Shanks", "Pork Belly"
            ],
            "3": [ // Chicken
                "Breast", "Chick Livers", "Chicken Feet", "Chicken Neck", "Drums", 
                "Quarters", "Tenders", "Thighs", "Wings"
            ]
        };
        
        // Variable to store existing meat parts from database
        let existingMeatParts = [];
        
        // Fetch existing meat parts when page loads
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const response = await fetch('get_existing_meat_parts.php');
                existingMeatParts = await response.json();
            } catch (error) {
                console.error('Failed to fetch existing meat parts:', error);
                // If fetch fails, just proceed with empty array
                existingMeatParts = [];
            }
            
            // Initialize other page elements
            const successModal = document.getElementById('successModal');
            if (successModal) {
                successModal.style.display = 'flex';
            }
        });
        
        // Update meat part dropdown options when meat category changes
        document.getElementById('meat_category').addEventListener('change', function() {
            const categoryId = this.value;
            const meatPartDropdown = document.getElementById('meat_part_name');
            const container = document.getElementById('meat_part_container');
            
            // Remove any custom input if it exists
            const customInput = document.getElementById('meat_part_name_custom');
            const backBtn = container.querySelector('button');
            if (customInput) container.removeChild(customInput);
            if (backBtn) container.removeChild(backBtn);
            
            // Show dropdown again if it was hidden
            meatPartDropdown.style.display = '';
            
            // Make sure the icon and chevron elements exist
            let iconElement = container.querySelector('.absolute.inset-y-0.left-0.pl-3');
            let chevronElement = container.querySelector('.absolute.inset-y-0.right-0');
            
            // Create them if they don't exist
            if (!iconElement) {
                iconElement = document.createElement('div');
                iconElement.className = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none';
                iconElement.innerHTML = '<i class="fas fa-tag text-gray-400"></i>';
                container.appendChild(iconElement);
            }
            
            if (!chevronElement) {
                chevronElement = document.createElement('div');
                chevronElement.className = 'absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none';
                chevronElement.innerHTML = '<i class="fas fa-chevron-down text-gray-400"></i>';
                container.appendChild(chevronElement);
            }
            
            // Clear previous options
            meatPartDropdown.innerHTML = '<option value="" disabled selected>Select meat part</option>';
            
            if (!categoryId) return;
            
            // Get the meat parts for selected category
            const partsForCategory = meatParts[categoryId] || [];
            
            // Add options to dropdown
            partsForCategory.forEach(part => {
                const option = document.createElement('option');
                option.value = part;
                
                // Check if this part already exists in the database for this category
                const exists = existingMeatParts.some(existingPart => {
                    // More robust comparison - normalize both strings by removing extra spaces and case
                    const normalizedExisting = existingPart.name.toLowerCase().trim();
                    const normalizedPart = part.toLowerCase().trim();
                    
                    // For Cube Beefs vs Cubed Beefs special case, check for partial match
                    if (normalizedExisting.includes('cube') && normalizedPart.includes('cube') &&
                        existingPart.category_id == categoryId) {
                        return true;
                    }
                    
                    // For Pork Belly vs Porks Belly special case
                    if ((normalizedExisting.includes('pork') && normalizedExisting.includes('belly')) && 
                        (normalizedPart.includes('pork') && normalizedPart.includes('belly')) &&
                        existingPart.category_id == categoryId) {
                        return true;
                    }
                    
                    // Regular comparison
                    return (normalizedExisting === normalizedPart && 
                        existingPart.category_id == categoryId);
                });
                
                if (exists) {
                    option.disabled = true;
                    option.textContent = `${part} (ALREADY EXISTS)`;
                    option.style.color = 'red';
                    option.style.fontWeight = 'bold';
                    option.style.backgroundColor = '#fee2e2';
                } else {
                    option.textContent = part;
                }
                
                meatPartDropdown.appendChild(option);
            });
            
            // Add a custom option at the end
            const customOption = document.createElement('option');
            customOption.value = "custom";
            customOption.textContent = "➕ Add custom meat part";
            meatPartDropdown.appendChild(customOption);
            
            // Add notification about existing parts
            const existingPartsForCategory = existingMeatParts.filter(
                part => part.category_id == categoryId
            );
            
            if (existingPartsForCategory.length > 0) {
                // Check if notification already exists
                let notification = document.getElementById('meat-part-notification');
                if (notification) {
                    notification.remove(); // Remove existing notification
                }
                
                // Create new notification
                notification = document.createElement('div');
                notification.id = 'meat-part-notification';
                notification.className = 'mt-2 text-sm font-medium text-red-600 bg-red-50 p-2 rounded border border-red-200';
                notification.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span>Note: ${existingPartsForCategory.length} meat part(s) in this category already exist:</span>
                    </div>
                    <ul class="mt-1 ml-6 list-disc text-xs">
                        ${existingPartsForCategory.map(part => 
                            `<li>${part.name}</li>`
                        ).join('')}
                    </ul>
                `;
                container.appendChild(notification);
            }
        });
        
        // Handle custom meat part selection
        document.getElementById('meat_part_name').addEventListener('change', function() {
            if (this.value === 'custom') {
                // Get container reference
                const container = document.getElementById('meat_part_container');
                
                // Replace the dropdown with a text input
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'meat_part_name_custom';
                input.id = 'meat_part_name_custom';
                input.required = true;
                input.placeholder = 'Enter custom meat part name';
                input.className = 'block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500';
                
                // Add icon div
                const iconDiv = document.createElement('div');
                iconDiv.className = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none';
                iconDiv.innerHTML = '<i class="fas fa-tag text-gray-400"></i>';
                
                // Hide the dropdown and insert the input
                this.style.display = 'none';
                container.appendChild(input);
                container.appendChild(iconDiv);
                input.focus();
                
                // Add a small "back" button
                const backBtn = document.createElement('button');
                backBtn.type = 'button';
                backBtn.className = 'absolute right-3 top-1/2 transform -translate-y-1/2 text-xs text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded';
                backBtn.innerHTML = '<i class="fas fa-arrow-left mr-1"></i> Back';
                backBtn.onclick = function(e) {
                    e.preventDefault();
                    container.removeChild(input);
                    container.removeChild(iconDiv);
                    container.removeChild(backBtn);
                    document.getElementById('meat_part_name').style.display = '';
                    document.getElementById('meat_part_name').selectedIndex = 0;
                };
                container.appendChild(backBtn);
            }
        });
    </script>
</body>
</html>