<?php
// Database connection
$host = 'localhost';
$dbname = 'RailwayManagement';
$username = 'root';
$password = '12345678';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle JSON-based POST actions (edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        if (stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
            throw new Exception("Expected application/json");
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON: " . json_last_error_msg());
        }

        // Delete train
        if (isset($input['delete_train'])) {
            $stmt = $pdo->prepare("DELETE FROM TrainSchedule WHERE TrainNumber = ?");
            $stmt->execute([$input['trainNumber']]);
            echo json_encode(['success' => true]);
            exit;
        }

        // Edit train
        if (isset($input['edit_train'])) {
            $stmt = $pdo->prepare("UPDATE TrainSchedule ts
                                   JOIN trains t ON ts.TrainNumber = t.train_id
                                   SET ts.FromStation = ?, ts.ToStation = ?, ts.DepartureTime = ?, ts.ArrivalTime = ?, ts.Status = ?, ts.Contact = ?, ts.Price = ?, ts.Date = ?,
                                       t.train_name = ?, t.ac_seats = ?, t.non_ac_seats = ?
                                   WHERE ts.TrainNumber = ?");
            $stmt->execute([
                $input['from'], $input['to'], $input['departure'], $input['arrival'],
                $input['status'], $input['contact'], $input['price'], $input['date'],
                $input['train_name'], $input['acSeats'], $input['nonAcSeats'], $input['train_id']
            ]);
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// JSON fetch endpoint
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_train_schedule') {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->query("SELECT ts.TrainNumber AS train_id, t.train_name, ts.FromStation AS `from`, ts.ToStation AS `to`, ts.DepartureTime, ts.ArrivalTime, ts.Status, ts.Contact, t.ac_seats, t.non_ac_seats, ts.Price, ts.Date
                             FROM TrainSchedule ts
                             JOIN trains t ON ts.TrainNumber = t.train_id");
        $trains = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $trainNames = $pdo->query("SELECT train_id, train_name FROM trains")->fetchAll(PDO::FETCH_ASSOC);
        $stations = $pdo->query("SELECT placeName FROM DESTINATION")->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode(['success' => true, 'trains' => $trains, 'trainNames' => $trainNames, 'stations' => $stations]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Fetch train schedule for HTML display
$stmt = $pdo->query("SELECT ts.TrainNumber AS train_id, t.train_name, ts.FromStation AS `from`, ts.ToStation AS `to`, ts.DepartureTime, ts.ArrivalTime, ts.Status, ts.Contact, t.ac_seats, t.non_ac_seats, ts.Price, ts.Date
                     FROM TrainSchedule ts
                     JOIN trains t ON ts.TrainNumber = t.train_id");
$trains = $stmt->fetchAll(PDO::FETCH_ASSOC);
$trainNames = $pdo->query("SELECT train_id, train_name FROM trains")->fetchAll(PDO::FETCH_ASSOC);
$stations = $pdo->query("SELECT placeName FROM DESTINATION")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Homepage - Railway Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="sidebar">
    <a href="user.php">Manage User</a>
    <a href="train.php">Add Train</a>
    <a href="destination.php">Add Destination</a>
    <a href="posting.php">Add Designation</a>
    <a href="department.php">Add Department</a>
    <a href="index.php">Info</a>
    <a href="employee.php">Employee Management</a>
    <a href="book.php">Booking Details</a>
</div>
<header>
    <h1>Railway Management System - Admin Panel</h1>
</header>
<div class="container">
    <h2>Train Schedule</h2>
    <table>
        <thead>
            <tr>
                <th>Train Number</th>
                <th>Train Name</th>
                <th>From</th>
                <th>To</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Status</th>
                <th>AC Seats</th>
                <th>Non-AC Seats</th>
                <th>Contact</th>
                <th>Price</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($trains as $train): ?>
            <tr data-id="<?= $train['train_id'] ?>">
                <td><?= htmlspecialchars($train['train_id']) ?></td>
                <td><?= htmlspecialchars($train['train_name']) ?></td>
                <td><?= htmlspecialchars($train['from']) ?></td>
                <td><?= htmlspecialchars($train['to']) ?></td>
                <td><?= htmlspecialchars($train['DepartureTime']) ?></td>
                <td><?= htmlspecialchars($train['ArrivalTime']) ?></td>
                <td><?= htmlspecialchars($train['Status']) ?></td>
                <td><?= htmlspecialchars($train['ac_seats']) ?></td>
                <td><?= htmlspecialchars($train['non_ac_seats']) ?></td>
                <td><?= htmlspecialchars($train['Contact']) ?></td>
                <td><?= htmlspecialchars($train['Price']) ?></td>
                <td><?= htmlspecialchars($train['Date']) ?></td>
                <td>
                    <button onclick="editTrain(this)">Edit</button>
                    <button onclick="deleteTrain(this)">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
    const trainNames = <?= json_encode($trainNames) ?>;
    const stations = <?= json_encode($stations) ?>;

    function editTrain(button) {
        const row = button.closest("tr");
        const isEditing = button.textContent === "Save";

        if (isEditing) {
            const train_id = row.dataset.id;
            const name = row.querySelector("td:nth-child(2) select").value;
            const from = row.querySelector("td:nth-child(3) select").value;
            const to = row.querySelector("td:nth-child(4) select").value;
            const departure = row.querySelector("td:nth-child(5)").textContent.trim();
            const arrival = row.querySelector("td:nth-child(6)").textContent.trim();
            const status = row.querySelector("td:nth-child(7)").textContent.trim();
            const acSeats = row.querySelector("td:nth-child(8)").textContent.trim();
            const nonAcSeats = row.querySelector("td:nth-child(9)").textContent.trim();
            const contact = row.querySelector("td:nth-child(10)").textContent.trim();
            const price = row.querySelector("td:nth-child(11)").textContent.trim();
            const date = row.querySelector("td:nth-child(12)").textContent.trim();

            fetch("index.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    edit_train: true,
                    train_id,
                    train_name: name,
                    from,
                    to,
                    departure,
                    arrival,
                    status,
                    acSeats,
                    nonAcSeats,
                    contact,
                    price,
                    date
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert("Error: " + data.error);
            })
            .catch(err => alert("Error: " + err.message));
        } else {
            row.querySelectorAll("td").forEach((cell, idx) => {
                if (idx === 1) {
                    const current = cell.textContent.trim();
                    const select = document.createElement("select");
                    trainNames.forEach(opt => {
                        const option = document.createElement("option");
                        option.value = opt.train_name;
                        option.textContent = opt.train_name;
                        if (opt.train_name === current) option.selected = true;
                        select.appendChild(option);
                    });
                    cell.innerHTML = "";
                    cell.appendChild(select);
                } else if (idx === 2 || idx === 3) {
                    const current = cell.textContent.trim();
                    const select = document.createElement("select");
                    stations.forEach(place => {
                        const opt = document.createElement("option");
                        opt.value = place;
                        opt.textContent = place;
                        if (place === current) opt.selected = true;
                        select.appendChild(opt);
                    });
                    cell.innerHTML = "";
                    cell.appendChild(select);
                } else if (idx > 3 && idx < 12) {
                    cell.setAttribute("contenteditable", "true");
                }
            });
            button.textContent = "Save";
        }
    }

    function deleteTrain(button) {
        const row = button.closest("tr");
        const trainNumber = row.dataset.id;

        if (!confirm("Are you sure you want to delete this train?")) return;

        fetch("index.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                delete_train: true,
                trainNumber
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) row.remove();
            else alert("Error: " + data.error);
        })
        .catch(err => alert("Error: " + err.message));
    }
</script>
</body>
</html>
