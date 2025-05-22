<?php
// Database configuration
$host = 'localhost:3306';
$username = 'root';
$password = '';
$database = 'topup_game';

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");
