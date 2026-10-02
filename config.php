<?php
// ============================================
// DATABASE CONNECTION SETTINGS
// ============================================
// Change these only if your MySQL setup is different (e.g. XAMPP defaults
// are usually host=localhost, user=root, pass="")

$host   = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "gym_management";   // <-- must match the database name you created

$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Start session on every page that includes this file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
