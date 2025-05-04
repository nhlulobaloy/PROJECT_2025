// Function to toggle comment section visibility
function toggleCommentSection(postId) {
    var commentSection = document.getElementById('comment-section-' + postId);
    if (!commentSection) return;

    if (commentSection.style.display === "none" || commentSection.style.display === "") {
        commentSection.style.display = "block"; // Show comment section
    } else {
        commentSection.style.display = "none"; // Hide comment section
    }
}

// Function to toggle the three-dot options menu for comments
function toggleCommentOptions(commentId) {
    var optionsMenu = document.getElementById('comment-options-' + commentId);
    if (!optionsMenu) return;

    document.querySelectorAll('.options-menu').forEach(menu => {
        if (menu !== optionsMenu) {
            menu.style.display = 'none';
        }
    });

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

// Function to delete a comment
function deleteComment(commentId) {
    if (!confirm("Are you sure you want to delete this comment?")) {
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "delete_comment.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                var commentElement = document.getElementById('comment-' + commentId);
                if (commentElement) {
                    commentElement.remove();
                }

                var postId = response.post_id;
                var commentSection = document.getElementById('comment-section-' + postId);
                if (commentSection) {
                    var xhrRefresh = new XMLHttpRequest();
                    xhrRefresh.open("GET", "get_comments.php?post_id=" + postId, true);
                    xhrRefresh.onload = function() {
                        if (xhrRefresh.status === 200) {
                            commentSection.innerHTML = xhrRefresh.responseText;
                        } else {
                            alert("Error refreshing comment section.");
                        }
                    };
                    xhrRefresh.send();
                }
            } else {
                alert("You don't have permission to delete the comment.");
            }
        }
    };
    xhr.send("comment_id=" + commentId);
}

// Function to toggle full comment visibility
function toggleFullComment(commentId) {
    var commentDiv = document.getElementById('comment-' + commentId);
    if (!commentDiv) return;

    commentDiv.classList.toggle('full-comment');

    var ellipsis = commentDiv.querySelector('.ellipsis');
    ellipsis.textContent = commentDiv.classList.contains('full-comment') ? ' Show Less' : '...';
}

// Function to append new comments dynamically
function appendNewComment(comment) {
    var commentSection = document.getElementById('comment-section-' + comment.post_id);
    if (!commentSection) return;

    var commentHTML = `
        <div class='comment' id='comment-${comment.comment_id}'>
            <div class='comment-header'>
                <img src='data:image/jpeg;base64,${comment.profile_picture}' alt='Profile Picture' class='comment-user-img'>
                <strong>${comment.first_name} ${comment.last_name}:</strong>
                <div class='more-options'>
                    <button class='more-options-btn' onclick='toggleCommentOptions(${comment.comment_id})'>⋮</button>
                    <div id='comment-options-${comment.comment_id}' class='options-menu' style='display: none;'>
                        <ul>
                            <li><a href='report_comment.php?comment_id=${comment.comment_id}'>Report</a></li>
                            <li><a href='javascript:void(0);' onclick='deleteComment(${comment.comment_id})'>Delete</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <p>${comment.comment}</p>
            <small>Posted on: ${new Date(comment.created_at).toLocaleString()}</small>
        </div>
    `;

    commentSection.innerHTML += commentHTML;
    commentSection.style.display = "block"; // Ensure comment section is visible
}

function submitComment(postId, form) {
    event.preventDefault();  // Prevent the default form submission
    var submitButton = form.querySelector('button[type="submit"]');
    
    // Check if the submit button is disabled to prevent double submission
    if (submitButton.disabled) return;

    submitButton.disabled = true; // Disable the button
    submitButton.innerHTML = '<i class="loading-spinner"></i> Posting...'; // Show loading state

    var formData = new FormData(form);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);

    xhr.onload = function() {
        submitButton.disabled = false; // Re-enable the button
        submitButton.innerHTML = 'Post Comment'; // Reset button text

        if (xhr.status == 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                
                if (response.error === "Duplicate comment detected.") {
                    alert("You've already posted this exact comment.");
                    return;
                }
                
                if (response.html) {
                    var commentSection = document.getElementById('comment-section-' + postId);
                    commentSection.insertAdjacentHTML('afterbegin', response.html);
                    form.reset();
                }
            } catch (e) {
                // Fallback for non-JSON responses
                var commentSection = document.getElementById('comment-section-' + postId);
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = xhr.responseText;
                
                var newComment = tempDiv.querySelector('.comment');
                if (newComment) {
                    commentSection.insertBefore(newComment, commentSection.firstChild);
                    form.reset();
                }
            }
        } else {
            alert("Error posting comment. Please try again.");
        }
    };

    xhr.onerror = function() {
        submitButton.disabled = false;
        submitButton.innerHTML = 'Post Comment';
        alert("Network error. Please check your connection.");
    };

    xhr.send(formData); // Send the form data via AJAX
}

// Function to toggle the three-dot options menu for posts
function togglePostOptions(postId) {
    var optionsMenu = document.getElementById('post-options-' + postId);
    if (!optionsMenu) return;

    document.querySelectorAll('.options-menu').forEach(menu => {
        if (menu !== optionsMenu) {
            menu.style.display = 'none';
        }
    });

    optionsMenu.style.display = (optionsMenu.style.display === "none" || optionsMenu.style.display === "") ? "block" : "none";
}

// Function to show/hide the reply form
function toggleReplyForm(commentId) {
    var replySection = document.getElementById('reply-section-' + commentId);
    if (!replySection) return;

    // Toggle visibility of the reply section
    replySection.style.display = (replySection.style.display === "none" || replySection.style.display === "") ? "block" : "none";
}

// Function to toggle the visibility of replies for a specific comment
function toggleReplies(commentId) {
    var repliesContainer = document.getElementById('replies-container-' + commentId);
    if (repliesContainer.style.display === 'none' || repliesContainer.style.display === '') {
        repliesContainer.style.display = 'block';  // Show replies
    } else {
        repliesContainer.style.display = 'none';  // Hide replies
    }
}

// Function to submit a new reply without reloading the page
function submitReply(commentId, form) {
    event.preventDefault();  // Prevent form submission and page reload

    var formData = new FormData(form);  // Gather form data
    var xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);  // Submit data to the same page (or a specific handler)

    xhr.onload = function () {
        if (xhr.status == 200) {
            var response = JSON.parse(xhr.responseText); // Parse the response

            // Check if the reply was successfully added
            if (response.success) {
                // Create a temporary container to hold the raw HTML
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = response.replies_html;  // Set the reply HTML from the response

                // Get the replies container for the specific comment
                var repliesContainer = document.getElementById('replies-container-' + commentId);

                // Append the new reply to the container
                repliesContainer.appendChild(tempDiv.firstChild);  // Use firstChild to get the new reply element

                // Make sure the replies section remains visible
                repliesContainer.style.display = 'block';

                // Reset the form but keep it visible
                form.reset();
            }
        }
    };

    xhr.send(formData);  // Send form data via AJAX
}
