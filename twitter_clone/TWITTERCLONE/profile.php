<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user profile details
$sql = "SELECT * FROM users WHERE id = $user_id";
$user = $conn->query($sql)->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_tweet'])) {
        $tweet_id = $_POST['tweet_id'];
        $content = $_POST['content'];

        $sql = "UPDATE tweets SET content = '$content' WHERE id = $tweet_id AND user_id = $user_id";
        if ($conn->query($sql) === TRUE) {
            header("Location: profile.php");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    if (isset($_POST['delete_tweet'])) {
        $tweet_id = $_POST['tweet_id'];

        $sql = "DELETE FROM tweets WHERE id = $tweet_id AND user_id = $user_id";
        if ($conn->query($sql) === TRUE) {
            header("Location: profile.php");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $bio = $_POST['bio'];

    if (!empty($_FILES['profile_image']['name'])) {
        $profile_image = $_FILES['profile_image']['name'];
        $target = "uploads/profile_images/" . basename($profile_image);
        $imageFileType = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        $valid_extensions = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
                $sql = "UPDATE users SET bio = '$bio', profile_image = '$profile_image' WHERE id = $user_id";
            } else {
                echo "Sorry, there was an error uploading your file.";
                exit;
            }
        } else {
            echo "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
            exit;
        }
    } else {
        $sql = "UPDATE users SET bio = '$bio' WHERE id = $user_id";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: profile.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch total followers
$sql = "SELECT COUNT(*) AS total_followers FROM follows WHERE followed_id = $user_id";
$followers_count = $conn->query($sql)->fetch_assoc()['total_followers'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="navbar">
        <h1>Friendster</h1>
        <nav>
            <a href="home.php">Home</a>
            <a href="profile.php">Profile</a>
            <a href="search.php">Search</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
    <div class="profile-header">
        <div class="profile-banner"></div>
        <div class="profile-info">
            <img src="uploads/profile_images/<?php echo $user['profile_image']; ?>" alt="Profile Image" class="profile-image">
            <div class="profile-details">
                <h2><?php echo $user['username']; ?> (<?php echo $followers_count; ?> followers)</h2>
                <p>@<?php echo $user['username']; ?></p>
                <p><?php echo $user['bio']; ?></p>
            </div>
        </div>
    </div>
    <div class="container">
        <h3>Edit Profile</h3>
        <form method="post" action="profile.php" enctype="multipart/form-data">
            <textarea name="bio" placeholder="Bio"><?php echo $user['bio']; ?></textarea><br>
            <input type="file" name="profile_image"><br>
            <input type="submit" value="Update Profile">
        </form>
        <h3>Your Tweets</h3>
        <?php
        $sql = "SELECT tweets.id as tweet_id, tweets.content, tweets.created_at, users.profile_image as tweet_user_image
                FROM tweets
                JOIN users ON tweets.user_id = users.id
                WHERE tweets.user_id = $user_id
                ORDER BY tweets.created_at DESC";
        $tweets = $conn->query($sql);
        while ($tweet = $tweets->fetch_assoc()): ?>
            <div class="tweet">
                <img src="uploads/profile_images/<?php echo $tweet['tweet_user_image']; ?>" alt="User Image" class="tweet-image">
                <p><?php echo $tweet['content']; ?></p>
                <small><?php echo $tweet['created_at']; ?></small>
                <form method="post" action="profile.php" style="display:inline;">
                    <input type="hidden" name="tweet_id" value="<?php echo $tweet['tweet_id']; ?>">
                    <input type="text" name="content" value="<?php echo $tweet['content']; ?>">
                    <input type="submit" name="update_tweet" value="Update">
                </form>
                <form method="post" action="profile.php" style="display:inline;">
                    <input type="hidden" name="tweet_id" value="<?php echo $tweet['tweet_id']; ?>">
                    <input type="submit" name="delete_tweet" value="Delete">
                </form>

                <?php
                $tweet_id = $tweet['tweet_id'];
                $sql_comments = "SELECT comments.comment as comment_content, comments.created_at as comment_created_at, users.username as commenter_name, users.profile_image as commenter_image
                                 FROM comments
                                 JOIN users ON comments.user_id = users.id
                                 WHERE comments.tweet_id = $tweet_id
                                 ORDER BY comments.created_at ASC";
                $comments = $conn->query($sql_comments);
                while ($comment = $comments->fetch_assoc()): ?>
                    <div class="comment">
                        <img src="uploads/profile_images/<?php echo $comment['commenter_image']; ?>" alt="Commenter Image" class="comment-image">
                        <p><strong><?php echo $comment['commenter_name']; ?>:</strong> <?php echo $comment['comment_content']; ?></p>
                        <small><?php echo $comment['comment_created_at']; ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
