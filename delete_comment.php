<?php
include_once('db_connection.php');

// Ensure the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Check if the necessary data is sent
if (isset($_POST['comment_id'])) {
    $comment_id = (int)$_POST['comment_id']; // Get the comment ID from the POST request

    // Verify if the comment exists and belongs to the logged-in user
    $sql = "SELECT * FROM comments WHERE comment_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Delete the comment
        $delete_sql = "DELETE FROM comments WHERE comment_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $comment_id);
        if ($delete_stmt->execute()) {
            // Return success response
            echo json_encode(["success" => true]);
        } else {
            // Return error response
            echo json_encode(["success" => false, "message" => "Error deleting comment."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Comment not found or you do not have permission to delete it."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No comment ID provided."]);
}
?>
