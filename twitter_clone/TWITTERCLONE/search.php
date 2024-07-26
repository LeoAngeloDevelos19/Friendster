<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$search_term = '';
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['query'])) {
    $search_term = $_GET['query'];
    $sql = "SELECT * FROM users WHERE username LIKE '%$search_term%' OR bio LIKE '%$search_term%'";
    $search_results = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="navbar">
        <h1>Friendster</h1>
        <a href="logout.php">Logout</a>
        <a href="home.php">Home</a>
        <a href="profile.php">Profile</a>
    </div>
    <div class="container">
        <h2>Search</h2>
        <form method="get" action="search.php">
            <input type="text" name="query" placeholder="Search..." value="<?php echo $search_term; ?>" required><br>
            <input type="submit" value="Search">
        </form>
        <h3>Results</h3>
        <?php if (isset($search_results)): ?>
            <?php while ($user = $search_results->fetch_assoc()): ?>
                <div class="search-result">
                    <strong><?php echo $user['username']; ?></strong>
                    <p><?php echo $user['bio']; ?></p>
                    <a href="follow.php?follow_id=<?php echo $user['id']; ?>">Follow</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>
</html>
