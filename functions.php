<?php
include_once('db_connection.php');

// Function to get posts for a specific user
function getPostsForUser($user_id, $conn) {
    if (!$conn) {
        die("Database connection error.");
    }

    // SQL query to get the posts along with the like_count from the likes table
    $sql = "SELECT feed.post_id, feed.content, feed.created_at, feed.media, 
            users.first_name, users.last_name, users.profile_picture, feed.user_id 
            FROM feed 
            JOIN users ON feed.user_id = users.user_id 
            WHERE feed.user_id = ? 
            ORDER BY feed.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL preparation error: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        // Get the like count dynamically
        $like_count_sql = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?";
        $like_count_stmt = $conn->prepare($like_count_sql);
        $like_count_stmt->bind_param("i", $row['post_id']);
        $like_count_stmt->execute();
        $like_count_result = $like_count_stmt->get_result();
        $like_count = $like_count_result->fetch_assoc()['like_count'];

        // Add the like_count to the post data
        $row['like_count'] = $like_count;

        // Add post to the array
        $posts[] = $row;
    }

    $stmt->close(); // Free up resources
    return $posts;
}
?>
