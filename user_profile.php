<?php  
session_start();
include('db_connection.php');  

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Get the profile user ID
if (!isset($_GET['user_id'])) {
    header("Location: discover.php"); 
    exit();
}

$profile_user_id = $_GET['user_id'];

// Fetch user data
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// Check if logged-in user follows profile user
$follow_check_query = "SELECT * FROM follows WHERE follower_id = ? AND following_id = ?";
$follow_check_stmt = $conn->prepare($follow_check_query);
$follow_check_stmt->bind_param("ii", $current_user_id, $profile_user_id);
$follow_check_stmt->execute();
$is_following = $follow_check_stmt->get_result()->num_rows > 0;

// Handle follow/unfollow actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['follow_action'])) {
    if ($_POST['follow_action'] === 'follow') {
        $insert_follow_query = "INSERT INTO follows (follower_id, following_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_follow_query);
        $stmt->bind_param("ii", $current_user_id, $profile_user_id);
        $stmt->execute();
        $_SESSION['message'] = "You are now following this user.";
    } elseif ($_POST['follow_action'] === 'unfollow') {
        $delete_follow_query = "DELETE FROM follows WHERE follower_id = ? AND following_id = ?";
        $stmt = $conn->prepare($delete_follow_query);
        $stmt->bind_param("ii", $current_user_id, $profile_user_id);
        $stmt->execute();
        $_SESSION['message'] = "You have unfollowed this user.";
    }

    header("Location: user_profile.php?user_id=$profile_user_id");
    exit();
}

// Fetch followers and following count
$followers_stmt = $conn->prepare("SELECT COUNT(*) AS follower_count FROM follows WHERE following_id = ?");
$followers_stmt->bind_param("i", $profile_user_id);
$followers_stmt->execute();
$followers_count = $followers_stmt->get_result()->fetch_assoc()['follower_count'];

$following_stmt = $conn->prepare("SELECT COUNT(*) AS following_count FROM follows WHERE follower_id = ?");
$following_stmt->bind_param("i", $profile_user_id);
$following_stmt->execute();
$following_count = $following_stmt->get_result()->fetch_assoc()['following_count'];

// Fetch total likes
$total_likes_stmt = $conn->prepare("SELECT COUNT(*) AS total_likes FROM likes WHERE post_id IN (SELECT post_id FROM feed WHERE user_id = ?)");
$total_likes_stmt->bind_param("i", $profile_user_id);
$total_likes_stmt->execute();
$total_likes_count = $total_likes_stmt->get_result()->fetch_assoc()['total_likes'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - SA Talent Hub</title>
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="posts.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="submit_comment.css">
    <link rel="stylesheet" href="discover.css">
    <script src="like_comment.js"></script>
    <script src="discover.js" defer></script>
    <script src="three-dots.js" defer></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside>
            <?php include('sidebar.html'); ?>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <header id="navbar">
                <h1><?= htmlspecialchars($user['first_name'] ?? "Guest"); ?>!</h1>

                <!-- Three Dots Menu -->
                <div class="user-menu">
                    <button class="user-menu-btn" onclick="toggleUserMenu()">⋮</button>
                    <div class="user-menu-options" id="userMenuOptions">
                        <button onclick="alert('Restricted!')">Restrict</button>
                        <button onclick="alert('Blocked!')">Block</button>
                        <button onclick="alert('Reported!')">Report</button>
                        <button onclick="alert('About this user')">About</button>
                        <button onclick="copyProfileUrl()">Copy Profile URL</button>
                        <button onclick="shareProfile()">Share Profile</button>
                    </div>
                </div>
                <button class="hamburger-menu" onclick="toggleMenu()">☰</button>
            </header>

            <div class="profile-container">
                <?php if (isset($_SESSION['message'])) : ?>
                    <div class='alert alert-success'><?= $_SESSION['message']; ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <div class="profile-pic">
    <?php 
    $profile_pic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default-profile.jpg';
    echo "<img src='data:image/jpeg;base64," . base64_encode($profile_pic) . "' alt='Profile Picture'>"; 
    ?>
</div>

                    <div class="profile-info">
                        <h2><?= htmlspecialchars($user['first_name'] ?? '') . " " . htmlspecialchars($user['last_name'] ?? ''); ?></h2>
                        <p><strong>Joined:</strong> <?= date("F j, Y", strtotime($user['created_at'])); ?></p>
                        <p><strong>Bio:</strong> <?= htmlspecialchars($user['bio'] ?? 'No bio available.'); ?></p>
                        <p><strong>Talent:</strong> <?= htmlspecialchars($user['talent'] ?? 'No talent listed.'); ?></p>
                        <p><strong>Followers:</strong> <?= $followers_count; ?></p>
                        <p><strong>Following:</strong> <?= $following_count; ?></p>
                        <p><strong>Total Likes:</strong> <?= $total_likes_count; ?></p>
                    </div>
                </div>

                <form action="user_profile.php?user_id=<?= $profile_user_id; ?>" method="post">
                    <button type="submit" name="follow_action" value="<?= $is_following ? 'unfollow' : 'follow'; ?>" class="follow-btn">
                        <?= $is_following ? 'Following' : 'Follow'; ?>
                    </button>
                </form>

                <div class="user-posts">
                    <h3>Posts by <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></h3>

                    <!-- Include user posts -->
                    <?php include('posts.php'); ?>
                    <script src="posts.js" defer></script>
                </div>
            </div>
        </div>
    </div>

    <script src="delete_post_user.js"></script>
</body>
</html>
