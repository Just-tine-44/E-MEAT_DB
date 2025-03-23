<?php
include '../connection/config.php';
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure the user is logged in and store user_id
$username = $_SESSION['username'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// Fetch products from the database using enhanced stored procedure
$category_id = null; // Or get from request for filtering
$search_term = null; // Or get from search input
$in_stock_only = false; // Or true to show only in-stock items

$stmt = $conn->prepare("CALL GetAllProducts(?, ?, ?)");
$stmt->bind_param("isi", $category_id, $search_term, $in_stock_only);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Clear result set
$conn->next_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EMEAT</title>
  <link rel="icon" type="image" href="../IMAGES/RED LOGO.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.5.0/remixicon.css">
  <link rel="stylesheet" href="../CCS/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../CCS/tailwind.min.css">

  <!-- SweetAlert2 CSS and JS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<header>
    <nav class="nav container">
      <a href="#" class="nav__logo">
        <img src="../IMAGES/WHITE LOGO.png" alt="Emeat Logo" class="nav__logo-img">
        EMEAT
      </a>
      <ul class="nav__menu">
        <li><a href="#home">Home</a></li>
        <li><a href="#feature">About</a></li>
        <li><a href="#shop">Shop</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
      <div class="nav-icons">
        <a href="order_confirmation.php" class="order-confirmation-button">
          <i class="ri-file-list-line"></i> Your Order
        </a>
        <a href="#shop"><i class="ri-search-line"></i></a>
        <div class="user-dropdown">
          <i class="ri-user-line user-icon"></i>
          <div class="dropdown-content">
            <a href="#">Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong></a>
            <a href="../back_process/logout.php">Log Out</a>
          </div>
        </div>
        <a href="cart.php" class="cart-icon-container">
            <i class="ri-shopping-cart-fill"></i>
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
            <?php endif; ?>
        </a>
      </div>
    </nav>
  </header>
  <main>
      <!--  -->
      <section class="hero scroll-animate" id="home">
        <h1>EMEAT</h1>
        <p>PREMIUM MEATS, JUST A CLICK AWAY.</p>
        <div class="button-container">
            <a href="#shop" class="custom-btn">
              <span class="icon-circle">
                <i class="ri-arrow-right-line"></i>
              </span>
              Browse
            </a>
            <a href="#feature" class="custom-btn">
              Learn More <span class="plus-icon">+</span>
            </a>
          </div>
      </section>
      <section class="features scroll-animate" id="feature">
        <div class="feature-item">
          <i class="ri-restaurant-2-line"></i> <!-- You can replace with any relevant icon -->
          <h3>Only organic nutrition</h3>
          <p>High-quality organic nutrition for your meats.</p>
        </div>
        <div class="feature-item">
          <i class="ri-archive-2-line"></i> <!-- Icon for packaging -->
          <h3>Convenient vacuum packaging</h3>
          <p>Fresh meat, vacuum-sealed for convenience.</p>
        </div>
        <div class="feature-item">
          <i class="ri-award-line"></i> <!-- Icon for certificates -->
          <h3>International quality certifications</h3>
          <p>Our meats meet international quality standards.</p>
        </div>
        <div class="feature-item">
          <i class="ri-knife-line"></i> <!-- Icon for meat cutting -->
          <h3>Professional meat cutting</h3>
          <p>Precision cutting for your perfect meal.</p>
        </div>
      </section>
      <section class="about scroll-animate" id="feature">
        <div class="about-container">
          <h3>About</h3>
          <h2>ABOUT EMEAT</h2>
          <p>
            At EMEAT, we take pride in being your trusted source for high-quality, fresh, and sustainably sourced meats. Our mission is to provide a seamless online shopping experience, ensuring that you get premium cuts delivered straight to your doorstep. Whether you’re cooking for a family meal, hosting a special occasion, or running a business, we offer a diverse selection of beef, pork, and chicken products to meet your needs. With a commitment to exceptional customer service, affordability, and safety, EMEAT is not just about selling meat—it’s about creating moments that matter, one meal at a time.
          <div class="button-container">
            <a href="#contact" class="custom-btn">
              </span>
              Contact us +
            </a>
        </div>
      </section>  

      <section class="our-story scroll-animate py-10" style="background-color: #733D3D;">
        <div class="container mx-auto px-6 max-w-5xl">
          <div class="story-content text-center">
        <h2 class="text-4xl font-bold mb-6" style="color: #F7F0E2;">OUR STORY</h2>
        <div class="w-24 h-1 mx-auto mb-8" style="background-color: #F7F0E2;"></div>
        <p class="text-lg max-w-3xl mx-auto leading-relaxed mb-12" style="color: #F7F0E2;">
          Founded with a passion for quality meats, EMEAT started as a small family butcher shop committed to 
          delivering the finest cuts to local families. Today, we've embraced technology to bring that same
          dedication to customers nationwide, never compromising on our standards of excellence and freshness.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
          <div class="p-6 bg-white rounded-lg shadow-sm transition-all duration-300 hover:shadow-md hover:transform hover:scale-105">
            <i class="ri-heart-line text-4xl mb-4" style="color: #733D3D;"></i>
            <span class="block text-xl font-semibold text-gray-800">Passion for Quality</span>
          </div>
          <div class="p-6 bg-white rounded-lg shadow-sm transition-all duration-300 hover:shadow-md hover:transform hover:scale-105">
            <i class="ri-truck-line text-4xl mb-4" style="color: #733D3D;"></i>
            <span class="block text-xl font-semibold text-gray-800">Farm to Table</span>
          </div>
          <div class="p-6 bg-white rounded-lg shadow-sm transition-all duration-300 hover:shadow-md hover:transform hover:scale-105">
            <i class="ri-shield-check-line text-4xl mb-4" style="color: #733D3D;"></i>
            <span class="block text-xl font-semibold text-gray-800">100% Satisfaction</span>
          </div>
        </div>
          </div>
        </div>
      </section>
              
    <section class="select-meat-category scroll-animate" id="shop">
      <div class="container">
          <h2>SELECT MEAT CATEGORY</h2>
          <div class="search-bar">
              <input type="text" placeholder="Search meat...">
              <button><i class="ri-search-line"></i></button>
          </div>
          <div class="meat-categories">
              <div class="category-item" data-category="pork">
                  <img src="../IMAGES/MEATS/Logo/PIGGY.png" alt="Pork">
                  <h3>PORK</h3>
              </div>
              <div class="category-item" data-category="beef">
                  <img src="../IMAGES/MEATS/Logo/COW.png" alt="Beef">
                  <h3>BEEF</h3>
              </div>
              <div class="category-item" data-category="chicken">
                  <img src="../IMAGES/MEATS/Logo/CHICK.png" alt="Chicken">
                  <h3>CHICKEN</h3>
              </div>
          </div>
            <div class="product-grid">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $image_path = '../website/IMAGES/MEATS/' . $row['MEAT_PART_PHOTO'];
                        if (!file_exists($image_path)) {
                            $image_path = '../website/IMAGES/default-meat.jpg';
                        }
                        ?>
                        <div class="product-item <?php echo strtolower($row['category']); ?>">
                            <div class="product-image">
                                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($row['MEAT_PART_NAME']); ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($row['MEAT_PART_NAME']); ?></h3>
                                <p>₱ <?php echo number_format($row['UNIT_PRICE'], 2); ?>/<?php echo htmlspecialchars($row['UNIT_OF_MEASURE']); ?></p>

                                <div class="my-6"></div>

                                <!-- ✅ Stock Availability -->
                                    <p class="text-sm <?php echo ($row['QTY_AVAILABLE'] > 0) ? 'text-green-600' : 'text-red-600 font-bold'; ?>">
                                        <?php
                                        if ($row['QTY_AVAILABLE'] > 0) {
                                            echo "In Stock: " . number_format($row['QTY_AVAILABLE']) . " " . htmlspecialchars($row['UNIT_OF_MEASURE']);
                                        } else {
                                            echo "Out of Stock";
                                        }
                                        ?>
                                    </p>
                                
                                <div class="mb-6"></div>

                                    <div class="weight-options">
                                    <label>Select Quantity:</label>
                                    <input type="number" placeholder="QTY" class="quantity" min="1" max="<?php echo $row['QTY_AVAILABLE']; ?>">
                                    <select class="unit">
                                        <option value="kg">Kg</option>
                                        <option value="g">Grams</option>
                                    </select>
                                    </div>

                                    
                                <button class="add-to-cart" data-id="<?php echo $row['MEAT_PART_ID']; ?>" 
                                    <?php echo ($row['QTY_AVAILABLE'] == 0) ? 'disabled' : ''; ?>>
                                    <i class="ri-shopping-cart-fill"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p>No products available</p>";
                }
                ?>
            </div>
      </div>
    </section>
  </main>
 <!--  -->
<footer class="footer" id="contact">
    <div class="footer-container">
      <div class="footer-section">
        <h3>EMEAT</h3>
        <p>Premium quality meats delivered to your doorstep.</p>
      </div>
      <div class="footer-section">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="#home">Home</a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#shop">Shop</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h4>Contact Us</h4>
        <p>Email: contact@emeat.com</p>
        <p>Phone: +123-456-7890</p>
      </div>
      <div class="footer-section">
        <h4>Follow Us</h4>
        <div class="social-links">
          <a href="#"><i class="ri-facebook-circle-line"></i></a>
          <a href="#"><i class="ri-instagram-line"></i></a>
          <a href="#"><i class="ri-twitter-line"></i></a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 EMEAT. All Rights Reserved.</p>
    </div>
</footer>  
  <script src="../JAVASCRIPT/main.js"></script>
</body>
</html>

<script>
  document.addEventListener("DOMContentLoaded", function () {
      document.querySelectorAll('.quantity').forEach(function (input) {
          let typingTimer; // Timer variable

          input.addEventListener('input', function () {
              clearTimeout(typingTimer); // Clear previous timer

              typingTimer = setTimeout(() => {
                  var unitSelect = this.closest('.weight-options').querySelector('.unit');
                  var unit = unitSelect.value;
                  var value = parseFloat(this.value);

                  if (unit === 'g') {
                      if (value < 100 || value > 950) {
                          Swal.fire({
                              title: 'Invalid Input',
                              text: "Grams must be between 100g and 950g.",
                              icon: 'warning',
                              confirmButtonColor: '#3085d6',
                              confirmButtonText: 'OK'
                          });
                          this.value = ''; // Clear input
                      }
                  } else if (unit === 'kg') {
                      if (value <= 0) {
                          Swal.fire({
                              title: 'Invalid Input',
                              text: "Kilograms must be greater than 0.",
                              icon: 'warning',
                              confirmButtonColor: '#3085d6',
                              confirmButtonText: 'OK'
                          });
                          this.value = ''; // Clear input
                      }
                  }
              }, 3000); // ✅ Delay validation by 3000ms to wait for user to finish typing
          });

          // ✅ Handle unit change dynamically
          var unitSelect = input.closest('.weight-options').querySelector('.unit');
          unitSelect.addEventListener('change', function () {
              input.value = ''; // Clear the input when unit changes
          });
      });
  });
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Remove any existing event listeners
    const addToCartButtons = document.querySelectorAll(".add-to-cart");
    
    addToCartButtons.forEach(button => {
        // Remove old listeners
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        // Add fresh event listener
        newButton.addEventListener("click", function (e) {
            e.preventDefault();
            const productItem = this.closest(".product-item");
            const meatPartId = this.getAttribute("data-id");
            const qtyInput = productItem.querySelector(".quantity");
            const unitSelect = productItem.querySelector(".unit");
            const availableStock = parseFloat(qtyInput.getAttribute("max"));
            const stockUnit = qtyInput.getAttribute("data-stock-unit") || "kg"; // Assuming stock is stored in kg by default

            if (!qtyInput.value.trim()) {
                Swal.fire({
                    title: 'Missing Information',
                    text: "Please enter a quantity.",
                    icon: 'info',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const qty = parseFloat(qtyInput.value);
            const unit = unitSelect.value;

            if (isNaN(qty) || qty <= 0) {
                Swal.fire({
                    title: 'Invalid Input',
                    text: "Please enter a valid quantity.",
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Convert input quantity to the same unit as stock for comparison
            let qtyInStockUnit;
            if (unit === 'g' && stockUnit === 'kg') {
                qtyInStockUnit = qty / 1000; // Convert grams to kg
            } else if (unit === 'kg' && stockUnit === 'g') {
                qtyInStockUnit = qty * 1000; // Convert kg to grams
            } else {
                qtyInStockUnit = qty; // Same units, no conversion needed
            }

            if (qtyInStockUnit > availableStock) {
                // Calculate max in current unit for display
                let maxInCurrentUnit;
                if (unit === 'g' && stockUnit === 'kg') {
                    maxInCurrentUnit = availableStock * 1000;
                } else if (unit === 'kg' && stockUnit === 'g') {
                    maxInCurrentUnit = availableStock / 1000;
                } else {
                    maxInCurrentUnit = availableStock;
                }
                
                Swal.fire({
                    title: 'Stock Limit Exceeded',
                    text: `Not enough stock available! Maximum available: ${maxInCurrentUnit.toFixed(unit === 'g' ? 0 : 2)} ${unit}`,
                    icon: 'error',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                
                qtyInput.value = maxInCurrentUnit.toFixed(unit === 'g' ? 0 : 2);
                return;
            }

            console.log("Sending:", { meatPartId, qty, unit });

            fetch("../back_process/add_to_cart.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `meat_part_id=${meatPartId}&qty=${qty}&unit=${unit}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.text();
            })
            .then(text => {
                console.log("Response text:", text);
                try {
                    const data = JSON.parse(text);
                    
                    if (data.success) {
                        // Update cart count in the navbar
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                            cartCount.style.display = "flex";
                        } else {
                            const cartIcon = document.querySelector('.cart-icon-container');
                            if (cartIcon) {
                                const countElement = document.createElement('span');
                                countElement.className = 'cart-count';
                                countElement.textContent = data.cart_count;
                                countElement.style.display = "flex";
                                cartIcon.appendChild(countElement);
                            }
                        }
                        
                        // Clear input after successful add
                        qtyInput.value = '';
                        
                        // Show success message with SweetAlert
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || "Error adding item to cart",
                            icon: 'error',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    }
                } catch (error) {
                    console.error("Error parsing JSON:", error, "Raw text:", text);
                    Swal.fire({
                        title: 'Processing Error',
                        text: "An error occurred while processing your request.",
                        icon: 'error',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                Swal.fire({
                    title: 'Network Error',
                    text: "An error occurred while processing your request: " + error.message,
                    icon: 'error',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            });
        });
    });
});
</script>