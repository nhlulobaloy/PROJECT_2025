<?php
session_start();
include('db_connection.php');
include('algorithm.php'); // Include the algorithm script

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = $_GET['query'];
$criteria = $_GET['criteria'];

// Search based on user input
if ($criteria == 'user-name') {
    $sql = "SELECT f.*, u.first_name, u.last_name, u.profile_picture FROM FEED f
            JOIN users u ON f.user_id = u.user_id
            WHERE u.first_name LIKE ? OR u.last_name LIKE ? ORDER BY f.created_at DESC";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
} else if ($criteria == 'content') {
    $sql = "SELECT f.*, u.first_name, u.last_name, u.profile_picture FROM FEED f
            JOIN users u ON f.user_id = u.user_id
            WHERE f.content LIKE ? ORDER BY f.created_at DESC";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("s", $searchTerm);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($post = $result->fetch_assoc()) {
        $formatted_date = date("F j, Y \a\t g:i A", strtotime($post['created_at']));
        
        // Get the number of comments for this post
        $comment_count_sql = "SELECT COUNT(*) AS comment_count FROM comments WHERE post_id = ?";
        $comment_count_stmt = $conn->prepare($comment_count_sql);
        $comment_count_stmt->bind_param("i", $post['post_id']);
        $comment_count_stmt->execute();
        $comment_count_result = $comment_count_stmt->get_result();
        $comment_count = $comment_count_result->fetch_assoc()['comment_count'];

        // Get the number of likes for this post
        $like_count_sql = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?";
        $like_count_stmt = $conn->prepare($like_count_sql);
        $like_count_stmt->bind_param("i", $post['post_id']);
        $like_count_stmt->execute();
        $like_count_result = $like_count_stmt->get_result();
        $like_count_row = $like_count_result->fetch_assoc();
        $like_count = isset($like_count_row['like_count']) ? $like_count_row['like_count'] : 0;  // Default to 0 if no likes

        echo "<div class='post' id='post-" . $post['post_id'] . "'>";
        echo "<div class='post-header'>";
        
        $profile_pic = !empty($post['profile_picture']) ? $post['profile_picture'] : 'default-profile.jpg';
        echo "<a href='user_profile.php?user_id=" . $post['user_id'] . "'>";
        echo "<img src='data:image/jpeg;base64," . base64_encode($profile_pic) . "' alt='Profile Picture' class='post-user-img'>";
        echo "</a>";

        // Use a fallback value if first_name or last_name is null
        $first_name = !empty($post['first_name']) ? htmlspecialchars($post['first_name']) : "Anonymous";
        $last_name = !empty($post['last_name']) ? htmlspecialchars($post['last_name']) : "";

        echo "<a href='user_profile.php?user_id=" . $post['user_id'] . "'>";
        echo "<strong class='user-name'>" . $first_name . " " . $last_name . "</strong>";
        echo "</a>";
        echo "</div>";

        echo "<p class='post-content'>" . htmlspecialchars($post['content']) . "</p>";
        echo "<p class='post-date'>Posted on: " . $formatted_date . "</p>";

        if (!empty($post['media'])) {
            echo '<img src="data:image/jpeg;base64,' . base64_encode($post['media']) . '" alt="Post Media" class="post-media">';
        }

        echo "<div class='post-actions'>";
        echo "<button id='like-btn-" . $post['post_id'] . "' class='like-btn' onclick='likePost(" . $post['post_id'] . ")'>Like (" . $like_count . ")</button>";
        echo "<button id='comment-btn-" . $post['post_id'] . "' class='comment-btn' onclick='showCommentSection(" . $post['post_id'] . ")'>Comment (" . $comment_count . ")</button>";
        echo "</div>";

        echo "<div id='comment-section-" . $post['post_id'] . "' class='comment-section' style='display:none;'>";

        $comments_sql = "SELECT comments.comment, comments.created_at, users.first_name, users.last_name, users.profile_picture FROM comments
                         JOIN users ON comments.user_id = users.user_id
                         WHERE comments.post_id = ? ORDER BY comments.created_at DESC";
        $comments_stmt = $conn->prepare($comments_sql);
        $comments_stmt->bind_param("i", $post['post_id']);
        $comments_stmt->execute();
        $comments_result = $comments_stmt->get_result();

        if ($comments_result->num_rows > 0) {
            while ($comment = $comments_result->fetch_assoc()) {
                echo "<div class='comment'>";
                
                $comment_profile_pic = !empty($comment['profile_picture']) ? $comment['profile_picture'] : 'default-profile.jpg';
                echo "<div class='comment-header'>";
                echo "<img src='data:image/jpeg;base64," . base64_encode($comment_profile_pic) . "' alt='Profile Picture' class='comment-user-img'>";
                echo "<strong>" . htmlspecialchars($comment['first_name']) . " " . htmlspecialchars($comment['last_name']) . ":</strong>";
                echo "</div>";

                echo "<p>" . htmlspecialchars($comment['comment']) . "</p>";
                echo "<small>Posted on: " . date("F j, Y \a\t g:i A", strtotime($comment['created_at'])) . "</small>";
                echo "</div>";
            }
        } else {
            echo "<p>No comments yet. Be the first to comment!</p>";
        }

        echo "<form action='submit_comment.php' method='POST'>";
        echo "<input type='hidden' name='post_id' value='" . $post['post_id'] . "'>";
        echo "<textarea name='comment_content' placeholder='Write a comment...'></textarea>";
        echo "<button type='submit' class='submit-comment-btn'>Submit</button>";
        echo "</form>";

        echo "</div>"; // End of comment-section
        echo "</div>"; // End of post
    }
} else {
    echo "<p>No posts found based on your search.</p>";
}
?>

<script>
    // JavaScript function to display the comment section when the comment button is clicked
    function showCommentSection(postId) {
        const commentSection = document.getElementById('comment-section-' + postId);
        if (commentSection.style.display === "none" || commentSection.style.display === "") {
            commentSection.style.display = "block";
        } else {
            commentSection.style.display = "none";
        }
    }

    // JavaScript for searching posts by content or username
    function searchPosts() {
        const searchTerm = document.getElementById("search-bar").value.toLowerCase();
        const searchCriteria = document.getElementById("search-criteria").value;
        const posts = document.querySelectorAll(".post");

        posts.forEach(post => {
            let content = post.querySelector('.post-content').textContent.toLowerCase();
            let userName = post.querySelector('.user-name').textContent.toLowerCase();
            
            if (searchCriteria === "user-name" && userName.includes(searchTerm) || 
                searchCriteria === "content" && content.includes(searchTerm)) {
                post.style.display = "block";
            } else {
                post.style.display = "none";
            }
        });

        const noResultsMessage = document.getElementById('no-results-message');
        let visiblePosts = Array.from(posts).filter(post => post.style.display !== "none");
        if (visiblePosts.length === 0) {
            noResultsMessage.style.display = "block";
        } else {
            noResultsMessage.style.display = "none";
        }
    }
</script>
