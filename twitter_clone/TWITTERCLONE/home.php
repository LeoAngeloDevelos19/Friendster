<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['content'])) {
        $content = $_POST['content'];
        $imagePath = '';

        if (!empty($_FILES['tweet_image']['name'])) {
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($_FILES["tweet_image"]["name"]);
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $uploadOk = 1;

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["tweet_image"]["tmp_name"]);
            if ($check === false) {
                echo "File is not an image.";
                $uploadOk = 0;
            }

            // Check if file already exists
            if (file_exists($targetFile)) {
                echo "Sorry, file already exists.";
                $uploadOk = 0;
            }

            // Check file size
            if ($_FILES["tweet_image"]["size"] > 5000000) { // 5MB limit
                echo "Sorry, your file is too large.";
                $uploadOk = 0;
            }

            // Allow certain file formats
            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadOk = 0;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                echo "Sorry, your file was not uploaded.";
            } else {
                if (move_uploaded_file($_FILES["tweet_image"]["tmp_name"], $targetFile)) {
                    $imagePath = $targetFile;
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            }
        }

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO tweets (user_id, content, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user_id, $content, $imagePath);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['comment']) && isset($_POST['tweet_id'])) {
        $comment = $_POST['comment'];
        $tweet_id = $_POST['tweet_id'];

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO comments (tweet_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $tweet_id, $user_id, $comment);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all tweets
$sql = "SELECT tweets.id AS tweet_id, tweets.content, tweets.image, users.username, tweets.created_at 
        FROM tweets 
        JOIN users ON tweets.user_id = users.id 
        ORDER BY tweets.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$tweets = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="navbar">
        <h1>Friendster</h1>
        <a href="logout.php">Logout</a>
        <a href="profile.php">Profile</a>
        <a href="search.php">Search</a>
    </div>
    <div class="container">
        <h2>Welcome</h2>
        <form method="post" action="home.php" enctype="multipart/form-data">
            <textarea name="content" placeholder="What's happening?" required></textarea><br>
            <input type="file" name="tweet_image" accept="image/*"><br>
            <input type="submit" value="Tweet">
        </form>
        <h2>Tweets</h2>
        <?php while ($tweet = $tweets->fetch_assoc()): ?>
            <div class="tweet">
                <strong><?php echo htmlspecialchars($tweet['username']); ?></strong>
                <p><?php echo htmlspecialchars($tweet['content']); ?></p>
                <?php if (!empty($tweet['image'])): ?>
                    <img src="<?php echo htmlspecialchars($tweet['image']); ?>" alt="Tweet image" style="max-width: 100%; height: auto;">
                <?php endif; ?>
                <small><?php echo htmlspecialchars($tweet['created_at']); ?></small>
                <form method="post" action="home.php">
                    <input type="hidden" name="tweet_id" value="<?php echo htmlspecialchars($tweet['tweet_id']); ?>">
                    <textarea name="comment" placeholder="Write a comment..." required></textarea><br>
                    <input type="submit" value="Comment">
                </form>
                <?php
                // Fetch comments
                $comment_sql = "SELECT comments.comment, users.username, comments.created_at 
                                FROM comments 
                                JOIN users ON comments.user_id = users.id 
                                WHERE comments.tweet_id = ? 
                                ORDER BY comments.created_at ASC";
                $comment_stmt = $conn->prepare($comment_sql);
                $comment_stmt->bind_param("i", $tweet['tweet_id']);
                $comment_stmt->execute();
                $comments = $comment_stmt->get_result();
                while ($comment = $comments->fetch_assoc()): ?>
                    <div class="comment">
                        <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                        <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                        <small><?php echo htmlspecialchars($comment['created_at']); ?></small>
                    </div>
                <?php endwhile; ?>
                <?php $comment_stmt->close(); ?>
            </div>
        <?php endwhile; ?>
        <?php $stmt->close(); ?>
    </div>
</body>
</html>
