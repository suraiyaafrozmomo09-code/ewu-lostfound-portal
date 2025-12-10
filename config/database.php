<?php
// Database configuration for Port 3307
$host = 'localhost';
$dbname = 'ewu_lostfound';
$username = 'root';
$password = '';
$port = 3307; // Your MySQL port

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>