<?php
session_start(); // Start session for user management
$servername = "localhost";
$username = "root";
$password = "12345678";
$dbname = "RailwayManagement";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($action === 'login') {
        // Check for specific admin credentials
        $adminEmail = "admin@gmail.com";
        $adminPassword = "123";

        if ($email === $adminEmail && $password === $adminPassword) {
            $_SESSION['user_id'] = "admin"; // Store admin identifier in session
            $_SESSION['user_email'] = $adminEmail; // Store admin email
            header("Location: index.php"); // Corrected redirection to admin index.php
            exit();
        }

        // Check for other users in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id']; // Store user ID in session
                $_SESSION['user_email'] = $user['email']; // Optional: Store user email
                header("Location: main.php"); // Redirect to main.php for regular users
                exit();
            } else {
                header("Location: login.php?error=Invalid email or password"); // Redirect with error
                exit();
            }
        } else {
            header("Location: login.php?error=Invalid email or password"); // Redirect with error
            exit();
        }
        $stmt->close();
    } elseif ($action === 'signup') {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashedPassword);
        if ($stmt->execute()) {
            header("Location: login.php?success=Signup successful"); // Redirect with success message
            exit();
        } else {
            header("Location: signup.php?error=Server error"); // Redirect with error
            exit();
        }
        $stmt->close();
    }
}

$conn->close();
?>
