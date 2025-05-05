<?php
include_once('db_connection.php');
include_once('algorithm.php');  // Inlude the algorithm file for fetching posts based on user preferences

// Start the session only if it is not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Fetch posts for the logged-in user from the algorithm, considering user preferences
$posts = getPostsForUser($user_id, $conn);

// Check if there are posts available
?>

<section id="feed-section" class="feed-section">
    <h3>Discover</h3>
    <?php
    if (!empty($posts)) {
        foreach ($posts as $post) {
            $formatted_date = date("F j, Y \a\t g:i A", strtotime($post['created_at']));

            // Get the number of comments and replies for this post
            $comment_count_sql = "SELECT COUNT(*) AS comment_count 
                                  FROM comments WHERE post_id = ? 
                                  UNION ALL 
                                  SELECT COUNT(*) 
                                  FROM replies WHERE comment_id IN (SELECT comment_id FROM comments WHERE post_id = ?)";
            $comment_count_stmt = $conn->prepare($comment_count_sql);
            $comment_count_stmt->bind_param("ii", $post['post_id'], $post['post_id']);
            $comment_count_stmt->execute();
            $comment_count_result = $comment_count_stmt->get_result();
            
            // Combine the results of both counts (comments and replies)
            $comment_count = 0;
            while ($row = $comment_count_result->fetch_assoc()) {
                $comment_count += (int)$row['comment_count'];
            }

            echo "<div class='post' id='post-" . $post['post_id'] . "'>";
            echo "<div class='post-header'>";

            // Profile picture
            $profile_pic = !empty($post['profile_picture']) ? $post['profile_picture'] : 'default-profile.jpg';
            echo "<a href='user_profile.php?user_id=" . htmlspecialchars($post['user_id']) . "'>";
            echo "<img src='data:image/jpeg;base64," . base64_encode($profile_pic) . "' alt='Profile Picture' class='post-user-img'>";
            echo "</a>";

            // Display the user name
            echo "<a href='user_profile.php?user_id=" . htmlspecialchars($post['user_id']) . "'>";
            echo "<strong class='user-name'>" . htmlspecialchars($post['first_name']) . " " . htmlspecialchars($post['last_name']) . "</strong>";
            echo "</a>";

            // Always show the three dots button for every post
            echo "<div class='more-options'>";
            echo "<button class='more-options-btn' onclick='togglePostOptions(" . $post['post_id'] . ")'>&#x2022;&#x2022;&#x2022;</button>";
            echo "<div id='post-options-" . $post['post_id'] . "' class='options-menu' style='display:none;'>";
            echo "<ul>";
            echo "<li><a href='save_post.php?post_id=" . $post['post_id'] . "'>Save</a></li>";
            echo "<li><a href='share_post.php?post_id=" . $post['post_id'] . "'>Share</a></li>";
            echo "<li><a href='report_post.php?post_id=" . $post['post_id'] . "'>Report</a></li>";
            echo "</ul>";
            echo "</div>";
            echo "</div>";

            echo "</div>"; // End of post header

            // Display the post content
            echo "<p class='post-content'>" . htmlspecialchars($post['content']) . "</p>";
            echo "<p class='post-date'>Posted on: " . $formatted_date . "</p>";

            // If the post contains media, render it properly
            if (!empty($post['media'])) {
                echo '<img src="data:image/jpeg;base64,' . $post['media'] . '" alt="Post Media" class="post-media">';
            }

            // Display actions
            echo "<div class='post-actions'>";
            echo "<button id='like-btn-" . $post['post_id'] . "' class='like-btn' onclick='likePost(" . $post['post_id'] . ")'>Like (" . $post['like_count'] . ")</button>";
            echo "<button id='comment-btn-" . $post['post_id'] . "' class='comment-btn' data-post-id='" . $post['post_id'] . "' onclick='toggleCommentSection(" . $post['post_id'] . ")'>Comment (" . $comment_count . ")</button>";
            echo "</div>";
             
            // Comment Section
            echo "<div id='comment-section-" . $post['post_id'] . "' class='comment-section' style='display:none;'>";

            $comments_sql = "SELECT comments.comment, comments.created_at, comments.comment_id, users.first_name, users.last_name, users.profile_picture, comments.user_id 
                            FROM comments
                            JOIN users ON comments.user_id = users.user_id
                            WHERE comments.post_id = ? ORDER BY comments.created_at DESC";
            $comments_stmt = $conn->prepare($comments_sql);
            $comments_stmt->bind_param("i", $post['post_id']);
            $comments_stmt->execute();
            $comments_result = $comments_stmt->get_result();

            if ($comments_result->num_rows > 0) {
                while ($comment = $comments_result->fetch_assoc()) {
                    echo "<div class='comment' id='comment-" . $comment['comment_id'] . "'>";
                    $comment_profile_pic = !empty($comment['profile_picture']) ? $comment['profile_picture'] : 'default-profile.jpg';

                    echo "<div class='comment-header'>";

                    // Three dots menu for each comment
                    echo "<div class='more-options'>";
                    echo "<button class='more-options-btn' onclick='toggleCommentOptions(" . $comment['comment_id'] . ")'>&#x2022;&#x2022;&#x2022;</button>";
                    echo "<div id='comment-options-" . $comment['comment_id'] . "' class='options-menu' style='display:none;'>";
                    echo "<ul>";
                    echo "<li><a href='javascript:void(0);' onclick='toggleReplyForm(" . $comment['comment_id'] . ")'>Reply</a></li>";
                    echo "<li><a href='report_comment.php?comment_id=" . $comment['comment_id'] . "'>Report</a></li>";
                    echo "<li><a href='javascript:void(0);' onclick='deleteComment(" . $comment['comment_id'] . ")'>Delete</a></li>";
                    echo "</ul>";
                    echo "</div>";
                    echo "</div>";
                    
                    // Profile link for the commenter
                    echo "<a href='user_profile.php?user_id=" . htmlspecialchars($comment['user_id']) . "'>";
                    echo "<img src='data:image/jpeg;base64," . base64_encode($comment_profile_pic) . "' alt='Profile Picture' class='comment-user-img'>";
                    echo "<strong>" . htmlspecialchars($comment['first_name']) . " " . htmlspecialchars($comment['last_name']) . ":</strong>";
                    echo "</a>";

                    echo "</div>";

                    echo "<p>" . htmlspecialchars($comment['comment']) . "</p>";
                    echo "<small>Posted on: " . date("F j, Y \a\t g:i A", strtotime($comment['created_at'])) . "</small>";

                    // Button to toggle the replies visibility
                    echo "<button class='toggle-replies-btn' onclick='toggleReplies(" . $comment['comment_id'] . ")'>Replies</button>";

                    // Add a section to show the replies if any exist
                    echo "<div id='replies-container-" . $comment['comment_id'] . "' class='replies-container' style='display:none;'>";

                    // Fetch replies for this comment
                    $replies_sql = "SELECT replies.reply_content, replies.created_at, users.first_name, users.last_name, users.profile_picture, replies.reply_id
                    FROM replies 
                    JOIN users ON replies.user_id = users.user_id 
                    WHERE replies.comment_id = ? ORDER BY replies.created_at DESC";
    $replies_stmt = $conn->prepare($replies_sql);
    $replies_stmt->bind_param("i", $comment['comment_id']);
    $replies_stmt->execute();
    $replies_result = $replies_stmt->get_result();
    
    if ($replies_result->num_rows > 0) {
        while ($reply = $replies_result->fetch_assoc()) {
            echo "<div class='reply'>";

            // Profile picture
            $reply_profile_pic = !empty($reply['profile_picture']) ? $reply['profile_picture'] : 'default-profile.jpg';
            
            // Three dots menu for replies
            echo "<div class='more-options'>";
            echo "<button class='more-options-btn' onclick='toggleReplyOptions(" . $reply['reply_id'] . ")'>&#x2022;&#x2022;&#x2022;</button>";
            echo "<div id='reply-options-" . $reply['reply_id'] . "' class='options-menu' style='display:none;'>";
            echo "<ul>";
            echo "<li><a href='report_reply.php?reply_id=" . $reply['reply_id'] . "'>Report</a></li>";
            echo "<li><a href='javascript:void(0);' onclick='deleteReply(" . $reply['reply_id'] . ")'>Delete</a></li>";
            echo "</ul>";
            echo "</div>";
            echo "</div>"; // End of more-options for reply
            
            // Reply header
            echo "<div class='reply-header'>";
            echo "<img src='data:image/jpeg;base64," . base64_encode($reply_profile_pic) . "' alt='Profile Picture' class='reply-user-img'>";
            echo "<strong>" . htmlspecialchars($reply['first_name']) . " " . htmlspecialchars($reply['last_name']) . "</strong>";
            echo "<small>Posted on: " . date("F j, Y \a\t g:i A", strtotime($reply['created_at'])) . "</small>";
            echo "</div>";
            
            // Reply content
            echo "<p>" . htmlspecialchars($reply['reply_content']) . "</p>";
            
            echo "</div>"; // End of reply
            
                        }
                    } else {
                        echo "<p>No replies yet.</p>";
                    }

                    echo "</div>"; // End of replies container

                    // Reply section for each comment
                    echo "<div id='reply-section-" . $comment['comment_id'] . "' class='reply-section' style='display:none;'>";
                    echo "<form class='reply-form' action='submit_reply.php' method='POST' onsubmit='return submitReply(" . $comment['comment_id'] . ", this);'>";
                    echo "<input type='hidden' name='comment_id' value='" . $comment['comment_id'] . "'>";
                    echo "<textarea name='reply_content' placeholder='Write a reply...' required></textarea>";
                    echo "<button type='submit'>Post Reply</button>";
                    echo "</form>";
                    echo "</div>"; // End of reply section

                    echo "</div>"; // End of comment
                }
            }

            // Comment form
            echo "<form id='comment-form-" . $post['post_id'] . "' class='comment-form' action='submit_comment.php' method='POST' onsubmit='return submitComment(" . $post['post_id'] . ", this);'>";
            echo "<input type='hidden' name='post_id' value='" . $post['post_id'] . "'>";
            echo "<textarea name='comment_content' placeholder='Write a comment...' required></textarea>";
            echo "<button type='submit'>Post Comment</button>";
            echo "</form>";

            echo "</div>"; // End of comment section
            echo "</div>"; // End of post
        }
    } else {
        echo "<p>No posts available.</p>";
    }
    ?>
</section>
