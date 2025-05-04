// Existing function to check if an account exists (simulating backend check with AJAX)
async function checkAccountExists() {
    let email = document.getElementById('email').value;
    
    try {
        const response = await fetch('check_account_exists.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email })
        });
        const data = await response.json();

        if (data.exists) {
            alert("This account already exists! Please login instead.");
            return false;
        }

        return true; // Proceed with the form submission if account doesn't exist
    } catch (error) {
        console.error('Error checking account:', error);
        alert('An error occurred while checking the account. Please try again.');
        return false;
    }
}

document.querySelector('.login-form').addEventListener('submit', async function(event) {
    if (!(await checkAccountExists())) {
        event.preventDefault();
    }
});

// New function to handle profile form submission with animation
async function handleProfileFormSubmission(event) {
    event.preventDefault(); // Prevent the default form submission

    const formData = new FormData(event.target);

    try {
        const response = await fetch('update_profile.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        const successMessage = document.getElementById('success-message');
        if (result.status === 'success') {
            successMessage.textContent = result.message;
            successMessage.style.display = 'block';

            // Add the "show" class for the slide-in animation
            setTimeout(() => {
                successMessage.classList.add('show');
            }, 10);

            // Hide the message after 3 seconds
            setTimeout(() => {
                successMessage.classList.remove('show');
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 500); // Wait for the slide-out animation to finish
            }, 3000); // Keep the message visible for 3 seconds
        } else {
            alert(result.message); // Show error message if not successful
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        alert('An error occurred while updating your profile. Please try again.');
    }
}

document.querySelector('.edit-profile-form').addEventListener('submit', handleProfileFormSubmission);

// Optional: If you want to handle event form submissions
async function handleEventFormSubmission(event) {
    event.preventDefault(); // Prevent the default form submission

    const formData = new FormData(event.target);

    try {
        const response = await fetch('add_event.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        const successMessage = document.getElementById('success-message');
        if (data.status === 'success') {
            successMessage.textContent = data.message;
            successMessage.style.display = 'block';

            // Add the "show" class for the slide-in animation
            setTimeout(() => {
                successMessage.classList.add('show');
            }, 10);

            // Hide the message after 3 seconds
            setTimeout(() => {
                successMessage.classList.remove('show');
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 500); // Wait for the slide-out animation to finish
            }, 3000); // Keep the message visible for 3 seconds
        } else {
            alert(data.message); // Show error message
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

document.getElementById('add-event-form').addEventListener('submit', handleEventFormSubmission);

// Toggle Sidebar function
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}
