<?php
session_start();
include('db_connection.php'); // Include the database connection

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT first_name, profile_picture, created_at, talent_type, bio FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $first_name = htmlspecialchars($user['first_name']);
    $profile_picture = !empty($user['profile_picture']) ? 'data:image/jpeg;base64,' . base64_encode($user['profile_picture']) : 'default.jpg';
} else {
    echo "User not found.";
    exit();
}

// Get the number of followers and following
$followers_sql = "SELECT COUNT(*) AS followers_count FROM follows WHERE following_id = ?";
$followers_stmt = $conn->prepare($followers_sql);
$followers_stmt->bind_param("s", $user_id);
$followers_stmt->execute();
$followers_result = $followers_stmt->get_result();
$followers_count = $followers_result->fetch_assoc()['followers_count'];

$following_sql = "SELECT COUNT(*) AS following_count FROM follows WHERE follower_id = ?";
$following_stmt = $conn->prepare($following_sql);
$following_stmt->bind_param("s", $user_id);
$following_stmt->execute();
$following_result = $following_stmt->get_result();
$following_count = $following_result->fetch_assoc()['following_count'];

// Get total likes count for the user (based on their posts)
$likes_sql = "SELECT COUNT(*) AS total_likes FROM likes WHERE post_id IN (SELECT post_id FROM feed WHERE user_id = ?)";
$likes_stmt = $conn->prepare($likes_sql);
$likes_stmt->bind_param("s", $user_id);
$likes_stmt->execute();
$likes_result = $likes_stmt->get_result();
$total_likes = $likes_result->fetch_assoc()['total_likes'];

// Post Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_post'])) {
    $post_content = $_POST['post_content'];
    $media = '';

    // Handle media upload (image, audio, video)
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
        $image = $_FILES['post_image'];
        $image_name = $image['name'];
        $image_tmp = $image['tmp_name'];
        $image_type = $image['type'];
        $image_size = $image['size'];

        // Check file size (limit to 5MB)
        if ($image_size > 5242880) { // 5MB
            echo "File is too large. Please upload a file smaller than 5MB.";
            exit();
        }

        // Allow only image, audio, and video types
        if (strpos($image_type, 'image') !== false) {
            $media = base64_encode(file_get_contents($image_tmp));
        } elseif (strpos($image_type, 'audio') !== false) {
            $media = base64_encode(file_get_contents($image_tmp));
        } elseif (strpos($image_type, 'video') !== false) {
            $media = base64_encode(file_get_contents($image_tmp));
        } else {
            echo "Invalid file type! Only image, audio, and video files are allowed.";
            exit();
        }
    }

    // Insert the post into the database (Updated query)
    $insert_sql = "INSERT INTO feed (user_id, content, media, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sss", $user_id, $post_content, $media);

    if ($stmt->execute()) {
        // Redirect to avoid resubmitting the form upon page refresh
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error creating post: " . $stmt->error;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SA Talent Hub</title>
    <link rel="stylesheet" href="posts.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="submit_comment.css">
    <script src="like_comment.js" defer></script>
    <script src="discover.js" defer></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.html'; ?> <!-- Include sidebar here -->

        <div class="main-content">
            <header id="navbar">
                <h1>Welcome, <?php echo $first_name; ?>!</h1>
                <button class="hamburger-menu" onclick="toggleMenu()">☰</button>
            </header>

            <div class="user-profile">
                <h2>Hello, <?php echo $first_name; ?>!</h2>
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="profile-picture">
                <p><strong>Joined:</strong> <?php echo date("F j, Y", strtotime($user['created_at'])); ?></p>
                <p><strong>Talent:</strong> <?php echo htmlspecialchars($user['talent_type']); ?></p>
                <p><strong>Bio:</strong> <?php echo htmlspecialchars($user['bio']); ?></p>
                <div class="stats">
                    <p><strong>Followers:</strong> <?php echo $followers_count; ?></p>
                    <p><strong>Following:</strong> <?php echo $following_count; ?></p>
                    <p><strong>Total Likes:</strong> <?php echo $total_likes; ?></p>
                </div>
            </div>

            <!-- Post Creation Form -->
            <div class="create-post">
                <h3>Create a Post</h3>
                <form method="POST" enctype="multipart/form-data">
                    <textarea name="post_content" placeholder="What's on your mind?" required></textarea>
                    <input type="file" name="post_image" accept="image/*,audio/*,video/*">
                    <button type="submit" name="create_post">Post</button>
                </form>
            </div>

            <!-- Embed the user_posts.php here to display posts -->
            <?php include 'user_posts.php'; ?>
        </div>
    </div>

<script src="user.js" defer></script>
</body>
</html>
