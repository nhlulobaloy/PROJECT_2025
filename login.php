<?php
session_start();
include('db_connection.php'); // Include the DB connection

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query the database to check if the email exists
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    // Verify the password
    if ($user && password_verify($password, $user['password_hash'])) {
        // Password is correct, set session and redirect to dashboard
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        header('Location: dashboard.php');
        exit();
    } else {
        // Invalid credentials, display error message and redirect to login page after 3 seconds
        echo "
        <style>
            /* Error Message Styling */
            .error-message {
                padding: 20px;
                background-color: #f44336; /* Red background */
                color: white;
                font-size: 18px;
                text-align: center;
                border-radius: 8px;
                width: 80%;
                margin: 20px auto;
                animation: shake 0.5s ease-in-out, fadeIn 0.5s ease-out;
            }

            /* Animation for shaking effect */
            @keyframes shake {
                0% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                50% { transform: translateX(10px); }
                75% { transform: translateX(-10px); }
                100% { transform: translateX(0); }
            }

            /* Fade-in effect */
            @keyframes fadeIn {
                0% { opacity: 0; }
                100% { opacity: 1; }
            }
        </style>
        <div class='error-message'>
            Invalid email or password. Please try again.
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'login.html'; // Redirect after 3 seconds
            }, 3000);
        </script>";
    }
}
?>
