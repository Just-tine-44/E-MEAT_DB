<?php
include 'config.php';
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
  <link rel="icon" type="image" href="../website/IMAGES/RED LOGO.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.5.0/remixicon.css">
  <link rel="stylesheet" href="../website/CCS/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../website/CCS/tailwind.min.css">
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
        <!-- Team Section -->
    <section id="team" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- Section Header -->
        <div class="text-center mb-16">
        <span class="inline-block px-4 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold">OUR TEAM</span>
        <h2 class="text-4xl font-bold text-gray-900 mt-4">MEET OUR TEAM</h2>
        <p class="mt-4 text-gray-600 max-w-2xl mx-auto">
            The passionate professionals behind E-MEAT dedicated to bringing quality products to your table.
        </p>
        <div class="w-24 h-1 bg-red-600 mx-auto mt-6"></div>
        </div>
        
        <!-- Team Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8">
        <!-- Team Member 1 -->
        <div class="group">
            <div class="relative overflow-hidden rounded-lg shadow-lg transform transition-transform duration-500 hover:-translate-y-2">
            <!-- Profile Image with Overlay -->
            <div class="relative">
                <img 
                src="../website/IMAGES/PROFILES/RONIN.jpg" 
                alt="Ronin Cabusao" 
                class="w-full h-64 object-cover object-center transition-transform duration-500 group-hover:scale-110"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-end p-4">
                <!-- Social Media Icons -->
                <div class="flex space-x-3 mb-4 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-facebook-fill"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-instagram-line"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-github-fill"></i>
                    </a>
                </div>
                </div>
            </div>
            
            <!-- Info Card that slides up -->
            <div class="absolute bottom-0 left-0 right-0 bg-white p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                <div class="text-center">
                <h3 class="font-bold text-lg text-gray-900">Ronin Cabusao</h3>
                <p class="text-red-600 text-sm mt-1">Developer</p>
                </div>
            </div>
            </div>
            
            <!-- Name Badge - Visible by Default -->
            <div class="bg-white rounded-lg shadow-md p-3 text-center -mt-6 relative z-10 transition-transform duration-300 group-hover:opacity-0">
            <h3 class="font-bold text-gray-900">Ronin Cabusao</h3>
            <p class="text-red-600 text-sm">Developer</p>
            </div>
        </div>
        
        <!-- Team Member 2 -->
        <div class="group">
            <div class="relative overflow-hidden rounded-lg shadow-lg transform transition-transform duration-500 hover:-translate-y-2">
            <!-- Profile Image with Overlay -->
            <div class="relative">
                <img 
                src="../website/IMAGES/PROFILES/VINCE.jpg" 
                alt="Vince Bryant Cabunilas" 
                class="w-full h-64 object-cover object-center transition-transform duration-500 group-hover:scale-110"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-end p-4">
                <!-- Social Media Icons -->
                <div class="flex space-x-3 mb-4 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-facebook-fill"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-instagram-line"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-github-fill"></i>
                    </a>
                </div>
                </div>
            </div>
            
            <!-- Info Card that slides up -->
            <div class="absolute bottom-0 left-0 right-0 bg-white p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                <div class="text-center">
                <h3 class="font-bold text-lg text-gray-900">Vince Bryant Cabunilas</h3>
                <p class="text-red-600 text-sm mt-1">Developer</p>
                </div>
            </div>
            </div>
            
            <!-- Name Badge - Visible by Default -->
            <div class="bg-white rounded-lg shadow-md p-3 text-center -mt-6 relative z-10 transition-transform duration-300 group-hover:opacity-0">
            <h3 class="font-bold text-gray-900">Vince Bryant Cabunilas</h3>
            <p class="text-red-600 text-sm">Developer</p>
            </div>
        </div>
        
        <!-- Team Member 3 -->
        <div class="group">
            <div class="relative overflow-hidden rounded-lg shadow-lg transform transition-transform duration-500 hover:-translate-y-2">
            <!-- Profile Image with Overlay -->
            <div class="relative">
                <img 
                src="../website/IMAGES/PROFILES/ERICA.jpg" 
                alt="Erica Juarez" 
                class="w-full h-64 object-cover object-center transition-transform duration-500 group-hover:scale-110"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-end p-4">
                <!-- Social Media Icons -->
                <div class="flex space-x-3 mb-4 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-facebook-fill"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-instagram-line"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-github-fill"></i>
                    </a>
                </div>
                </div>
            </div>
            
            <!-- Info Card that slides up -->
            <div class="absolute bottom-0 left-0 right-0 bg-white p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                <div class="text-center">
                <h3 class="font-bold text-lg text-gray-900">Erica Juarez</h3>
                <p class="text-red-600 text-sm mt-1">Developer</p>
                </div>
            </div>
            </div>
            
            <!-- Name Badge - Visible by Default -->
            <div class="bg-white rounded-lg shadow-md p-3 text-center -mt-6 relative z-10 transition-transform duration-300 group-hover:opacity-0">
            <h3 class="font-bold text-gray-900">Erica Juarez</h3>
            <p class="text-red-600 text-sm">Developer</p>
            </div>
        </div>
        
        <!-- Team Member 4 -->
        <div class="group">
            <div class="relative overflow-hidden rounded-lg shadow-lg transform transition-transform duration-500 hover:-translate-y-2">
            <!-- Profile Image with Overlay -->
            <div class="relative">
                <img 
                src="../website/IMAGES/PROFILES/JUSTINE.jpg" 
                alt="Justine Paraiso" 
                class="w-full h-64 object-cover object-center transition-transform duration-500 group-hover:scale-110"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-end p-4">
                <!-- Social Media Icons -->
                <div class="flex space-x-3 mb-4 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-facebook-fill"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-instagram-line"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-github-fill"></i>
                    </a>
                </div>
                </div>
            </div>
            
            <!-- Info Card that slides up -->
            <div class="absolute bottom-0 left-0 right-0 bg-white p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                <div class="text-center">
                <h3 class="font-bold text-lg text-gray-900">Justine Paraiso</h3>
                <p class="text-red-600 text-sm mt-1">Developer</p>
                </div>
            </div>
            </div>
            
            <!-- Name Badge - Visible by Default -->
            <div class="bg-white rounded-lg shadow-md p-3 text-center -mt-6 relative z-10 transition-transform duration-300 group-hover:opacity-0">
            <h3 class="font-bold text-gray-900">Justine Paraiso</h3>
            <p class="text-red-600 text-sm">Developer</p>
            </div>
        </div>
        
        <!-- Team Member 5 -->
        <div class="group">
            <div class="relative overflow-hidden rounded-lg shadow-lg transform transition-transform duration-500 hover:-translate-y-2">
            <!-- Profile Image with Overlay -->
            <div class="relative">
                <img 
                src="../website/IMAGES/PROFILES/LEYZEL.jpg" 
                alt="Liezel Tumbaga" 
                class="w-full h-64 object-cover object-center transition-transform duration-500 group-hover:scale-110"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-end p-4">
                <!-- Social Media Icons -->
                <div class="flex space-x-3 mb-4 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-facebook-fill"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-instagram-line"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/20 hover:bg-red-600 flex items-center justify-center text-white transition-colors">
                    <i class="ri-github-fill"></i>
                    </a>
                </div>
                </div>
            </div>
            
            <!-- Info Card that slides up -->
            <div class="absolute bottom-0 left-0 right-0 bg-white p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                <div class="text-center">
                <h3 class="font-bold text-lg text-gray-900">Liezel Tumbaga</h3>
                <p class="text-red-600 text-sm mt-1">Developer</p>
                </div>
            </div>
            </div>
            
            <!-- Name Badge - Visible by Default -->
            <div class="bg-white rounded-lg shadow-md p-3 text-center -mt-6 relative z-10 transition-transform duration-300 group-hover:opacity-0">
            <h3 class="font-bold text-gray-900">Liezel Tumbaga</h3>
            <p class="text-red-600 text-sm">Developer</p>
            </div>
        </div>
        </div>
        
        <!-- Call to Action -->
        <div class="mt-16 text-center">
        <p class="text-gray-600 mb-6">Interested in joining our team?</p>
        <a href="#contact" class="inline-block px-6 py-3 bg-red-600 text-white font-medium rounded-lg shadow-md hover:bg-red-700 transition-colors">
            Contact Us
        </a>
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

                                <div class="my-4"></div>

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
<!-- Modern Footer -->
<footer id="contact" class="bg-gradient-to-br from-red-900 to-red-800 text-white pt-16 pb-6">
  <div class="container mx-auto px-4">
    <!-- Main Footer Content -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
      <!-- Company Info -->
      <div class="space-y-4">
        <div class="flex items-center space-x-2 mb-4">
          <img src="../website/IMAGES/RED LOGO.png" alt="E-MEAT Logo" class="h-9 w-9 rounded-full bg-white p-1.5">
          <h3 class="text-xl font-bold tracking-wide">E-MEAT</h3>
        </div>
        <p class="text-gray-200 leading-relaxed">
          Premium quality meats delivered fresh to your doorstep. Experience the best cuts with our carefully sourced products.
        </p>
        <div class="pt-4">
          <div class="flex items-center space-x-2">
            <span class="bg-red-700 rounded-full p-1.5">
              <i class="ri-map-pin-line text-white"></i>
            </span>
            <span class="text-gray-200 text-sm">123 Butcher Street, Metro Manila</span>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="mt-8 md:mt-0">
        <h4 class="text-lg font-semibold mb-5 flex items-center">
          <span class="w-8 h-8 bg-red-700 rounded-full flex items-center justify-center mr-2">
            <i class="ri-link text-sm"></i>
          </span>
          Quick Links
        </h4>
        <ul class="space-y-3">
          <li>
            <a href="#home" class="text-gray-200 hover:text-white flex items-center group">
              <i class="ri-arrow-right-s-line mr-2 transition-transform group-hover:translate-x-1"></i>
              <span>Home</span>
            </a>
          </li>
          <li>
            <a href="#feature" class="text-gray-200 hover:text-white flex items-center group">
              <i class="ri-arrow-right-s-line mr-2 transition-transform group-hover:translate-x-1"></i>
              <span>About Us</span>
            </a>
          </li>
          <li>
            <a href="#shop" class="text-gray-200 hover:text-white flex items-center group">
              <i class="ri-arrow-right-s-line mr-2 transition-transform group-hover:translate-x-1"></i>
              <span>Shop</span>
            </a>
          </li>
          <li>
            <a href="#contact" class="text-gray-200 hover:text-white flex items-center group">
              <i class="ri-arrow-right-s-line mr-2 transition-transform group-hover:translate-x-1"></i>
              <span>Contact</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- Contact Us -->
      <div class="mt-8 lg:mt-0">
        <h4 class="text-lg font-semibold mb-5 flex items-center">
          <span class="w-8 h-8 bg-red-700 rounded-full flex items-center justify-center mr-2">
            <i class="ri-customer-service-line text-sm"></i>
          </span>
          Contact Us
        </h4>
        <ul class="space-y-3">
          <li class="flex items-start space-x-3">
            <i class="ri-mail-line mt-1 text-red-300"></i>
            <span class="text-gray-200">contact@emeat.com</span>
          </li>
          <li class="flex items-start space-x-3">
            <i class="ri-phone-line mt-1 text-red-300"></i>
            <span class="text-gray-200">+123-456-7890</span>
          </li>
          <li class="flex items-start space-x-3">
            <i class="ri-time-line mt-1 text-red-300"></i>
            <span class="text-gray-200">Mon-Fri: 9AM - 6PM</span>
          </li>
        </ul>
      </div>

      <!-- Newsletter -->
      <div class="mt-8 lg:mt-0">
        <h4 class="text-lg font-semibold mb-5 flex items-center">
          <span class="w-8 h-8 bg-red-700 rounded-full flex items-center justify-center mr-2">
            <i class="ri-mail-send-line text-sm"></i>
          </span>
          Newsletter
        </h4>
        <p class="text-gray-200 mb-4">Subscribe to receive updates on new products and special offers.</p>
        
        <form class="space-y-3">
          <div class="relative">
            <input 
              type="email" 
              placeholder="Your email address" 
              class="w-full bg-red-950 bg-opacity-50 border border-red-700 rounded-lg py-2 px-4 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500"
              required
            >
          </div>
          <button 
            type="submit" 
            class="w-full bg-red-700 hover:bg-red-600 rounded-lg py-2 px-4 text-white font-medium transition duration-300 flex items-center justify-center space-x-2"
          >
            <span>Subscribe</span>
            <i class="ri-send-plane-fill"></i>
          </button>
        </form>
      </div>
    </div>

    <!-- Social Links -->
    <div class="flex flex-col md:flex-row justify-between items-center border-t border-red-700 pt-8 pb-4">
      <div class="text-sm text-gray-300 mb-4 md:mb-0">
        &copy; 2025 E-MEAT. All Rights Reserved.
      </div>
      
      <div class="flex space-x-4">
        <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center bg-red-800 hover:bg-red-700 transition-colors duration-300">
          <i class="ri-facebook-fill text-white"></i>
        </a>
        <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center bg-red-800 hover:bg-red-700 transition-colors duration-300">
          <i class="ri-instagram-line text-white"></i>
        </a>
        <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center bg-red-800 hover:bg-red-700 transition-colors duration-300">
          <i class="ri-twitter-fill text-white"></i>
        </a>
        <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center bg-red-800 hover:bg-red-700 transition-colors duration-300">
          <i class="ri-youtube-fill text-white"></i>
        </a>
      </div>
    </div>
    
    <!-- Payment Methods -->
    <div class="flex flex-wrap justify-center space-x-4 mt-6">
      <img src="https://cdn-icons-png.flaticon.com/128/196/196578.png" alt="Visa" class="h-8 w-auto grayscale hover:grayscale-0 transition-all">
      <img src="https://cdn-icons-png.flaticon.com/128/196/196561.png" alt="MasterCard" class="h-8 w-auto grayscale hover:grayscale-0 transition-all">
      <img src="https://cdn-icons-png.flaticon.com/128/196/196565.png" alt="PayPal" class="h-8 w-auto grayscale hover:grayscale-0 transition-all">
      <img src="https://cdn-icons-png.flaticon.com/128/5968/5968299.png" alt="GCash" class="h-8 w-auto grayscale hover:grayscale-0 transition-all">
    </div>
  </div>
</footer>
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