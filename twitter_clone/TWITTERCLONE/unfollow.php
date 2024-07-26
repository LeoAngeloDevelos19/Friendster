<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$follower_id = $_SESSION['user_id'];
$followed_id = $_GET['unfollow_id'];

$sql = "DELETE FROM follows WHERE follower_id = $follower_id AND followed_id = $followed_id";
$conn->query($sql);

header("Location: search.php");
?>
