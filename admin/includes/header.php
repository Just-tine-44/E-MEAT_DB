<?php
session_start();

// To match what login.php is setting:
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    $_SESSION['message'] = "You need to log in as admin to access this page";
    header("Location: ../users/login.php");
    exit();
}

// Set default title if not provided by the page
if(!isset($page_title)) {
    $page_title = "Dashboard E-MEAT";
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <link rel="icon" type="image" href="../IMAGES/RED LOGO.png">
        <title><?php echo $page_title; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>

<body class="sb-nav-fixed">

    <?php include('includes/navbar-top.php'); ?>

    <div id="layoutSidenav">
        
        <?php include('includes/sidebar.php'); ?>
    

        <div id="layoutSidenav_content">
            <main>   