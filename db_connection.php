<?php
// Database connection settings
$servername = "localhost";  // Your database server (usually localhost)
$username = "root";         // Your database username (default for local setups is usually "root")
$password = "";             // Your database password (leave empty for local setups without a password)
$dbname = "sa_talent_hub";  // Your database name (change this to match your actual database name)

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
