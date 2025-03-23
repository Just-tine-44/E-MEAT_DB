<?php

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'e-meat';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Hide errors
ini_set('display_errors', 0);
error_reporting(0);
?>