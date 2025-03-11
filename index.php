<?php
include 'config.php';
session_start();

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
  <link rel="icon" type="image" href="../website/IMAGES/RED LOGO.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.5.0/remixicon.css">
  <link rel="stylesheet" href="../website/CCS/style.css?v=<?php echo time(); ?>">
</head>
<body>
<header>
    <nav class="nav container">
      <a href="#" class="nav__logo">
        <img src="../website/IMAGES/WHITE LOGO.png" alt="Emeat Logo" class="nav__logo-img">
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
            <a href="logout.php">Log Out</a>
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
                  <img src="../website/IMAGES/MEATS/Logo/PIGGY.png" alt="Pork">
                  <h3>PORK</h3>
              </div>
              <div class="category-item" data-category="beef">
                  <img src="../website/IMAGES/MEATS/Logo/COW.png" alt="Beef">
                  <h3>BEEF</h3>
              </div>
              <div class="category-item" data-category="chicken">
                  <img src="../website/IMAGES/MEATS/Logo/CHICK.png" alt="Chicken">
                  <h3>CHICKEN</h3>
              </div>
          </div>
            <div class="product-grid">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $image_path = 'website/IMAGES/MEATS/' . $row['MEAT_PART_PHOTO'];
                    if (!file_exists($image_path)) {
                        $image_path = 'website/IMAGES/default-meat.jpg';
                    }
                    ?>
                    <div class="product-item <?php echo strtolower($row['category']); ?>">
                        <div class="product-image">
                            <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($row['MEAT_PART_NAME']); ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($row['MEAT_PART_NAME']); ?></h3>
                            <p>₱ <?php echo number_format($row['UNIT_PRICE'], 2); ?>/<?php echo htmlspecialchars($row['UNIT_OF_MEASURE']); ?></p>

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
  <script src="../website/JAVASCRIPT/main.js"></script>
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
let eventListenersAttached = false;

document.addEventListener("DOMContentLoaded", function () {
    const addToCartButtons = document.querySelectorAll(".add-to-cart");

    addToCartButtons.forEach(button => {
        button.addEventListener("click", function (e) {
            const productItem = this.closest(".product-item");
            const meatPartId = this.getAttribute("data-id");
            const qtyInput = productItem.querySelector(".quantity");
            const unitSelect = productItem.querySelector(".unit");
            const availableStock = parseFloat(qtyInput.getAttribute("max")); // Get the max stock value

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

            // Ensure user input does not exceed available stock
            if (qty > availableStock) {
                alert(`Not enough stock available! Maximum available: ${availableStock}`);
                qtyInput.value = availableStock; // Reset to the maximum available stock
                return;
            }

            console.log("Sending:", { meatPartId, qty, unit });

            fetch("add_to_cart.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `meat_part_id=${meatPartId}&qty=${qty}&unit=${unit}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                console.log("Response text:", text);
                try {
                    const data = JSON.parse(text);
                    
                    // Update cart count in the navbar
                    if (data.success) {
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                        } else {
                            const cartIcon = document.querySelector('.cart-icon-container');
                            if (cartIcon) {
                                const countElement = document.createElement('span');
                                countElement.className = 'cart-count';
                                countElement.textContent = data.cart_count;
                                cartIcon.appendChild(countElement);
                            }
                        }
                    }
                    
                    alert(data.message);
                } catch (error) {
                    console.error("Error parsing JSON:", error);
                    alert("An error occurred while processing your request.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while processing your request.");
            });
        });
    });
});
</script>