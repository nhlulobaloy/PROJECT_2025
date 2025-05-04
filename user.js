// Function to toggle the comment section visibility
function toggleCommentSection(postId) {
    var commentSection = document.getElementById('comment-section-' + postId);
    if (!commentSection) return;

    // Toggle the comment section visibility
    commentSection.style.display = (commentSection.style.display === "none" || commentSection.style.display === "") ? "block" : "none";
}

// Function to toggle the three-dot options menu for comments
function toggleCommentOptions(commentId) {
    var optionsMenu = document.getElementById('comment-options-' + commentId);
    if (!optionsMenu) return;

    // Close all other open menus
    document.querySelectorAll('.options-menu').forEach(menu => {
        if (menu !== optionsMenu) {
            menu.style.display = 'none';
        }
    });

    // Toggle the selected menu
    optionsMenu.style.display = (optionsMenu.style.display === "none" || optionsMenu.style.display === "") ? "block" : "none";
}

// Close menu when clicking outside
document.addEventListener("click", function (event) {
    if (!event.target.closest('.more-options-btn') && !event.target.closest('.options-menu')) {
        document.querySelectorAll('.options-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

// Function to delete a comment (allow any user to delete)
function deleteComment(commentId) {
    if (!confirm("Are you sure you want to delete this comment?")) {
        return; // Cancel if user doesn't confirm
    }

    // AJAX request to delete comment
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "delete_comment_user.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            console.log(response);  // Debugging: check the response

            if (response.success) {
                // If the comment is deleted successfully, remove it from the DOM
                var commentElement = document.getElementById('comment-' + commentId);
                if (commentElement) {
                    commentElement.remove();
                    alert('Comment deleted successfully.');
                } else {
                    alert('Comment not found in DOM.');
                }
            } else {
                alert("Error deleting comment: " + response.message);
            }
        }
    };
    xhr.send("comment_id=" + commentId);  // Send the comment ID to be deleted
}

// Function to toggle the visibility of the full comment (when clicking the three dots)
function toggleFullComment(commentId) {
    var commentDiv = document.getElementById('comment-' + commentId);
    if (!commentDiv) return;

    // Toggle the visibility of the full comment
    commentDiv.classList.toggle('full-comment');

    // Find the ellipsis text and change it accordingly
    var ellipsis = commentDiv.querySelector('.ellipsis');
    if (commentDiv.classList.contains('full-comment')) {
        ellipsis.textContent = ' Show Less';  // Change to 'Show Less' when full comment is visible
    } else {
        ellipsis.textContent = '...';         // Revert back to '...' when truncated
    }
}

function appendNewComment(comment, postOwnerId, loggedInUserId) {
    var commentHTML = "<div class='comment' id='comment-" + comment.comment_id + "'>";
    commentHTML += "<div class='comment-header'>";

    // Profile picture
    commentHTML += "<img src='data:image/jpeg;base64," + comment.profile_picture + "' alt='Profile Picture' class='comment-user-img'>";

    // Comment user info
    commentHTML += "<strong>" + comment.first_name + " " + comment.last_name + ":</strong>";

    // Three-dot menu for new comments
    commentHTML += "<div class='more-options'>";
    commentHTML += "<button class='more-options-btn' onclick='toggleCommentOptions(" + comment.comment_id + ")'>⋮</button>";
    commentHTML += "<div id='comment-options-" + comment.comment_id + "' class='options-menu' style='display: none;'>";
    commentHTML += "<ul>";
    commentHTML += "<li><a href='report_comment.php?comment_id=" + comment.comment_id + "'>Report</a></li>";

    // Allow deletion only if the post belongs to the logged-in user
    if (postOwnerId === loggedInUserId) {
        commentHTML += "<li><a href='javascript:void(0);' onclick='deleteComment(" + comment.comment_id + ", " + postOwnerId + ", " + loggedInUserId + ")'>Delete</a></li>";
    }

    commentHTML += "</ul>";
    commentHTML += "</div>";
    commentHTML += "</div>";

    commentHTML += "</div>"; // End of comment-header
    commentHTML += "<p>" + comment.comment + "</p>";
    commentHTML += "<small>Posted on: " + new Date(comment.created_at).toLocaleString() + "</small>";
    commentHTML += "</div>"; // End of comment

    // Append the new comment to the section
    document.getElementById('comment-section-' + comment.post_id).innerHTML += commentHTML;
}

// Function to submit a comment
function submitComment(postId, loggedInUserId) {
    var commentContent = document.getElementById('comment-content-' + postId).value;

    if (commentContent.trim() === '') {
        alert("Comment cannot be empty");
        return;
    }

    // AJAX request to submit the comment
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "submit_comment.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Append the new comment to the section
                appendNewComment(response.comment, response.post_owner_id, loggedInUserId);
            } else {
                alert("Error posting comment.");
            }
        }
    };
    xhr.send("post_id=" + postId + "&comment_content=" + encodeURIComponent(commentContent));  // Send comment and post ID
}

// Function to toggle the three-dot options menu for posts
function togglePostOptions(postId) {
    var optionsMenu = document.getElementById('post-options-' + postId);
    if (!optionsMenu) return;

    // Close all other open menus
    document.querySelectorAll('.options-menu').forEach(menu => {
        if (menu !== optionsMenu) {
            menu.style.display = 'none';
        }
    });

    // Toggle the selected menu
    optionsMenu.style.display = (optionsMenu.style.display === "none" || optionsMenu.style.display === "") ? "block" : "none";
}

// Function to show confirmation dialog for post deletion
function confirmDeletePost(postId) {
    if (confirm("Are you sure you want to delete this post?")) {
        deletePost(postId);
    }
}

// Function to delete the post
function deletePost(postId) {
    var formData = new FormData();
    formData.append('post_id', postId);

    fetch('delete_post_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('post-' + postId).remove();
            alert('Post deleted successfully.');
        } else {
            alert('Error deleting post: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the post.');
    });
}
