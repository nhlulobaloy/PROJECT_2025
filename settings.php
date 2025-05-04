<?php
session_start();
include('db_connection.php'); // Include database connection logic

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$message = ""; // Feedback message

// Handle First and Last Name Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_name'])) {
    $new_first_name = $_POST['new_first_name'];
    $new_last_name = $_POST['new_last_name'];

    // Update the first name and last name in the database
    $sql_update_name = "UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?";
    $stmt_update_name = $conn->prepare($sql_update_name);
    $stmt_update_name->bind_param("ssi", $new_first_name, $new_last_name, $user_id);
    if ($stmt_update_name->execute()) {
        $message = "<div class='success'>Name updated successfully!</div>";
    } else {
        $message = "<div class='error'>Error updating name.</div>";
    }
}

// Handle Email Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_email'])) {
    $new_email = $_POST['new_email'];

    // Check if the new email is different from the current email
    if ($new_email !== $user['email']) {
        // Update the email in the database
        $sql_update_email = "UPDATE users SET email = ? WHERE user_id = ?";
        $stmt_update_email = $conn->prepare($sql_update_email);
        $stmt_update_email->bind_param("si", $new_email, $user_id);
        if ($stmt_update_email->execute()) {
            $message = "<div class='success'>Email updated successfully!</div>";
        } else {
            $message = "<div class='error'>Error updating email.</div>";
        }
    } else {
        $message = "<div class='error'>New email cannot be the same as the current email.</div>";
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $new_password_confirm = $_POST['new_password_confirm'];

    if (password_verify($current_password, $user['password_hash'])) {
        if ($new_password === $new_password_confirm) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $sql_update = "UPDATE users SET password_hash = ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $hashed_password, $user_id);
            if ($stmt_update->execute()) {
                $message = "<div class='success'>Password updated successfully!</div>";
            } else {
                $message = "<div class='error'>Error updating password.</div>";
            }
        } else {
            $message = "<div class='error'>Passwords do not match.</div>";
        }
    } else {
        $message = "<div class='error'>Current password is incorrect.</div>";
    }
}

// Handle Username Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_username'])) {
    $new_username = $_POST['new_username'];
    $sql_update_username = "UPDATE users SET username = ? WHERE user_id = ?";
    $stmt_update_username = $conn->prepare($sql_update_username);
    $stmt_update_username->bind_param("si", $new_username, $user_id);
    if ($stmt_update_username->execute()) {
        $message = "<div class='success'>Username updated successfully!</div>";
    } else {
        $message = "<div class='error'>Error updating username.</div>";
    }
}

// Handle Notification Preferences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notifications'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;

    $sql_update_notifications = "UPDATE users SET email_notifications = ?, sms_notifications = ? WHERE user_id = ?";
    $stmt_update_notifications = $conn->prepare($sql_update_notifications);
    $stmt_update_notifications->bind_param("iii", $email_notifications, $sms_notifications, $user_id);
    if ($stmt_update_notifications->execute()) {
        $message = "<div class='success'>Notification preferences updated successfully!</div>";
    } else {
        $message = "<div class='error'>Error updating notification preferences.</div>";
    }
}

// Handle Two-Factor Authentication (2FA)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_2fa'])) {
    $enable_2fa = isset($_POST['enable_2fa']) ? 1 : 0;

    $sql_update_2fa = "UPDATE users SET two_factor_enabled = ? WHERE user_id = ?";
    $stmt_update_2fa = $conn->prepare($sql_update_2fa);
    $stmt_update_2fa->bind_param("ii", $enable_2fa, $user_id);
    if ($stmt_update_2fa->execute()) {
        $message = "<div class='success'>Two-Factor Authentication updated successfully!</div>";
    } else {
        $message = "<div class='error'>Error updating Two-Factor Authentication.</div>";
    }
}

// Handle Language Preference
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_language'])) {
    $language = $_POST['language'];
    $sql_update_language = "UPDATE users SET language = ? WHERE user_id = ?";
    $stmt_update_language = $conn->prepare($sql_update_language);
    $stmt_update_language->bind_param("si", $language, $user_id);
    if ($stmt_update_language->execute()) {
        $message = "<div class='success'>Language preference updated successfully!</div>";
    } else {
        $message = "<div class='error'>Error updating language preference.</div>";
    }
}

// Handle Privacy Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_privacy'])) {
    $profile_visibility = $_POST['profile_visibility'];
    $sql_update_privacy = "UPDATE users SET profile_visibility = ? WHERE user_id = ?";
    $stmt_update_privacy = $conn->prepare($sql_update_privacy);
    $stmt_update_privacy->bind_param("si", $profile_visibility, $user_id);
    if ($stmt_update_privacy->execute()) {
        $message = "<div class='success'>Privacy settings updated successfully!</div>";
    } else {
        $message = "<div class='error'>Error updating privacy settings.</div>";
    }
}

// Handle Account Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $sql_delete_account = "DELETE FROM users WHERE user_id = ?";
    $stmt_delete_account = $conn->prepare($sql_delete_account);
    $stmt_delete_account->bind_param("i", $user_id);
    if ($stmt_delete_account->execute()) {
        session_destroy();
        header("Location: goodbye.php");
        exit();
    } else {
        $message = "<div class='error'>Error deleting account.</div>";
    }
}

// Fetch existing talent from 'users' table
$sql_fetch_talent = "SELECT talent_type FROM users WHERE user_id = ?";
$stmt_fetch_talent = $conn->prepare($sql_fetch_talent);
$stmt_fetch_talent->bind_param("i", $user_id);
$stmt_fetch_talent->execute();
$result_fetch_talent = $stmt_fetch_talent->get_result();
$user_talent = $result_fetch_talent->fetch_assoc()['talent_type'] ?? ''; // Safely fetch talent
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link rel="stylesheet" href="posts.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="submit_comment.css">
    <link rel="stylesheet" href="settings.css">
    <script src="like_comment.js" defer></script>
    <script src="discover.js" defer></script>
</head>
<body>
    <div class="container">
        <!-- Include Sidebar -->
        <?php include('sidebar.html'); ?>
        <div class="main-content">
        <header id="navbar">
        <button class="hamburger-menu" onclick="toggleMenu()">☰</button>
        </header>
        </div>

        <!-- Main Settings Section -->
        <div class="settings-container">
            <h2>⚙️ Account Settings</h2>
            <?php echo $message; ?>

            <!-- Change First and Last Name -->
            <section class="change-name">
                <h3>👤 Change Name</h3>
                <form method="post">
                    <label for="new_first_name">New First Name:</label>
                    <input type="text" name="new_first_name" required placeholder="Enter new first name">
                    
                    <label for="new_last_name">New Last Name:</label>
                    <input type="text" name="new_last_name" required placeholder="Enter new last name">
                    
                    <button type="submit" id="update-name-btn" name="change_name">Update Name</button>
                </form>
            </section>

            <!-- Change Email -->
            <section class="change-email">
                <h3>📧 Change Email</h3>
                <form method="post">
                    <label for="new_email">New Email:</label>
                    <input type="email" name="new_email" required placeholder="Enter new email">
                    <button type="submit" id="update-email-btn" name="change_email">Update Email</button>
                </form>
            </section>

            <!-- Change Password -->
            <section class="change-password">
                <h3>🔑 Change Password</h3>
                <form method="post">
                    <label for="current_password">Current Password:</label>
                    <input type="password" name="current_password" required placeholder="Enter current password">

                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" required placeholder="Enter new password">

                    <label for="new_password_confirm">Confirm New Password:</label>
                    <input type="password" name="new_password_confirm" required placeholder="Confirm new password">

                    <button type="submit" id="update-password-btn" name="change_password">Update Password</button>
                </form>
            </section>

            <!-- Profile Privacy Settings -->
            <section class="profile-privacy">
                <h3>🔒 Privacy Settings</h3>
                <form method="post">
                    <label for="profile_visibility">Profile Visibility:</label>
                    <select name="profile_visibility" required>
                        <option value="public" <?php echo $user['profile_visibility'] == 'public' ? 'selected' : ''; ?>>Public</option>
                        <option value="private" <?php echo $user['profile_visibility'] == 'private' ? 'selected' : ''; ?>>Private</option>
                    </select>
                    <button type="submit" name="update_privacy">Update Privacy</button>
                </form>
            </section>

            <!-- Delete Account Section -->
            <section class="delete-account">
                <h3>❌ Delete Account</h3>
                <form method="post">
                    <button type="submit" name="delete_account">Delete My Account</button>
                </form>
            </section>

        </div>
    </div>
</body>
</html>
