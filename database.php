<?php
$host = "localhost";
$user = "root"; // Default user in WAMP/XAMPP
$pass = ""; // No password by default
$dbname = "sa_talent_hub";

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Create Users Table
$sql = "CREATE TABLE IF NOT EXISTS users (
    user_id VARCHAR(6) PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    age INT NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    talent_type ENUM('Artist', 'Instrumentalist', 'Dancer', 'Musician', 'DJ', 'Producer', 'Other') NOT NULL,
    number VARCHAR(15) NOT NULL,
    profile_picture BLOB,  
    bio VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully.<br>";
} else {
    die("Error creating users table: " . $conn->error);
}

// Create Trigger to Generate Random 6-Digit ID for user_id
$sql = "DROP TRIGGER IF EXISTS generate_user_id;"; 
$conn->query($sql);

$sql = "
CREATE TRIGGER generate_user_id
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.user_id IS NULL THEN
        SET NEW.user_id = LPAD(FLOOR(RAND() * 1000000), 6, '0');
    END IF;
END;";

if ($conn->query($sql) === TRUE) {
    echo "Trigger created successfully.<br>";
} else {
    die("Error creating trigger: " . $conn->error);
}

// Create Gigs Table
$sql = "CREATE TABLE IF NOT EXISTS gigs (
    gig_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(100),
    date DATE NOT NULL,
    payment DECIMAL(10,2),
    posted_by VARCHAR(6), 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(user_id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Gigs table created successfully.<br>";
} else {
    die("Error creating gigs table: " . $conn->error);
}

// Create Feed Table
$sql = "CREATE TABLE IF NOT EXISTS feed (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(6) NOT NULL,
    content TEXT NOT NULL,
    media LONGBLOB,  
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Feed table created successfully.<br>";
} else {
    die("Error creating feed table: " . $conn->error);
}

// Create Likes Table
$sql = "CREATE TABLE IF NOT EXISTS likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(6) NOT NULL, 
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES feed(post_id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Likes table created successfully.<br>";
} else {
    die("Error creating likes table: " . $conn->error);
}

// Create Comments Table
$sql = "CREATE TABLE IF NOT EXISTS comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(6) NOT NULL, 
    post_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES feed(post_id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Comments table created successfully.<br>";
} else {
    die("Error creating comments table: " . $conn->error);
}

// Create Contact Messages Table
$sql = "CREATE TABLE IF NOT EXISTS contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Contact messages table created successfully.<br>";
} else {
    die("Error creating contact messages table: " . $conn->error);
}

// Create Login Activity Table
$sql = "CREATE TABLE IF NOT EXISTS login_activity (
    login_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(6),
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Login activity table created successfully.<br>";
} else {
    die("Error creating login activity table: " . $conn->error);
}

// Create Follows Table
$sql = "CREATE TABLE IF NOT EXISTS follows (
    follow_id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id VARCHAR(6) NOT NULL,
    following_id VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id)
)";
if ($conn->query($sql) === TRUE) {
    echo "Follows table created successfully.<br>";
} else {
    die("Error creating follows table: " . $conn->error);
}

// Create Followers Table (Added)
$sql = "CREATE TABLE IF NOT EXISTS followers (
    follow_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(6) NOT NULL,
    follower_id VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (follower_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Followers table created successfully.<br>";
} else {
    die("Error creating followers table: " . $conn->error);
}

// Create Events Table
$sql = "CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(6) NOT NULL, 
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Events table created successfully.<br>";
} else {
    die("Error creating events table: " . $conn->error);
}



// Placeholder Music Table
$sql = "CREATE TABLE IF NOT EXISTS music (
    music_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(6) NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Music table created successfully.<br>";
} else {
    die("Error creating music table: " . $conn->error);
}

echo "All tables and triggers created successfully.";


// Create Preferences Table
$sql = "CREATE TABLE IF NOT EXISTS preferences (
    user_id VARCHAR(6) PRIMARY KEY,
    email_notifications BOOLEAN DEFAULT 1,
    sms_notifications BOOLEAN DEFAULT 1,
    two_factor_enabled BOOLEAN DEFAULT 0,
    language VARCHAR(10) DEFAULT 'en',
    profile_visibility ENUM('Public', 'Private') DEFAULT 'Public',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Preferences table created successfully.<br>";
} else {
    die("Error creating preferences table: " . $conn->error);
}


$sql = "CREATE TABLE replies (
    reply_id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    reply_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comment_id) REFERENCES comments(comment_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";


if ($conn->query($sql) === TRUE) {
    echo "replies table created successfully.<br>";
} else {
    die("Error creating preferences table: " . $conn->error);
}



$conn->close();
?>
