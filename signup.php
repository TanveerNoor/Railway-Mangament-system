<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles1.css">
</head>
<body>
    <div class="background-image"></div>
    <div class="form-container">
        <img src="617fb5498bab4f0ee6675a4cda00f736.png" alt="w3docs" class="logo">
        <h2>Sign Up</h2>
        <form id="signupForm" action="server.php" method="POST">
            <input type="hidden" name="action" value="signup">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
    <script src="scripts.js"></script>
</body>
</html>
