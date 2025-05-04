<?php
include_once('db_connection.php');

// Start the session
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];  // Get user_id from session

// Get data from the reply form
$comment_id = $_POST['comment_id'];
$reply_content = $_POST['reply_content'];

// Prepare the SQL query to insert the reply into the replies table
$query = 'INSERT INTO replies (comment_id, user_id, reply_content) VALUES (?, ?, ?)';
$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $comment_id, $user_id, $reply_content);

// Execute the query
if ($stmt->execute()) {
    // Fetch all replies for the specific comment, including the most recent reply
    $reply_sql = "
        (SELECT replies.reply_id, replies.reply_content, replies.created_at, users.first_name, users.last_name, users.profile_picture 
         FROM replies
         JOIN users ON replies.user_id = users.user_id
         WHERE replies.comment_id = ?
         ORDER BY replies.created_at DESC LIMIT 1)  -- Get the latest reply
        UNION
        (SELECT replies.reply_id, replies.reply_content, replies.created_at, users.first_name, users.last_name, users.profile_picture 
         FROM replies
         JOIN users ON replies.user_id = users.user_id
         WHERE replies.comment_id = ?
         ORDER BY RAND())";  // Get the rest in random order
    
    $reply_stmt = $conn->prepare($reply_sql);
    $reply_stmt->bind_param("ii", $comment_id, $comment_id);
    $reply_stmt->execute();
    $result = $reply_stmt->get_result();
    
    // Generate HTML for all replies
    $replies_html = '';
    while ($reply = $result->fetch_assoc()) {
        $reply_id = $reply['reply_id'];
        $reply_profile_pic = !empty($reply['profile_picture']) ? $reply['profile_picture'] : 'default-profile.jpg';
        $formatted_date = date("F j, Y \a\t g:i A", strtotime($reply['created_at']));

        $replies_html .= "<div class='reply' id='reply-" . $reply_id . "'>";
        $replies_html .= "<div class='reply-header'>";
        $replies_html .= "<img src='data:image/jpeg;base64," . base64_encode($reply_profile_pic) . "' alt='Profile Picture' class='reply-user-img'>";
        $replies_html .= "<strong>" . htmlspecialchars($reply['first_name']) . " " . htmlspecialchars($reply['last_name']) . ":</strong>";
        $replies_html .= "</div>";
        
        // Check if the reply is too long and add ellipsis
        $reply_text = htmlspecialchars($reply['reply_content']);
        $reply_html = "<p>" . (strlen($reply_text) > 100 ? substr($reply_text, 0, 100) . '...' : $reply_text) . "</p>";
        
        // Add "..." toggle link to reveal the full reply
        if (strlen($reply_text) > 100) {
            $replies_html .= $reply_html . "<span class='ellipsis' onclick='toggleReply(" . $reply_id . ")'>...</span>";
        } else {
            $replies_html .= $reply_html;
        }

        $replies_html .= "<small>Posted on: " . $formatted_date . "</small>";
        $replies_html .= "</div>";
    }

    // Return all the replies' HTML to be injected into the page
    echo json_encode(['success' => true, 'replies_html' => $replies_html]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

// Close the statement and connection
$stmt->close();
$reply_stmt->close();
$conn->close();
?>
