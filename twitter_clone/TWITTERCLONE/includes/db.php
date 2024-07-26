<?php
$servername = "sql111.infinityfree.com";
$username = "if0_36981300";
$password = "microdash123";
$dbname = "if0_36981300_twitter_clone";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
