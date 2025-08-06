<?php
session_start(); // Start the session

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) === 'main.php') {
    header("Location: login.php");
    exit;
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "12345678";
$dbname = "RailwayManagement";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Fetch destinations from the database
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch_destinations'])) {
    $sql = "SELECT placeName FROM DESTINATION"; // Corrected table and column name
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $destinations = [];
        while ($row = $result->fetch_assoc()) {
            $destinations[] = $row['placeName'];
        }
        echo json_encode($destinations);
    } else {
        echo json_encode(["error" => "No destinations available."]);
    }
    $conn->close();
    exit;
}

// Fetch train schedule from the database
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch_trains'])) {
    $from = isset($_GET['from']) ? $conn->real_escape_string(trim($_GET['from'])) : '';
    $to = isset($_GET['to']) ? $conn->real_escape_string(trim($_GET['to'])) : '';
    $date = isset($_GET['date']) ? $conn->real_escape_string(trim($_GET['date'])) : '';

    // Log input parameters for debugging
    error_log("Input Parameters - From: $from, To: $to, Date: $date");

    // Convert date format if necessary
    if (!empty($date)) {
        $date = date('Y/m/d', strtotime($date)); // Convert to yyyy/mm/dd format
        error_log("Converted Date: $date"); // Log converted date
    }

    if (empty($from) && empty($to) && empty($date)) {
        $sql = "SELECT TrainNumber, FromStation, ToStation, DepartureTime, ArrivalTime, Status, Price, train_name
                FROM TrainSchedule";
    } else if (empty($from) || empty($to) || empty($date)) {
        echo json_encode(["error" => "Invalid input. Provide 'from', 'to', and 'date' or leave all fields empty to fetch all trains."]);
        $conn->close();
        exit;
    } else {
        $sql = "SELECT TrainNumber, FromStation AS from_station, ToStation AS to_station, 
                       DepartureTime AS departure_time, ArrivalTime AS arrival_time, 
                       Status AS status, Price AS price, train_name
                FROM TrainSchedule
                WHERE FromStation = '$from' AND ToStation = '$to' AND `Date` = '$date'";
    }

    // Log the SQL query for debugging
    error_log("SQL Query: $sql");

    $result = $conn->query($sql);

    if ($result === false) {
        error_log("SQL Error: " . $conn->error);
        echo json_encode(["error" => "Database query failed. Please check your input and try again."]);
        $conn->close();
        exit;
    }

    if ($result->num_rows > 0) {
        $trains = [];
        while ($row = $result->fetch_assoc()) {
            $trains[] = $row;
        }
        echo json_encode($trains);
    } else {
        // Improved error message for no results
        echo json_encode(["error" => "No trains found for the selected criteria. Please verify your input."]);
    }
    $conn->close();
    exit;
}

// Handle train booking process
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['book_train']) && isset($_GET['trainNumber'])) {
    $trainNumber = $conn->real_escape_string(trim($_GET['trainNumber']));

    // Fetch train details for confirmation
    $sql = "SELECT TrainNumber, train_name, FromStation, ToStation, DepartureTime, ArrivalTime, Price 
            FROM TrainSchedule WHERE TrainNumber = '$trainNumber'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $trainDetails = $result->fetch_assoc();
        echo json_encode(["success" => true, "trainDetails" => $trainDetails]);
    } else {
        echo json_encode(["error" => "Train not found. Please try again."]);
    }
    $conn->close();
    exit;
}

// Handle booking confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $trainNumber = $conn->real_escape_string(trim($_POST['trainNumber']));
    $passengerName = $conn->real_escape_string(trim($_POST['passengerName']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $seatType = $conn->real_escape_string(trim($_POST['seatType']));
    $seatCount = intval($_POST['seatCount']);

    // Debugging: Log input values
    error_log("Booking Input - TrainNumber: $trainNumber, PassengerName: $passengerName, Email: $email, Phone: $phone, SeatType: $seatType, SeatCount: $seatCount");

    // Fetch PassengerID or create a new passenger record
    $passengerQuery = "SELECT PassengerID FROM Passenger WHERE Name = '$passengerName' AND Contact = '$phone'";
    $passengerResult = $conn->query($passengerQuery);

    if ($passengerResult && $passengerResult->num_rows > 0) {
        $passenger = $passengerResult->fetch_assoc();
        $passengerID = $passenger['PassengerID'];
    } else {
        $insertPassenger = "INSERT INTO Passenger (Name, Age, Gender, Contact) VALUES ('$passengerName', 0, 'Unknown', '$phone')";
        if ($conn->query($insertPassenger) === TRUE) {
            $passengerID = $conn->insert_id;
        } else {
            error_log("Error inserting passenger: " . $conn->error);
            echo json_encode(["error" => "Failed to create passenger record."]);
            $conn->close();
            exit;
        }
    }

    // Insert booking details into the Booking table
    $insertBooking = "INSERT INTO Booking (PassengerID, TrainNumber, ClassType, BookingDate, booked_seat)
                      VALUES ($passengerID, '$trainNumber', '$seatType', NOW(), $seatCount)";
    if ($conn->query($insertBooking) === TRUE) {
        // Update seat count in TrainSchedule
        $seatColumn = $seatType === 'AC' ? 'ACSeats' : 'NonACSeats';
        $updateSeats = "UPDATE TrainSchedule SET $seatColumn = $seatColumn - $seatCount WHERE TrainNumber = '$trainNumber'";
        if ($conn->query($updateSeats) === TRUE) {
            echo json_encode(["success" => true, "message" => "Booking confirmed successfully."]);
        } else {
            error_log("Error updating seat count: " . $conn->error);
            echo json_encode(["error" => "Booking confirmed, but failed to update seat count."]);
        }
    } else {
        error_log("Error inserting booking: " . $conn->error);
        echo json_encode(["error" => "Booking failed. Please try again."]);
    }
    $conn->close();
    exit;
}

// Check seat availability
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_seat_availability']) && isset($_GET['trainNumber'])) {
    $trainNumber = $conn->real_escape_string(trim($_GET['trainNumber']));

    // Fetch seat availability details
    $sql = "SELECT 'AC' AS SeatType, ACSeats AS RemainingSeats 
            FROM TrainSchedule 
            WHERE TrainNumber = '$trainNumber'
            UNION ALL
            SELECT 'Non-AC' AS SeatType, NonACSeats AS RemainingSeats 
            FROM TrainSchedule 
            WHERE TrainNumber = '$trainNumber'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $seats = [];
        while ($row = $result->fetch_assoc()) {
            $seats[] = $row;
        }
        echo json_encode(["success" => true, "seats" => $seats]);
    } else {
        echo json_encode(["error" => "No seat information available for this train."]);
    }
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway Management System</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .transparent-box {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 1000px;
            /* Expanded width */
            margin: 30px auto;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .form-grid label {
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            /* Adjusted for 8 columns */
            gap: 20px;
        }

        thead,
        tbody,
        tr {
            display: contents;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        th {
            font-weight: bold;
            background-color: #f1f1f1;
        }

        button {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        async function fetchSeatAvailability(trainNumber) {
            try {
                const response = await fetch(`process.php?check_seat_availability=1&trainNumber=${trainNumber}`);
                const data = await response.json();
                const seatInfoContainer = document.getElementById('realTimeSeats');
                seatInfoContainer.innerHTML = ''; // Clear previous data

                if (data.success) {
                    data.seats.forEach(seat => {
                        const seatRow = document.createElement('div');
                        seatRow.textContent = `Type: ${seat.SeatType}, Remaining Seats: ${seat.RemainingSeats}`;
                        seatInfoContainer.appendChild(seatRow);
                    });
                } else {
                    seatInfoContainer.textContent = data.error || 'No seat information available.';
                }
            } catch (error) {
                console.error('Error fetching seat availability:', error);
                const seatInfoContainer = document.getElementById('realTimeSeats');
                seatInfoContainer.textContent = 'Error fetching seat availability. Please try again.';
            }
        }

        async function updateSeatCount(trainNumber, seatType, seatCount) {
            try {
                const response = await fetch(`process.php?update_seat_count=1&trainNumber=${trainNumber}&seatType=${seatType}&seatCount=${seatCount}`, {
                    method: 'POST'
                });
                const data = await response.json();
                if (data.success) {
                    alert('Booking successful! Seat count updated.');
                    fetchSeatAvailability(trainNumber); // Refresh seat availability
                } else {
                    alert(data.error);
                }
            } catch (error) {
                console.error('Error updating seat count:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const trainNumber = document.querySelector('input[name="trainNumber"]').value;
            if (trainNumber) {
                fetchSeatAvailability(trainNumber);
            }

            const bookingForm = document.querySelector('form');
            bookingForm.addEventListener('submit', (event) => {
                event.preventDefault();
                const seatType = document.getElementById('seatType').value;
                const seatCount = document.getElementById('seatCount').value;
                updateSeatCount(trainNumber, seatType, seatCount);
            });
        });
    </script>
</head>

<body>
    <header>
        <div class="logo-container">
            <a href="main.php">
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
                <li><a href="login.php" class="login-register-box">Login/Register</a></li>
            </ul>
        </nav>
    </header>
    <div class="transparent-box">
        <h2>Book Your Train</h2>
        <form method="POST" action="process.php" class="form-grid">
            <input type="hidden" name="trainNumber" value="<?php echo isset($_GET['trainNumber']) ? htmlspecialchars($_GET['trainNumber']) : ''; ?>">

            <label for="seatType">Seat Type:</label>
            <select id="seatType" name="seatType" required>
                <option value="AC">AC</option>
                <option value="Non-AC">Non-AC</option>
            </select>

            <label for="realTimeSeats">Real-Time Seat Availability:</label>
            <div id="realTimeSeats" style="grid-column: span 2; margin-bottom: 20px;">
                <!-- Real-time seat availability will be dynamically loaded here -->
            </div>

            <label for="passengerName">Passenger Name:</label>
            <input type="text" id="passengerName" name="passengerName" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required>

            <label for="seatCount">Number of Seats:</label>
            <input type="number" id="seatCount" name="seatCount" min="1" required>

            <button type="submit" name="confirm_booking">Book</button>
        </form>
    </div>
</body>

</html>