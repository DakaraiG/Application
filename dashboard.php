<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<h2>Dashboard Page</h2>
<p><a href="logout.php">Logout</a></p>
