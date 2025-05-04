<?php
// Database credentials
$servername = "localhost";
$username = "root";  // Default username for WAMP
$password = "";  // Default password for WAMP (empty by default)
$dbname = "sa_talent_hub";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $lastname = mysqli_real_escape_string($conn, trim($_POST['lastname']));
    $number = mysqli_real_escape_string($conn, trim($_POST['number']));
    $age = mysqli_real_escape_string($conn, trim($_POST['age']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $talent = mysqli_real_escape_string($conn, trim($_POST['talent']));

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format!'); window.location.href='signup.html';</script>";
        exit();
    }

    // Validate phone number
    if (!preg_match('/^[0-9]{10}$/', $number)) {
        echo "<script>alert('Invalid phone number! Please enter a 10-digit number.'); window.location.href='signup.html';</script>";
        exit();
    }

    // Check if the email already exists in the database
    $checkEmail = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        // Email already exists, show error message as an alert
        echo "
        <script>
            alert('This account already exists! Please login.');
            window.location.href = 'login.html';
        </script>";
    } else {
        // Password hashing for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // SQL to insert data into the table
        $sql = "INSERT INTO users (first_name, last_name, age, email, password_hash, talent_type, number) 
                VALUES ('$name', '$lastname', '$age', '$email', '$hashed_password', '$talent', '$number')";

        if ($conn->query($sql) === TRUE) {
            // Redirect to success page with animation and then redirect to login
            echo "
            <html>
            <head>
                <title>Success</title>
                <style>
                    .success-message {
                        text-align: center;
                        padding: 50px;
                        background-color: #4CAF50;
                        color: white;
                        font-size: 20px;
                        border-radius: 10px;
                        animation: fadeOut 3s forwards;
                    }

                    @keyframes fadeOut {
                        0% { opacity: 1; }
                        70% { opacity: 1; }
                        100% { opacity: 0; }
                    }
                </style>
            </head>
            <body>
                <div class='success-message'>
                    Account created successfully! You will be redirected to the login page shortly.
                </div>
                <script>
                    setTimeout(function(){
                        window.location.href = 'login.html';
                    }, 3000); // Redirect after 3 seconds
                </script>
            </body>
            </html>
            ";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Close connection
    $conn->close();
}
?>
