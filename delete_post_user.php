<?php
// Include database connection
require_once 'db_connection.php';  // Include your database connection file

// Check if it's a POST request and if post_id is set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_id'])) {
    // Get the post ID
    $post_id = $_POST['post_id'];

    // Sanitize and validate post_id
    $post_id = (int) $post_id;  // Ensure it's an integer

    // Prepare the SQL query to delete the post from the 'feed' table
    $sql = "DELETE FROM feed WHERE post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);

    // Execute the query and check the result
    if ($stmt->execute()) {
        // If deletion is successful
        echo json_encode(['success' => true]);
    } else {
        // If there was an error deleting
        echo json_encode(['success' => false, 'message' => 'Failed to delete post.']);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // If invalid request
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
