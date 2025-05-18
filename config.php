<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'personalfinancedb';

// Create database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to display empty states
function handleEmpty($message) {
    return '<div class="empty-state">'.$message.'</div>';
}

// Redirect if not logged in
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}
?>