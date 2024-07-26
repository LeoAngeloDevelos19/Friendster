<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$follower_id = $_SESSION['user_id'];
$followed_id = $_GET['follow_id'];

$sql = "INSERT INTO follows (follower_id, followed_id) VALUES ($follower_id, $followed_id)";
$conn->query($sql);

header("Location: search.php");
?>
