<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "12345678"; // Removed dummy value, replace with actual password
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
        echo json_encode(["error" => "No destinations found. Please verify that the DESTINATION table is correctly populated with valid data."]);
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
            max-width: 1000px; /* Expanded width */
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
            grid-template-columns: repeat(8, 1fr); /* Adjusted for 8 columns */
            gap: 20px;
        }
        thead, tbody, tr {
            display: contents;
        }
        th, td {
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
</head>
<body>
    <header>
        <div class="logo-container">
            <a href="homepage.php">
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
    <main>
        <section>
            <div class="transparent-box">
                <h2>Search Trains</h2>
                <form id="train-search-form" class="form-grid">
                    <div>
                        <label for="from-destination">From Destination:</label>
                        <select id="from-destination" name="from-destination" required>
                            <!-- Options will be dynamically populated -->
                        </select>
                    </div>
                    <div>
                        <label for="to-destination">To Destination:</label>
                        <select id="to-destination" name="to-destination" required>
                            <!-- Options will be dynamically populated -->
                        </select>
                    </div>
                    <div>
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" required>
                    </div>
                    <div style="grid-column: span 2; text-align: center;">
                        <button type="submit">Search</button>
                    </div>
                </form>
            </div>
        </section>
        <section id="train-list">
        <div class="transparent-box">
            <h2>Available Trains</h2>
                <table id="train-results">
                    <thead>
                        <tr>
                            <th>Train Name</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Action</th> <!-- Action column -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Train results will be dynamically inserted here -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script>
        function fetchDestinations(retries = 3) {
            fetch('booking.php?fetch_destinations=true')
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch destinations');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    const fromSelect = document.getElementById('from-destination');
                    const toSelect = document.getElementById('to-destination');
                    data.forEach(destination => {
                        const option = document.createElement('option');
                        option.value = destination;
                        option.textContent = destination;
                        fromSelect.appendChild(option.cloneNode(true));
                        toSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching destinations:', error);
                    if (retries > 0) {
                        fetchDestinations(retries - 1);
                    } else {
                        alert('Unable to load destinations. Check your database.');
                    }
                });
        }

        fetchDestinations();

        document.getElementById('train-search-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const from = document.getElementById('from-destination').value;
            const to = document.getElementById('to-destination').value;
            const date = document.getElementById('date').value;

            fetch(`booking.php?fetch_trains=true&from=${from}&to=${to}&date=${date}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    const trainResults = document.querySelector('#train-results tbody');
                    trainResults.innerHTML = '';

                    if (data.error) {
                        trainResults.innerHTML = `<tr><td colspan="7" style="text-align: center; color: red;">${data.error}</td></tr>`;
                        return;
                    }

                    if (data.length === 0) {
                        trainResults.innerHTML = `<tr><td colspan="7" style="text-align: center; color: gray;">No trains found for the selected criteria.</td></tr>`;
                        return;
                    }

                    data.forEach(train => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${train.train_name}</td>
                            <td>${train.from_station}</td>
                            <td>${train.to_station}</td>
                            <td>${train.departure_time}</td>
                            <td>${train.arrival_time}</td>
                            <td>${train.status}</td>
                            <td>${train.price}</td>
                            <td><button onclick="bookTrain('${train.TrainNumber}')">Book</button></td> <!-- Action button -->
                        `;
                        trainResults.appendChild(row);
                    });
                })
                .catch(error => {
                    const trainResults = document.querySelector('#train-results tbody');
                    trainResults.innerHTML = `<tr><td colspan="7" style="text-align: center; color: red;">An error occurred while fetching train data.</td></tr>`;
                    console.error('Fetch error:', error);
                });
        });
    </script>
    <script>
        function bookTrain(trainNumber) {
            window.location.href = `process.php?trainNumber=${trainNumber}`;
        }
    </script>
</body>
</html>
