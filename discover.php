<?php 
session_start();
include('db_connection.php');
include('algorithm.php'); 

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch logged-in user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover - SA Talent Hub</title>

    <link rel="stylesheet" href="posts.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="submit_comment.css">
    <script src="like_comment.js" defer></script>
    <script src="discover.js" defer></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include('sidebar.html'); ?>

        <!-- Main Content -->
        <div class="main-content">
            <header id="navbar">
                <h1>Welcome, 
                    <?php echo isset($user['first_name']) ? htmlspecialchars($user['first_name']) : "Guest"; ?>!
                </h1>
                <input type="text" id="search-bar" placeholder="Search for posts..." onkeyup="searchPosts()">
                <select id="search-criteria" onchange="searchPosts()">
                    <option value="user-name">Search by Username</option>
                    <option value="content">Search by Content</option>
                </select>

                <button class="hamburger-menu" onclick="toggleMenu()">☰</button>
            </header>

            <p id="no-results-message" style="display: none; color: red; text-align: center;">No posts found.</p>

            <!-- Include the posts section -->
            <?php include('posts.php'); ?>

        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="posts.js" defer></script>
</body>
</html>
