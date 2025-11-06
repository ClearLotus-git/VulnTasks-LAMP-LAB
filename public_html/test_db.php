<?php
// Show all PHP errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to include database connection
echo "<h3>Testing database connection...</h3>";

$path = "../includes/db_connect.php";
if (file_exists($path)) {
    include($path);
    echo "<p>db_connect.php included successfully.</p>";
} else {
    echo "<p style='color:red;'>❌ Could not find db_connect.php at $path</p>";
    exit;
}

// Test if connection object is valid
if (isset($conn) && $conn->ping()) {
    echo "<p style='color:green;'>Database connection successful!</p>";
} else {
    echo "<p style='color:red;'>Database connection failed: " . $conn->connect_error . "</p>";
}
?>

