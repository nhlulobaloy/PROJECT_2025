<?php
include('db_connection.php');  // Include database connection

// Check if 'user_id' is provided
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Query to fetch the profile picture (binary data)
    $sql = "SELECT profile_picture FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists and has a profile picture
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $profile_picture = $user['profile_picture'];

        if ($profile_picture) {
            // Set header to display the image as JPEG (or adjust based on your image type)
            header("Content-Type: image/jpeg"); 
            echo $profile_picture;  // Output the binary image data directly
        } else {
            // Output a default image if no profile picture is found
            header("Content-Type: image/jpeg");
            readfile('uploads/default-profile.jpg');
        }
    } else {
        // If user doesn't exist, send a default image
        header("Content-Type: image/jpeg");
        readfile('uploads/default-profile.jpg');
    }
} else {
    // No user_id provided, send a default image
    header("Content-Type: image/jpeg");
    readfile('uploads/default-profile.jpg');
}
?>
