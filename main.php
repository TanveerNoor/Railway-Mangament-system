<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login to access this page");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway Management System</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <header>
        <div class="logo-container">
            <a href="homepage.html">
                <img src="trt.png" alt="Railway Management System Logo" class="logo">
            </a>
            <h1>Railway Management System</h1>
        </div>
        <nav>
            <ul>
                <li><a href="main.php"><img src="home-transparent-background-free-png.png" alt="home" class="head">Home</a></li>
                <li><a href="about.php"><img src="contact.png" alt="contact" class="head">Contact</a></li>
                <li><a href="faq.php"><img src="faq.png" alt="faq" class="head">FAQ</a></li>
                <li><a href="notification.php"><img src="notification.png" alt="notification" class="head">Notification</a></li>
                <li><a href="profile.php"><img src="contact-icon-png-6.png" alt="profile" class="head">Profile</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li>
                        <form method="POST" action="process.php" style="display: inline;">
                            <button type="submit" name="logout" class="login-register-box">Logout</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li><a href="login.php" class="login-register-box">Login/Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
        <section class="options">
            <div class="option-box">
                <a href="booking.php">
                    <img src="booking.png" alt="Bookings">
                    <p>Bookings</p>
                </a>
            </div>
            <div class="option-box">
                <a href="schedules.html">
                    <img src="schedules.png" alt="Schedules">
                    <p>Schedules</p>
                </a>
            </div>
            <div class="option-box">
                <a href="tickets.html">
                    <img src="tickets.webp" alt="Tickets">
                    <p>Tickets</p>
                </a>
            </div>
            <div class="option-box">
                <a href="tickets.html">
                    <img src="n.png" alt="News & Updates">
                    <p>News & Updates</p>
                </a>
            </div>
        </section>
        <div id="booking-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Select Booking Type</h2>
                <!-- Removed the booking options for AC, Non-AC, and Cargo -->
            </div>
        </div>
    </main>
    <script src="script.js"></script>
</body>
</html>
