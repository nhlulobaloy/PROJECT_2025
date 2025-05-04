// Hamburger Menu Toggle
document.getElementById('hamburger-menu').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
});

// Toggle Comment Section Visibility for a Post
function toggleCommentSection(post_id) {
    const commentSection = document.getElementById('comment-section-' + post_id);
    const commentBtn = document.getElementById('comment-btn-' + post_id);
    
    if (commentSection) {
        // Toggle the display of the comment section
        const isHidden = commentSection.style.display === 'none' || commentSection.style.display === '';
        if (isHidden) {
            commentSection.style.display = 'block';  // Show comments
            if (commentBtn) commentBtn.innerText = 'Hide Comments';  // Update button text
        } else {
            commentSection.style.display = 'none';  // Hide comments
            if (commentBtn) commentBtn.innerText = 'Show Comments';  // Update button text
        }
    }
}

// Toggle the visibility of the comment input form for a post
function toggleCommentForm(post_id) {
    const form = document.getElementById('comment-form-' + post_id);
    if (form) {
        // Toggle the display of the comment form
        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    }
}

// General Function to Handle Comment Visibility for Multiple Posts
document.querySelectorAll('.comment-btn').forEach(commentBtn => {
    commentBtn.addEventListener('click', (event) => {
        const postId = event.target.getAttribute('data-post-id');
        toggleCommentSection(postId);  // Toggle the visibility of the comment section
    });
});
