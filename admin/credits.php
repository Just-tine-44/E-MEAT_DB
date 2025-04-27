<?php
session_start(); // Start the session

// Authentication check
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    $_SESSION['message'] = "You need to log in as admin to access this page";
    header("Location: ../users/login.php");
    exit();
}

$page_title = "Credits & Acknowledgements | E-MEAT Admin";
include('new_include/sidebar.php'); // Sidebar (navigation)
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F9FAFB;
        }
        
        .team-member-card {
            transition: all 0.3s ease;
        }
        
        .team-member-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .animate-fade-in {
            animation: fadeIn 0.8s ease forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-delay-100 { animation-delay: 0.1s; }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-300 { animation-delay: 0.3s; }
        .animate-delay-400 { animation-delay: 0.4s; }
        .animate-delay-500 { animation-delay: 0.5s; }
        .animate-delay-600 { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <div class="pl-0 lg:pl-64 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
            <!-- Page Header -->
            <div class="mb-10 text-center animate-fade-in">
                <h1 class="text-3xl font-bold text-gray-900">Credits & Acknowledgements</h1>
                <p class="mt-3 text-lg text-gray-600 max-w-3xl mx-auto">
                    This project was made possible by the hard work and dedication of the following team members
                    and the guidance of our instructor.
                </p>
            </div>
            
            <!-- Team Section -->
            <div class="mb-20 animate-fade-in animate-delay-100">
                <div class="text-center mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 inline-block pb-2 border-b-2 border-red-500">
                        Development Team
                    </h2>
                </div>
                
                <!-- Team Member Cards with Photos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                        <!-- Team Member 1 - Vince -->
                        <div class="team-member-card bg-white rounded-xl p-6 shadow-sm text-center animate-fade-in animate-delay-100">
                            <div class="relative mx-auto w-24 h-24 mb-4 overflow-hidden rounded-full border-2 border-red-500">
                                <img src="../IMAGES/PROFILES/VINCE.jpg" alt="Vince Bryant Cabunilas" class="object-cover w-full h-full">
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Vince Bryant Cabunilas</h3>
                            <p class="text-sm text-gray-500 mt-1">Developer</p>
                        </div>
                        
                        <!-- Team Member 2 - Ronin -->
                        <div class="team-member-card bg-white rounded-xl p-6 shadow-sm text-center animate-fade-in animate-delay-200">
                            <div class="relative mx-auto w-24 h-24 mb-4 overflow-hidden rounded-full border-2 border-red-500">
                                <img src="../IMAGES/PROFILES/RONIN.jpg" alt="Ronin Cabusao" class="object-cover w-full h-full">
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Ronin Cabusao</h3>
                            <p class="text-sm text-gray-500 mt-1">Developer</p>
                        </div>
                        
                        <!-- Team Member 3 - Erica -->
                        <div class="team-member-card bg-white rounded-xl p-6 shadow-sm text-center animate-fade-in animate-delay-300">
                            <div class="relative mx-auto w-24 h-24 mb-4 overflow-hidden rounded-full border-2 border-red-500">
                                <img src="../IMAGES/PROFILES/ERICA.jpg" alt="Erica Juarez" class="object-cover w-full h-full">
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Erica Juarez</h3>
                            <p class="text-sm text-gray-500 mt-1">Developer</p>
                        </div>
                        
                        <!-- Team Member 4 - Justine -->
                        <div class="team-member-card bg-white rounded-xl p-6 shadow-sm text-center animate-fade-in animate-delay-400">
                            <div class="relative mx-auto w-24 h-24 mb-4 overflow-hidden rounded-full border-2 border-red-500">
                                <img src="../IMAGES/PROFILES/JUSTINE.jpg" alt="Justine Paraiso" class="object-cover w-full h-full">
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Justine Paraiso</h3>
                            <p class="text-sm text-gray-500 mt-1">Developer</p>
                        </div>
                        
                        <!-- Team Member 5 - Leyzel -->
                        <div class="team-member-card bg-white rounded-xl p-6 shadow-sm text-center animate-fade-in animate-delay-500">
                            <div class="relative mx-auto w-24 h-24 mb-4 overflow-hidden rounded-full border-2 border-red-500">
                                <img src="../IMAGES/PROFILES/LEYZEL.jpg" alt="Leyzel Tumbaga" class="object-cover w-full h-full">
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Leyzel Tumbaga</h3>
                            <p class="text-sm text-gray-500 mt-1">Developer</p>
                        </div>
                    </div>
            </div>
            
            <!-- Instructor Section -->
            <div class="mb-16 animate-fade-in animate-delay-600">
                <div class="text-center mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 inline-block pb-2 border-b-2 border-red-500">
                        Instructor & Mentor
                    </h2>
                </div>
                
                <div class="max-w-md mx-auto">
                    <div class="bg-white rounded-xl p-8 shadow-sm text-center">
                        <div class="relative mx-auto w-28 h-28 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center mb-5">
                            <span class="text-3xl font-bold text-white"><?= substr('Lahaylahay', 0, 1) ?></span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Ma'am Beverley Lahaylahay</h3>
                        <p class="text-sm text-gray-500 mt-1">Instructor & Project Advisor</p>
                        <p class="mt-4 text-gray-600">
                            For her invaluable guidance, expertise, and support throughout the development of this project.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Additional Acknowledgements -->
            <div class="text-center animate-fade-in animate-delay-600 max-w-2xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Special Thanks</h2>
                <div class="bg-white rounded-xl p-6 shadow-sm mb-10">
                    <p class="text-gray-600 mb-4">
                        We would also like to express our gratitude to the University Of Cebu for providing us with the resources
                        and environment to develop this project, and to everyone who contributed to its success.
                    </p>
                    <div class="inline-flex items-center">
                        <span class="text-md text-gray-900 font-medium">E-MEAT: Database 2 Project</span>
                        <span class="mx-2">•</span>
                        <span class="text-gray-500"><?= date("Y") ?></span>
                    </div>
                </div>
                
                <!-- Logo/Branding Section -->
                <div class="mt-10 mb-4">
                    <img src="../IMAGES/RED LOGO.png" alt="E-MEAT Logo" class="h-14 mx-auto">
                </div>
            </div>
        </div>
    </div>
</body>
</html>