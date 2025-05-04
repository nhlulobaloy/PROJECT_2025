// JavaScript to make the navbar visible when scrolling up
let lastScrollTop = 0;

window.addEventListener('scroll', function() {
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    const navbar = document.getElementById('navbar');

    if (currentScroll > lastScrollTop) {
        // Scrolling down
        navbar.style.top = "-50px"; // Hide navbar
    } else {
        // Scrolling up
        navbar.style.top = "0"; // Show navbar
    }
    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // For Mobile or negative scrolling
});

// JavaScript function to display the comment section when comment button is clicked
function showCommentSection(postId) {
    const commentSection = document.getElementById('comment-section-' + postId);
    if (commentSection.style.display === "none" || commentSection.style.display === "") {
        commentSection.style.display = "block"; // Show the comment section
    } else {
        commentSection.style.display = "none"; // Hide the comment section
    }
}

// JavaScript to toggle the three-dots menu (options menu) for a specific post
function toggleOptions(postId) {
    const optionsMenu = document.getElementById('options-' + postId);
    optionsMenu.style.display = (optionsMenu.style.display === "block") ? "none" : "block"; // Toggle options menu
}

// Close the options menu if clicking outside of it
document.addEventListener('click', function(event) {
    const isClickInside = event.target.closest('.post-header');
    const isClickOnOptionsButton = event.target.classList.contains('more-options-btn');
    
    // Close all open options menus if clicking outside
    if (!isClickInside && !isClickOnOptionsButton) {
        const openMenus = document.querySelectorAll('.options-menu');
        openMenus.forEach(menu => menu.style.display = 'none');
    }
});

// Search posts based on user input and criteria
function searchPosts() {
    const searchTerm = document.getElementById("search-bar").value.toLowerCase();
    const searchCriteria = document.getElementById("search-criteria").value;
    const posts = document.querySelectorAll(".post");

    posts.forEach(post => {
        let content = post.querySelector('.post-content').textContent.toLowerCase();
        let userName = post.querySelector('.user-name').textContent.toLowerCase();

        // Determine if post matches search criteria
        if (searchCriteria === "user-name" && userName.includes(searchTerm) || 
            searchCriteria === "content" && content.includes(searchTerm)) {
            post.style.display = "block"; // Show post if it matches
        } else {
            post.style.display = "none"; // Hide post if it doesn't match
        }
    });

    const noResultsMessage = document.getElementById('no-results-message');
    let visiblePosts = Array.from(posts).filter(post => post.style.display !== "none");
    noResultsMessage.style.display = (visiblePosts.length === 0) ? "block" : "none";
}

// JavaScript to handle AJAX comment form submission
document.addEventListener("DOMContentLoaded", function() {
    const forms = document.querySelectorAll('form[id^="comment-form-"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();  // Prevents the default form submission
            
            const formData = new FormData(form);
            const postId = formData.get('post_id'); // Get the post ID
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Assuming the response is the updated comment list or a success message
                    const commentSection = document.getElementById('comment-section-' + postId);
                    commentSection.innerHTML = xhr.responseText; // Update comment section with the new comment
                    form.querySelector('textarea').value = ''; // Clear the textarea
                } else {
                    alert('Error submitting comment!');
                }
            };
            xhr.send(formData);  // Send form data via AJAX
        });
    });
});

// JavaScript to toggle the sidebar visibility
document.addEventListener("DOMContentLoaded", function () {
    const hamburger = document.querySelector('.hamburger-menu');
    const sidebar = document.querySelector('.sidebar');

    hamburger.addEventListener("click", function () {
        sidebar.classList.toggle("active"); // Toggle sidebar visibility
    });
});
