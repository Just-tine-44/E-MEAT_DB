<?php
// Redirect to public landing page for non-logged-in users
session_start();

if (isset($_SESSION['username']) && isset($_SESSION['user_id'])) {
    // User is logged in, redirect to the main dashboard
    header("Location: users/index.php");
    exit();
} else {
    // User is not logged in, redirect to public page
    header("Location: users/public.php");
    exit();
}
?>