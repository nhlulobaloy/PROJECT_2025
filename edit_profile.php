<?php
session_start();
include('db_connection.php'); // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if (isset($_POST['bio']) || isset($_FILES['profile_picture'])) {
    $bio = $_POST['bio'] ?? '';

    // Handle the profile picture upload if provided
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        // Read the file's binary content
        $profile_picture = file_get_contents($_FILES['profile_picture']['tmp_name']);
    } else {
        $profile_picture = $user['profile_picture']; // Keep the existing profile picture if no new one is uploaded
    }

    // Update user profile in the database
    $sql = "UPDATE users SET bio = ?, profile_picture = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $bio, $profile_picture, $user_id);

    if ($stmt->execute()) {
        echo "<div class='success-message show'>Profile updated successfully!</div>";
    } else {
        echo "<div class='error-message show'>Error updating profile: " . $stmt->error . "</div>";
    }
}

// Handle event submission
if (isset($_POST['event_name']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = $_POST['event_name'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_location = $_POST['event_location'] ?? '';
    $event_description = $_POST['event_description'] ?? '';

    // Validate input for event submission
    if (empty($event_name) || empty($event_date) || empty($event_location) || empty($event_description)) {
        echo "<div class='error-message show'>Please fill in all fields.</div>";
        exit();
    }

    // Insert event into the database
    $sql = "INSERT INTO events (user_id, title, event_date, location, description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $user_id, $event_name, $event_date, $event_location, $event_description);

    if ($stmt->execute()) {
        echo "<div class='success-message show'>Event added successfully!</div>";
        // Redirect to the same page after success
        header("Refresh: 2; url=edit_profile.php");
    } else {
        echo "<div class='error-message show'>Error adding event: " . $stmt->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="edit_profile.css">
</head>
<body>
    <div class="edit-profile-container">
        <h2>Edit Profile</h2>
        
        <!-- Back to Dashboard Button -->
        <a href="dashboard.php" class="back-to-dashboard-btn">Back to Dashboard</a>

        <!-- Profile Edit Form -->
        <form method="post" enctype="multipart/form-data" class="edit-profile-form">
            <label>Bio:</label>
            <textarea name="bio" rows="4" cols="50" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            
            <label>Profile Picture:</label>
            <input type="file" name="profile_picture" accept="image/*">
            
            <button type="submit" name="submit_profile" id="submit">Save Changes</button>
        </form>

        <!-- Add Event Form -->
        <h2>Add Event</h2>
        <form method="post" class="add-event-form">
            <label>Event Name:</label>
            <input type="text" name="event_name" required placeholder="Enter event name">
            
            <label>Date:</label>
            <input type="date" name="event_date" required>
            
            <label>Location:</label>
            <input type="text" name="event_location" required placeholder="Enter event location">
            
            <label>Description:</label>
            <textarea name="event_description" rows="4" cols="50" placeholder="Enter event description"></textarea>
            
            <button type="submit" class="add-event-btn">Add Event</button>
        </form>

        <!-- Success or Error Message Display -->
        <?php 
        if (isset($_SESSION['event_success'])) {
            echo "<div class='success-message show'>" . $_SESSION['event_success'] . "</div>";
            unset($_SESSION['event_success']);
        }
        if (isset($_SESSION['error_msg'])) {
            echo "<div class='error-message show'>" . $_SESSION['error_msg'] . "</div>";
            unset($_SESSION['error_msg']);
        }
        ?>
    </div>

    <script src="edit_profile.js"></script>
</body>
</html>