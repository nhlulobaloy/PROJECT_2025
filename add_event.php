<?php
session_start();
include('db_connection.php'); // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle event submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = $_POST['event_name'] ?? '';
    $event_date = $_POST['event_date'] ?? '';

    // Validate input
    if (empty($event_name) || empty($event_date)) {
        echo "<div class='error-message show'>Please fill in all fields.</div>";
        exit();
    }

    // Insert event into the database
    $sql = "INSERT INTO events (user_id, title, event_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo "<div class='error-message show'>Error preparing the SQL statement: " . $conn->error . "</div>";
        exit();
    }

    $stmt->bind_param("iss", $user_id, $event_name, $event_date);

    if ($stmt->execute()) {
        echo "<div class='success-message show'>Event added successfully!</div>";
        // Clear form fields after success
        header("Refresh: 2; url=edit_profile.php"); // Redirect to the same page after 2 seconds
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
        <h2>Add Event</h2>
        <form method="post" class="add-event-form">
            <label>Event Name:</label>
            <input type="text" name="event_name" required placeholder="Enter event name">
            
            <label>Date:</label>
            <input type="date" name="event_date" required>
            
            <button type="submit" class="add-event-btn">Add Event</button>
        </form>
    </div>
</body>
</html>
