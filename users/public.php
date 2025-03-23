<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-MEAT | Premium Quality Meat Delivery</title>
    <link rel="icon" type="image" href="../IMAGES/RED LOGO.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.5.0/remixicon.css">
    <link rel="stylesheet" href="../CCS/tailwind.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        :root {
            --meat-red: #733d3d;
            --meat-light: #f7f0e2;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }
        
        .hero-section {
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('../IMAGES/hero-meat.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
        }
        
        .custom-btn {
            background-color: var(--meat-red);
            color: white;
            transition: all 0.3s ease;
        }
        
        .custom-btn:hover {
            background-color: #8f4545;
            transform: translateY(-2px);
        }
        
        .meat-category-item {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .meat-category-item:hover {
            transform: translateY(-5px);
        }
        
        .nav__logo-img {
            width: 40px;
            height: 40px;
        }
        
        .testimonial-card {
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .team-member {
            transition: all 0.3s ease;
        }
        
        .team-member:hover {
            transform: translateY(-5px);
        }
        
        .scroll-reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <header class="fixed w-full z-50 bg-transparent transition-all duration-300" id="main-header">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="#" class="flex items-center space-x-2">
                <img src="../IMAGES/WHITE LOGO.png" alt="E-MEAT Logo" class="nav__logo-img">
                <span class="text-white text-2xl font-bold">E-MEAT</span>
            </a>
            
            <nav>
                <ul class="hidden md:flex space-x-8 text-white font-medium">
                    <li><a href="#home" class="hover:text-red-300 transition">Home</a></li>
                    <li><a href="#about" class="hover:text-red-300 transition">About</a></li>
                    <li><a href="#products" class="hover:text-red-300 transition">Products</a></li>
                    <li><a href="#team" class="hover:text-red-300 transition">Our Team</a></li>
                    <li><a href="#testimonials" class="hover:text-red-300 transition">Testimonials</a></li>
                    <li><a href="#contact" class="hover:text-red-300 transition">Contact</a></li>
                </ul>
                
                <button class="md:hidden text-white text-2xl">
                    <i class="ri-menu-line"></i>
                </button>
            </nav>
            
            <div class="hidden md:block">
                <a href="login.php" class="text-white hover:text-red-300 transition mr-6">Login</a>
                <a href="register.php" class="bg-red-700 hover:bg-red-800 text-white py-2 px-6 rounded-full transition">Register</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero-section flex items-center justify-center">
        <div class="container mx-auto px-4 text-center">
            <div class="max-w-3xl mx-auto scroll-reveal">
                <h1 class="text-5xl md:text-6xl font-bold text-white mb-6">Premium Quality Meat Delivered To Your Doorstep</h1>
                <p class="text-xl text-gray-200 mb-10">Experience the finest selection of fresh beef, pork, and chicken cuts with our convenient online delivery service.</p>
                <div class="flex flex-col md:flex-row justify-center space-y-4 md:space-y-0 md:space-x-6">
                    <a href="register.php" class="custom-btn py-3 px-8 rounded-full text-lg font-medium">Shop Now</a>
                    <a href="#about" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 py-3 px-8 rounded-full text-lg font-medium transition">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gray-50 rounded-xl p-8 text-center shadow-lg scroll-reveal">
                    <div class="bg-red-100 w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6">
                        <i class="ri-truck-line text-red-700 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Next-Day Delivery</h3>
                    <p class="text-gray-600">Order by 8 PM for next-day delivery to your doorstep, carefully packed to maintain freshness.</p>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-8 text-center shadow-lg scroll-reveal" style="transition-delay: 0.2s;">
                    <div class="bg-red-100 w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6">
                        <i class="ri-shield-check-line text-red-700 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Quality Guaranteed</h3>
                    <p class="text-gray-600">Our meats meet international quality standards with strict freshness and safety protocols.</p>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-8 text-center shadow-lg scroll-reveal" style="transition-delay: 0.4s;">
                    <div class="bg-red-100 w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6">
                        <i class="ri-knife-line text-red-700 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Professional Cutting</h3>
                    <p class="text-gray-600">Precision cutting by expert butchers ensures perfect portions for your recipes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center mb-16 scroll-reveal">
                <h2 class="text-sm font-bold text-red-700 uppercase tracking-wide mb-2">About Us</h2>
                <h3 class="text-4xl font-bold mb-6">Why Choose E-MEAT?</h3>
                <p class="text-gray-600 leading-relaxed">
                    At E-MEAT, we take pride in being your trusted source for high-quality, fresh, and sustainably sourced meats. Our mission is to provide a seamless online shopping experience, ensuring that you get premium cuts delivered straight to your doorstep. Whether you're cooking for a family meal, hosting a special occasion, or running a business, we offer a diverse selection of beef, pork, and chicken products to meet your needs. With a commitment to exceptional customer service, affordability, and safety, E-MEAT is not just about selling meat—it's about creating moments that matter, one meal at a time.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                <div class="scroll-reveal">
                    <img src="../IMAGES/RED LOGO.png" alt="Premium Quality Meat" class="rounded-lg shadow-xl w-3/4 mx-auto h-auto">
                </div>
                
                <div class="scroll-reveal" style="transition-delay: 0.2s;">
                    <h4 class="text-2xl font-bold mb-5">Our Commitment to Excellence</h4>
                    
                    <div class="space-y-5">
                        <div class="flex items-start">
                            <div class="bg-red-100 rounded-full p-2 mr-4 mt-1">
                                <i class="ri-check-line text-red-700"></i>
                            </div>
                            <div>
                                <h5 class="font-medium text-lg">Sustainably Sourced</h5>
                                <p class="text-gray-600">We partner with local farms that follow ethical and sustainable practices.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-red-100 rounded-full p-2 mr-4 mt-1">
                                <i class="ri-check-line text-red-700"></i>
                            </div>
                            <div>
                                <h5 class="font-medium text-lg">Hygiene Standards</h5>
                                <p class="text-gray-600">Our facilities maintain the highest standards of cleanliness and food safety.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-red-100 rounded-full p-2 mr-4 mt-1">
                                <i class="ri-check-line text-red-700"></i>
                            </div>
                            <div>
                                <h5 class="font-medium text-lg">Temperature Controlled</h5>
                                <p class="text-gray-600">Our specialized delivery vehicles maintain optimal temperature throughout transit.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-red-100 rounded-full p-2 mr-4 mt-1">
                                <i class="ri-check-line text-red-700"></i>
                            </div>
                            <div>
                                <h5 class="font-medium text-lg">Expert Team</h5>
                                <p class="text-gray-600">Our butchers have years of experience in selecting and cutting the finest meats.</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="register.php" class="custom-btn inline-block mt-8 py-3 px-8 rounded-full">Join Us Today</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16 scroll-reveal">
                <h2 class="text-sm font-bold text-red-700 uppercase tracking-wide mb-2">Our Products</h2>
                <h3 class="text-4xl font-bold mb-6">Premium Quality Meats</h3>
                <p class="text-gray-600 max-w-3xl mx-auto">
                    Explore our wide selection of fresh, premium quality meats. All cuts are carefully selected and prepared by our expert butchers.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="meat-category-item bg-gray-50 rounded-xl overflow-hidden shadow-lg scroll-reveal">
                    <img src="../IMAGES/MEATS/Logo/PIGGY.png" alt="Pork Category" class="w-32 h-32 mx-auto my-6 rounded-full">
                    <div class="p-6 text-center bg-red-700 text-white">
                        <h4 class="text-xl font-bold">PORK</h4>
                    </div>
                </div>
                
                <div class="meat-category-item bg-gray-50 rounded-xl overflow-hidden shadow-lg scroll-reveal" style="transition-delay: 0.2s;">
                    <img src="../IMAGES/MEATS/Logo/COW.png" alt="Beef Category" class="w-32 h-32 mx-auto my-6 rounded-full">
                    <div class="p-6 text-center bg-red-700 text-white">
                        <h4 class="text-xl font-bold">BEEF</h4>
                    </div>
                </div>
                
                <div class="meat-category-item bg-gray-50 rounded-xl overflow-hidden shadow-lg scroll-reveal" style="transition-delay: 0.4s;">
                    <img src="../IMAGES/MEATS/Logo/CHICK.png" alt="Chicken Category" class="w-32 h-32 mx-auto my-6 rounded-full">
                    <div class="p-6 text-center bg-red-700 text-white">
                        <h4 class="text-xl font-bold">CHICKEN</h4>
                    </div>
                </div>
            </div>
            
            <!-- Featured Products -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Product 1 -->
                <div class="bg-white rounded-xl overflow-hidden shadow-lg border border-gray-200 scroll-reveal">
                    <div class="h-52 overflow-hidden">
                        <img src="../IMAGES/PORKY/Pork Belly.png" alt="Pork Belly" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-lg font-bold">Pork Belly</h4>
                            <span class="text-red-700 font-bold">₱320/kg</span>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Premium cut pork belly, perfect for crispy lechon kawali or bacon.</p>
                        <div class="text-sm text-green-600 mb-4">In Stock</div>
                        <button onclick="showLoginPrompt()" class="w-full bg-red-700 hover:bg-red-800 text-white py-2 px-4 rounded-lg transition">
                            <i class="ri-shopping-cart-line mr-2"></i> Add to Cart
                        </button>
                    </div>
                </div>
                
                <!-- Product 2 -->
                <div class="bg-white rounded-xl overflow-hidden shadow-lg border border-gray-200 scroll-reveal" style="transition-delay: 0.2s;">
                    <div class="h-52 overflow-hidden">
                        <img src="../IMAGES/COW/Rib eye Steaks.png" alt="Beef Ribeye" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-lg font-bold">Beef Ribeye</h4>
                            <span class="text-red-700 font-bold">₱580/kg</span>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Premium ribeye steak with excellent marbling for a tender, juicy result.</p>
                        <div class="text-sm text-green-600 mb-4">In Stock</div>
                        <button onclick="showLoginPrompt()" class="w-full bg-red-700 hover:bg-red-800 text-white py-2 px-4 rounded-lg transition">
                            <i class="ri-shopping-cart-line mr-2"></i> Add to Cart
                        </button>
                    </div>
                </div>
                
                <!-- Product 3 -->
                <div class="bg-white rounded-xl overflow-hidden shadow-lg border border-gray-200 scroll-reveal" style="transition-delay: 0.4s;">
                    <div class="h-52 overflow-hidden">
                        <img src="../IMAGES/CHICKY/Breast.png" alt="Chicken Breast" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-lg font-bold">Chicken Breast</h4>
                            <span class="text-red-700 font-bold">₱220/kg</span>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Boneless, skinless chicken breast - high in protein and versatile.</p>
                        <div class="text-sm text-green-600 mb-4">In Stock</div>
                        <button onclick="showLoginPrompt()" class="w-full bg-red-700 hover:bg-red-800 text-white py-2 px-4 rounded-lg transition">
                            <i class="ri-shopping-cart-line mr-2"></i> Add to Cart
                        </button>
                    </div>
                </div>
                
                <!-- Product 4 -->
                <div class="bg-white rounded-xl overflow-hidden shadow-lg border border-gray-200 scroll-reveal" style="transition-delay: 0.6s;">
                    <div class="h-52 overflow-hidden">
                        <img src="../IMAGES/PORKY/Pork Chops.png" alt="Pork Chop" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-lg font-bold">Pork Chop</h4>
                            <span class="text-red-700 font-bold">₱280/kg</span>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Bone-in pork chops, perfect for grilling or pan-frying.</p>
                        <div class="text-sm text-green-600 mb-4">In Stock</div>
                        <button onclick="showLoginPrompt()" class="w-full bg-red-700 hover:bg-red-800 text-white py-2 px-4 rounded-lg transition">
                            <i class="ri-shopping-cart-line mr-2"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="register.php" class="custom-btn inline-block py-3 px-8 rounded-full">
                    View All Products
                </a>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16 scroll-reveal">
                <h2 class="text-sm font-bold text-red-700 uppercase tracking-wide mb-2">Meet Our Team</h2>
                <h3 class="text-4xl font-bold mb-6">The People Behind E-MEAT</h3>
                <p class="text-gray-600 max-w-3xl mx-auto">
                    Our dedicated team of professionals works tirelessly to ensure you get the best quality meat products and excellent service.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-8">
                <!-- Team Member 1 -->
                <div class="team-member bg-white rounded-xl overflow-hidden shadow-lg text-center scroll-reveal">
                    <div class="h-64 overflow-hidden">
                        <img src="../IMAGES/PROFILES/RONIN.jpg" alt="Ronin Cabusao" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h4 class="text-xl font-bold mb-1">Ronin Cabusao</h4>
                        <p class="text-red-700 font-medium mb-3">Lead Developer</p>
                        <p class="text-gray-600 text-sm mb-4">Full-stack developer and system architect behind E-MEAT's web platform.</p>
                        <div class="flex justify-center space-x-3">
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-linkedin-fill text-lg"></i></a>
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-twitter-fill text-lg"></i></a>
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-mail-fill text-lg"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Team Member 2 -->
                <div class="team-member bg-white rounded-xl overflow-hidden shadow-lg text-center scroll-reveal" style="transition-delay: 0.2s;">
                    <div class="h-64 overflow-hidden">
                        <img src="../IMAGES/PROFILES/VINCE.jpg" alt="Vince Bryant Cabunilas" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h4 class="text-xl font-bold mb-1">Vince Cabunilas</h4>
                        <p class="text-red-700 font-medium mb-3">UX Designer</p>
                        <p class="text-gray-600 text-sm mb-4">Creates seamless user experiences and intuitive interfaces for our platform.</p>
                        <div class="flex justify-center space-x-3">
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-linkedin-fill text-lg"></i></a>
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-twitter-fill text-lg"></i></a>
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-mail-fill text-lg"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Team Member 3 -->
                <div class="team-member bg-white rounded-xl overflow-hidden shadow-lg text-center scroll-reveal" style="transition-delay: 0.4s;">
                    <div class="h-64 overflow-hidden">
                        <img src="../IMAGES/PROFILES/ERICA.jpg" alt="Erica Juarez" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h4 class="text-xl font-bold mb-1">Erica Juarez</h4>
                        <p class="text-red-700 font-medium mb-3">Product Manager</p>
                        <p class="text-gray-600 text-sm mb-4">Oversees product quality and ensures premium selections for all categories.</p>
                        <div class="flex justify-center space-x-3">
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-linkedin-fill text-lg"></i></a>
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-twitter-fill text-lg"></i></a>
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-mail-fill text-lg"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Team Member 4 -->
                <div class="team-member bg-white rounded-xl overflow-hidden shadow-lg text-center scroll-reveal" style="transition-delay: 0.6s;">
                    <div class="h-64 overflow-hidden">
                        <img src="../IMAGES/PROFILES/JUSTINE.jpg" alt="Justine Paraiso" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h4 class="text-xl font-bold mb-1">Justine Paraiso</h4>
                        <p class="text-red-700 font-medium mb-3">Marketing Director</p>
                        <p class="text-gray-600 text-sm mb-4">Manages brand strategy and customer acquisition for maximum reach.</p>
                        <div class="flex justify-center space-x-3">
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-linkedin-fill text-lg"></i></a>
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-twitter-fill text-lg"></i></a>
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-mail-fill text-lg"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Team Member 5 -->
                <div class="team-member bg-white rounded-xl overflow-hidden shadow-lg text-center scroll-reveal" style="transition-delay: 0.8s;">
                    <div class="h-64 overflow-hidden">
                        <img src="../IMAGES/PROFILES/LEYZEL.jpg" alt="Liezel Tumbaga" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h4 class="text-xl font-bold mb-1">Liezel Tumbaga</h4>
                        <p class="text-red-700 font-medium mb-3">Customer Support</p>
                        <p class="text-gray-600 text-sm mb-4">Provides excellent customer service and resolves customer inquiries.</p>
                        <div class="flex justify-center space-x-3">
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-linkedin-fill text-lg"></i></a>
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-twitter-fill text-lg"></i></a>
                            <a href="#" class="text-gray-400 hover:text-red-700 transition"><i class="ri-mail-fill text-lg"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16 scroll-reveal">
                <h2 class="text-sm font-bold text-red-700 uppercase tracking-wide mb-2">Testimonials</h2>
                <h3 class="text-4xl font-bold mb-6">What Our Customers Say</h3>
                <p class="text-gray-600 max-w-3xl mx-auto">
                    Don't just take our word for it. Here's what some of our satisfied customers have to say about their E-MEAT experience.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="testimonial-card bg-gray-50 p-8 rounded-xl shadow-lg scroll-reveal">
                    <div class="flex items-center mb-6">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="John Doe" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">John Doe</h4>
                            <p class="text-gray-500 text-sm">Home Chef</p>
                        </div>
                    </div>
                    <div class="mb-6 text-yellow-400">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                    </div>
                    <p class="text-gray-600">
                        "I've been ordering from E-MEAT for the past 6 months, and the quality has been consistently excellent. Their ribeye steaks are the best I've had, and delivery is always on time. Highly recommended!"
                    </p>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="testimonial-card bg-gray-50 p-8 rounded-xl shadow-lg scroll-reveal" style="transition-delay: 0.2s;">
                    <div class="flex items-center mb-6">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Maria Garcia" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">Maria Garcia</h4>
                            <p class="text-gray-500 text-sm">Restaurant Owner</p>
                        </div>
                    </div>
                    <div class="mb-6 text-yellow-400">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                    </div>
                    <p class="text-gray-600">
                        "As a restaurant owner, consistency in meat quality is crucial. E-MEAT has been my trusted supplier for over a year now. Their cuts are always perfect, and their bulk ordering options save me time and money."
                    </p>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="testimonial-card bg-gray-50 p-8 rounded-xl shadow-lg scroll-reveal" style="transition-delay: 0.4s;">
                    <div class="flex items-center mb-6">
                        <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="David Kim" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">David Kim</h4>
                            <p class="text-gray-500 text-sm">BBQ Enthusiast</p>
                        </div>
                    </div>
                    <div class="mb-6 text-yellow-400">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-half-fill"></i>
                    </div>
                    <p class="text-gray-600">
                        "I'm very particular about the meat I use for my weekend BBQs, and E-MEAT has never disappointed. Their pork belly is perfect for grilling, and the chicken cuts are always fresh. Great service and product!"
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16 bg-red-700 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6 scroll-reveal">Ready to experience premium meat delivery?</h2>
            <p class="text-xl text-white/80 mb-10 max-w-3xl mx-auto scroll-reveal" style="transition-delay: 0.2s;">
                Join thousands of satisfied customers who enjoy the convenience of quality meat delivered to their doorstep.
            </p>
            <div class="scroll-reveal" style="transition-delay: 0.4s;">
                <a href="register.php" class="bg-white text-red-700 hover:bg-gray-100 py-3 px-8 rounded-full text-lg font-medium transition">Create an Account</a>
                <a href="login.php" class="ml-4 bg-transparent border-2 border-white text-white hover:bg-white hover:text-red-700 py-3 px-8 rounded-full text-lg font-medium transition">Login</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16 scroll-reveal">
                <h2 class="text-sm font-bold text-red-700 uppercase tracking-wide mb-2">Contact Us</h2>
                <h3 class="text-4xl font-bold mb-6">Get In Touch</h3>
                <p class="text-gray-600 max-w-3xl mx-auto">
                    Have questions about our products or services? Our team is here to help. Feel free to reach out through any of the channels below.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <!-- Contact Info 1 -->
                <div class="bg-white p-8 rounded-xl shadow-lg text-center scroll-reveal">
                    <div class="bg-red-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-6">
                        <i class="ri-map-pin-2-line text-red-700 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-3">Our Location</h4>
                    <p class="text-gray-600">123 Meat Street, Butcher District<br>Metro Manila, Philippines</p>
                </div>
                
                <!-- Contact Info 2 -->
                <div class="bg-white p-8 rounded-xl shadow-lg text-center scroll-reveal" style="transition-delay: 0.2s;">
                    <div class="bg-red-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-6">
                        <i class="ri-phone-line text-red-700 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-3">Call Us</h4>
                    <p class="text-gray-600">(02) 8123-4567<br>+63 912 345 6789</p>
                </div>
                
                <!-- Contact Info 3 -->
                <div class="bg-white p-8 rounded-xl shadow-lg text-center scroll-reveal" style="transition-delay: 0.4s;">
                    <div class="bg-red-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-6">
                        <i class="ri-mail-line text-red-700 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-3">Email Us</h4>
                    <p class="text-gray-600">info@emeat.com<br>support@emeat.com</p>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg scroll-reveal">
                <div class="p-8">
                    <h4 class="text-2xl font-bold mb-8 text-center">Get in touch</h4>
                    <form action="#" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <input type="text" id="name" name="name" placeholder="Your name" class="w-full px-4 py-3 bg-gray-50 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" required>
                            </div>
                            
                            <div>
                                <input type="email" id="email" name="email" placeholder="Your email" class="w-full px-4 py-3 bg-gray-50 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" required>
                            </div>
                        </div>
                        
                        <div>
                            <input type="text" id="subject" name="subject" placeholder="Subject" class="w-full px-4 py-3 bg-gray-50 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        </div>
                        
                        <div>
                            <textarea id="message" name="message" rows="4" placeholder="Your message" class="w-full px-4 py-3 bg-gray-50 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 resize-none" required></textarea>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="bg-red-700 hover:bg-red-800 text-white py-3 px-8 rounded-lg transition-all duration-300 transform hover:-translate-y-1">
                                Send message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-16 pb-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="flex items-center space-x-3 mb-6">
                        <img src="../IMAGES/RED LOGO.png" alt="E-MEAT Logo" class="h-10 w-10 rounded-full bg-white p-1.5">
                        <span class="text-2xl font-bold">E-MEAT</span>
                    </div>
                    <p class="text-gray-400 mb-6">
                        Premium quality meats delivered to your doorstep. Experience the convenience of online meat shopping with guaranteed freshness and quality.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="ri-facebook-fill text-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="ri-instagram-fill text-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="ri-twitter-fill text-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="ri-youtube-fill text-lg"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h5 class="text-xl font-bold mb-6">Quick Links</h5>
                    <ul class="space-y-3">
                        <li><a href="#home" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="#about" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="#products" class="text-gray-400 hover:text-white transition">Products</a></li>
                        <li><a href="#team" class="text-gray-400 hover:text-white transition">Our Team</a></li>
                        <li><a href="#testimonials" class="text-gray-400 hover:text-white transition">Testimonials</a></li>
                        <li><a href="#contact" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h5 class="text-xl font-bold mb-6">Products</h5>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Beef Products</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Pork Products</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Chicken Products</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Special Cuts</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Bulk Orders</a></li>
                    </ul>
                </div>
                
                <div>
                    <h5 class="text-xl font-bold mb-6">Contact Info</h5>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="ri-map-pin-line text-red-700 mr-3 mt-1"></i>
                            <span class="text-gray-400">123 Meat Street, Butcher District<br>Metro Manila, Philippines</span>
                        </li>
                        <li class="flex items-start">
                            <i class="ri-phone-line text-red-700 mr-3 mt-1"></i>
                            <span class="text-gray-400">(02) 8123-4567<br>+63 912 345 6789</span>
                        </li>
                        <li class="flex items-start">
                            <i class="ri-mail-line text-red-700 mr-3 mt-1"></i>
                            <span class="text-gray-400">info@emeat.com<br>support@emeat.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 mt-8 text-center text-gray-500">
                <p>&copy; 2025 E-MEAT. All rights reserved. Designed and developed by E-MEAT Team.</p>
            </div>
        </div>
    </footer>

    <!-- Login Prompt Modal -->
    <div id="login-modal" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl">
            <div class="text-center mb-6">
                <img src="../IMAGES/RED LOGO.png" alt="E-MEAT Logo" class="h-16 w-16 mx-auto bg-red-700 p-3 rounded-full">
                <h3 class="text-2xl font-bold mt-4">Login Required</h3>
                <p class="text-gray-600 mt-2">Please log in or create an account to add items to your cart and make a purchase.</p>
            </div>
            
            <div class="flex flex-col md:flex-row justify-center gap-4 mt-8">
                <a href="login.php" class="custom-btn py-2 px-6 rounded-full text-center">Login</a>
                <a href="register.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-6 rounded-full text-center transition">Register</a>
            </div>
            
            <button id="close-modal" class="mt-6 text-gray-500 hover:text-gray-800 transition text-center w-full">
                Continue Browsing
            </button>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="fixed bottom-6 right-6 bg-red-700 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-lg opacity-0 invisible transition-all duration-300 z-40">
        <i class="ri-arrow-up-line text-xl"></i>
    </button>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll Reveal Animation
            const scrollRevealElements = document.querySelectorAll('.scroll-reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                    }
                });
            }, { threshold: 0.1 });
            
            scrollRevealElements.forEach(el => {
                observer.observe(el);
            });
            
            // Header Background Change on Scroll
            const header = document.getElementById('main-header');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 100) {
                    header.classList.add('bg-red-800', 'shadow-md');
                    header.classList.remove('bg-transparent');
                } else {
                    header.classList.add('bg-transparent');
                    header.classList.remove('bg-red-800', 'shadow-md');
                }
            });
            
            // Back to Top Button
            const backToTopButton = document.getElementById('back-to-top');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 500) {
                    backToTopButton.classList.remove('opacity-0', 'invisible');
                    backToTopButton.classList.add('opacity-100', 'visible');
                } else {
                    backToTopButton.classList.add('opacity-0', 'invisible');
                    backToTopButton.classList.remove('opacity-100', 'visible');
                }
            });
            
            backToTopButton.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Login Modal
            const loginModal = document.getElementById('login-modal');
            const closeModal = document.getElementById('close-modal');
            
            closeModal.addEventListener('click', () => {
                loginModal.classList.add('hidden');
            });
            
            // Smooth Scroll for Anchor Links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
        
        // Login Prompt Function
        function showLoginPrompt() {
            document.getElementById('login-modal').classList.remove('hidden');
        }
    </script>
</body>
</html>