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
                          alert("Grams must be between 100g and 950g.");
                          this.value = ''; // Clear input
                      }
                  } else if (unit === 'kg') {
                      if (value <= 0) {
                          alert("Kilograms must be greater than 0.");
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

            if (!qtyInput.value.trim()) {
                alert("Please enter a quantity.");
                return;
            }

            const qty = parseFloat(qtyInput.value);
            const unit = unitSelect.value;

            if (isNaN(qty) || qty <= 0) {
                alert("Please enter a valid quantity.");
                return;
            }

            if (qty > availableStock) {
                alert(`Not enough stock available! Maximum available: ${availableStock}`);
                qtyInput.value = availableStock;
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
                        
                        // Show success message
                        alert(data.message);
                    } else {
                        alert(data.message || "Error adding item to cart");
                    }
                } catch (error) {
                    console.error("Error parsing JSON:", error, "Raw text:", text);
                    alert("An error occurred while processing your request.");
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                alert("An error occurred while processing your request: " + error.message);
            });
        });
    });
});
</script>