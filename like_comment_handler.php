<?php
session_start();
include('db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "You need to be logged in."]);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';  // Safely check if 'action' is set
$post_id = isset($_POST['post_id']) ? $_POST['post_id'] : '';  // Safely check if 'post_id' is set

// Ensure 'action' and 'post_id' are set
if (empty($action) || empty($post_id)) {
    echo json_encode(["error" => "Invalid request."]);
    exit();
}

// Check the action type
if ($action == 'like') {
    // Handle like functionality
    $like_check = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $conn->prepare($like_check);
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If the user already liked the post, remove the like
        $delete_like = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
        $stmt = $conn->prepare($delete_like);
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        $liked = false;
    } else {
        // If the user hasn't liked the post yet, add a like
        $insert_like = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_like);
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        $liked = true;
    }

    // Fetch updated like count
    $like_count_query = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?";
    $stmt = $conn->prepare($like_count_query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->bind_result($like_count);
    $stmt->fetch();

    // Return response with like status and like count
    echo json_encode([
        "like_count" => $like_count,
        "liked" => $liked,  // Whether the user has liked the post or not
        "status" => "success"
    ]);
}
?>
