<?php
// Get current page for active state highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="icon" type="image" href="../IMAGES/RED LOGO.png">

<div id="sidebar" class="sidebar-wrapper transition-all duration-300 ease-in-out">
    <!-- Sidebar Container -->
    <aside class="fixed inset-y-0 left-0 z-20 flex flex-col w-64 h-full overflow-y-auto text-gray-700 bg-white border-r border-gray-100 shadow-sm transition-all">
        <!-- Logo Section -->
        <div class="flex items-center justify-between px-4 pt-4 pb-2">
            <a href="index.php" class="flex items-center space-x-2">
                <div class="flex items-center justify-center w-9 h-9 rounded-full bg-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">E-Meat</h1>
                    <p class="text-xs text-gray-500 -mt-1">Admin Dashboard</p>
                </div>
            </a>
            <button id="sidebar-toggle-btn" class="p-1 rounded-md lg:hidden hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- User Profile Section -->
        <div class="px-4 py-3">
            <div class="flex items-center space-x-3 p-2 bg-gray-50 rounded-xl">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-tr from-red-500 to-red-600 flex items-center justify-center text-white font-semibold">
                    <?php 
                    if (isset($_SESSION['user_fname']) && isset($_SESSION['user_lname'])) {
                        echo strtoupper(substr($_SESSION['user_fname'], 0, 1) . substr($_SESSION['user_lname'], 0, 1));
                    } else {
                        echo "AD";
                    }
                    ?>
                </div>
                <div>
                    <h3 class="font-medium text-sm"><?= isset($_SESSION['user_fname']) ? htmlspecialchars($_SESSION['user_fname'].' '.$_SESSION['user_lname']) : "Admin User" ?></h3>
                    <p class="text-xs text-gray-500">Administrator</p>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="flex flex-col justify-between h-full">
            <nav class="px-4 pt-4 pb-4 space-y-3">
                <!-- Dashboard -->
                <a href="admin.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 
                <?= $current_page === 'admin.php' ? 'bg-red-50 text-red-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3 <?= $current_page === 'admin.php' ? 'text-red-500' : 'text-gray-500' ?>">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Dashboard
                </a>

                <!-- Manage Section -->
                <div class="space-y-1">
                    <p class="mt-3 px-3 text-xs text-gray-400 uppercase font-semibold">Manage</p>
                    
                    <!-- Products List -->
                    <a href="product_list.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 
                    <?= $current_page === 'product_list.php' ? 'bg-red-50 text-red-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3 <?= $current_page === 'product_list.php' ? 'text-red-500' : 'text-gray-500' ?>">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        List of Products
                    </a>

                    <!-- Add Product -->
                    <a href="add_product.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 
                    <?= $current_page === 'add_product.php' ? 'bg-red-50 text-red-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3 <?= $current_page === 'add_product.php' ? 'text-red-500' : 'text-gray-500' ?>">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Product
                    </a>
                    
                    <!-- Order Status -->
                    <a href="status_order.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 
                    <?= $current_page === 'status_order.php' ? 'bg-red-50 text-red-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3 <?= $current_page === 'status_order.php' ? 'text-red-500' : 'text-gray-500' ?>">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        Order Status
                    </a>

                    <!-- Manage Riders -->
                    <a href="rider_manage.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 
                    <?= $current_page === 'rider_manage.php' ? 'bg-red-50 text-red-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3 <?= $current_page === 'rider_manage.php' ? 'text-red-500' : 'text-gray-500' ?>">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        Manage Riders
                    </a>
                </div>

                <!-- Reports Section -->
                <div class="space-y-1">
                    <p class="mt-3 px-3 text-xs text-gray-400 uppercase font-semibold">Reports</p>
                    
                    <!-- Sales Report -->
                    <a href="sales_report.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 
                    <?= $current_page === 'sales_report.php' ? 'bg-red-50 text-red-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3 <?= $current_page === 'sales_report.php' ? 'text-red-500' : 'text-gray-500' ?>">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        Sales Report
                    </a>
                </div>

                <div class="space-y-1">
                    <p class="mt-3 px-3 text-xs text-gray-400 uppercase font-semibold">Credits</p>
                    
                    <!-- Credits -->
                    <a href="credits.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 
                    <?= $current_page === 'credits.php' ? 'bg-red-50 text-red-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3 <?= $current_page === 'credits.php' ? 'text-red-500' : 'text-gray-500' ?>">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        Credits & Team
                    </a>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 mt-auto border-t border-gray-100">
                <a href="../back_process/logout.php" class="flex items-center justify-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 text-red-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    Sign Out
                </a>
                <div class="mt-4 text-center">
                    <p class="text-xs text-gray-500">E-Meat Admin © <?= date('Y') ?></p>
                    <p class="text-xs text-gray-400">Version 1.2.0</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Mobile overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 z-10 bg-black opacity-50 hidden lg:hidden"></div>
</div>

<!-- JavaScript for Sidebar Toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const mobileSidebar = document.querySelector('.sidebar-wrapper aside');
        
        // Function to toggle sidebar visibility on mobile
        function toggleSidebar() {
            if (mobileSidebar.classList.contains('-translate-x-full')) {
                mobileSidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            } else {
                mobileSidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }
        
        // Add default closed state for mobile only
        if (window.innerWidth < 1024) {
            mobileSidebar.classList.add('-translate-x-full');
        }
        
        // Event listeners for sidebar toggle
        sidebarToggleBtn.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
        
        // Handle responsive behavior if window resizes
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                mobileSidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            } else {
                mobileSidebar.classList.add('-translate-x-full');
            }
        });
    });
</script>