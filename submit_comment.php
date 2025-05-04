<?php
// Start the session to access session variables
session_start();

// Include database connection
include_once('db_connection.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];  // Get user_id from session

// Validate the post_id and comment_content are set
if (isset($_POST['post_id']) && isset($_POST['comment_content'])) {
    $post_id = $_POST['post_id'];
    $comment_content = $_POST['comment_content'];

    // Prepare and bind the SQL query to insert the comment
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $user_id, $comment_content);

    // Execute the query
    if ($stmt->execute()) {
        // After inserting the new comment, fetch all comments for the specific post
        $comment_sql = "
            (SELECT comments.comment_id, comments.comment, comments.created_at, users.first_name, users.last_name, users.profile_picture 
             FROM comments
             JOIN users ON comments.user_id = users.user_id
             WHERE comments.post_id = ?
             ORDER BY comments.created_at DESC LIMIT 1)  -- Get the latest comment
            UNION
            (SELECT comments.comment_id, comments.comment, comments.created_at, users.first_name, users.last_name, users.profile_picture 
             FROM comments
             JOIN users ON comments.user_id = users.user_id
             WHERE comments.post_id = ?
             ORDER BY RAND())";  // Get the rest in random order
        
        $comment_stmt = $conn->prepare($comment_sql);
        $comment_stmt->bind_param("ii", $post_id, $post_id);
        $comment_stmt->execute();
        $result = $comment_stmt->get_result();
        
        // Generate HTML for all comments
        $comments_html = '';
        while ($comment = $result->fetch_assoc()) {
            $comment_id = $comment['comment_id'];
            $comment_profile_pic = !empty($comment['profile_picture']) ? $comment['profile_picture'] : 'default-profile.jpg';
            $formatted_date = date("F j, Y \a\t g:i A", strtotime($comment['created_at']));

            $comments_html .= "<div class='comment' id='comment-" . $comment_id . "'>";
            $comments_html .= "<div class='comment-header'>";
            $comments_html .= "<img src='data:image/jpeg;base64," . base64_encode($comment_profile_pic) . "' alt='Profile Picture' class='comment-user-img'>";
            $comments_html .= "<strong>" . htmlspecialchars($comment['first_name']) . " " . htmlspecialchars($comment['last_name']) . ":</strong>";
            $comments_html .= "</div>";
            
            // Check if the comment is too long and add ellipsis
            $comment_text = htmlspecialchars($comment['comment']);
            $comment_html = "<p>" . (strlen($comment_text) > 100 ? substr($comment_text, 0, 100) . '...' : $comment_text) . "</p>";
            
            // Add "..." toggle link to reveal the full comment
            if (strlen($comment_text) > 100) {
                $comments_html .= $comment_html . "<span class='ellipsis' onclick='toggleComment(" . $comment_id . ")'>...</span>";
            } else {
                $comments_html .= $comment_html;
            }

            $comments_html .= "<small>Posted on: " . $formatted_date . "</small>";
            $comments_html .= "</div>";
        }

        // Return all the comments' HTML
        echo $comments_html;
    } else {
        echo "Error posting comment: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
    $comment_stmt->close();
} else {
    echo "Post ID or comment content is missing.";
}

// Close the database connection
$conn->close();
?>
