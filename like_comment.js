// Function to handle the like button click
function likePost(post_id) {
    const likeButton = document.getElementById('like-btn-' + post_id);
    
    // Make AJAX request to add like to the post
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "like_comment_handler.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Update like count
            const response = JSON.parse(xhr.responseText);
            if (response.status === "success") {
                likeButton.innerHTML = 'Like (' + response.like_count + ')';
            } else {
                alert("Error liking the post. Please try again.");
            }
        }
    };
    xhr.send("action=like&post_id=" + post_id);
}

// Function to open the modal and display comments
function commentPost(post_id) {
    const modal = document.getElementById("commentModal");
    const commentsList = document.getElementById("commentsList");
    
    // Clear previous comments
    commentsList.innerHTML = '';
    
    // Make AJAX request to get comments for this post
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "get_comments.php?post_id=" + post_id, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const comments = JSON.parse(xhr.responseText);
            
            // Dynamically load comments
            comments.forEach(comment => {
                const commentDiv = document.createElement("div");
                commentDiv.classList.add("comment");
                commentDiv.innerHTML = `<strong>${comment.user}</strong>: ${comment.text}`;
                
                // Add delete button for the user's own comment
                if (comment.is_user_comment) {
                    const deleteButton = document.createElement("button");
                    deleteButton.classList.add("delete-comment-btn");
                    deleteButton.innerHTML = "Delete";
                    deleteButton.onclick = function() {
                        deleteComment(comment.comment_id, post_id);
                    };
                    commentDiv.appendChild(deleteButton);
                }
                
                commentsList.appendChild(commentDiv);
            });
        }
    };
    xhr.send();
    
    // Show the modal
    modal.style.display = "block";
    
    // Focus on the textarea to ensure keyboard pops up on mobile
    document.getElementById("commentText").focus();
}

// Function to close the modal
function closeModal() {
    const modal = document.getElementById("commentModal");
    modal.style.display = "none";
}

function submitComment(postId, form) {
    var commentContent = form.comment_content.value;

    // Send the comment to the server using AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "submit_comment.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // Data to send to the server
    var data = "post_id=" + postId + "&comment_content=" + encodeURIComponent(commentContent);

    xhr.onload = function() {
        if (xhr.status == 200) {
            // Update the comment section with the new comment (keeping the comment box visible)
            document.getElementById('comment-section-' + postId).innerHTML = xhr.responseText;

            // Clear the comment input field after submission (keeps the comment box visible)
            form.comment_content.value = '';  // Clear the comment box
            
            // Optionally, scroll to the bottom of the comments to view the new comment
            var commentSection = document.getElementById('comment-section-' + postId);
            commentSection.scrollTop = commentSection.scrollHeight;
        } else {
            alert("Error submitting comment.");
        }
    };

    xhr.send(data);

    // Prevent the form from submitting normally
    return false;
}


// Function to handle comment deletion
function deleteComment(commentId, postId) {
    if (!confirm("Are you sure you want to delete this comment?")) {
        return;
    }

    // Make AJAX request to delete the comment
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "delete_comment.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === "success") {
                // Remove the comment from the list
                const commentElement = document.getElementById("comment-" + commentId);
                if (commentElement) {
                    commentElement.remove();
                }
                alert("Comment deleted successfully!");
            } else {
                alert(response.message);
            }
        } else {
            alert("Error deleting comment. Please try again.");
        }
    };
    xhr.send("comment_id=" + commentId + "&post_id=" + postId);
}
