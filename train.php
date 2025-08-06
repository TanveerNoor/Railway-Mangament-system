<?php
$conn = new mysqli("localhost", "root", "12345678", "RailwayManagement");

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    if ($_POST["action"] === "update") {
        $trainId = $_POST["trainId"];
        $trainName = $_POST["trainName"];
        $acSeats = $_POST["acSeats"];
        $nonAcSeats = $_POST["nonAcSeats"];

        $stmt = $conn->prepare("UPDATE trains SET train_name = ?, ac_seats = ?, non_ac_seats = ? WHERE train_id = ?");
        if ($stmt) {
            $stmt->bind_param("siis", $trainName, $acSeats, $nonAcSeats, $trainId);
            if (!$stmt->execute()) {
                echo "Error updating data: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
        exit();
    }

    if ($_POST["action"] === "delete") {
        $trainId = $_POST["trainId"];

        $stmt = $conn->prepare("DELETE FROM trains WHERE train_id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $trainId);
            if (!$stmt->execute()) {
                echo "Error deleting data: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["action"])) {
    $trainId = $_POST["trainId"];
    $trainName = $_POST["trainName"];
    $acSeats = $_POST["acSeats"];
    $nonAcSeats = $_POST["nonAcSeats"];

    $stmt = $conn->prepare("INSERT INTO trains (train_id, train_name, ac_seats, non_ac_seats) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssii", $trainId, $trainName, $acSeats, $nonAcSeats);
        if (!$stmt->execute()) {
            echo "Error inserting data: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    // Redirect to the same page to prevent form resubmission
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit();
}

if (isset($_GET["delete_id"])) {
    $deleteId = $_GET["delete_id"];
    $stmt = $conn->prepare("DELETE FROM trains WHERE train_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $deleteId);
        if (!$stmt->execute()) {
            echo "Error deleting data: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    // Redirect to the same page to prevent form resubmission
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Train</title>
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
        <h1>Railway Management System - Add Train</h1>
    </header>
    <div class="container">
        <h2>Add Train</h2>
        <form id="addTrainForm" method="POST">
            <label for="trainId">Train ID:</label>
            <input type="text" id="trainId" name="trainId" required>
            
            <label for="trainName">Train Name:</label>
            <input type="text" id="trainName" name="trainName" required>
            
            <label for="acSeats">Available AC Seats:</label>
            <input type="number" id="acSeats" name="acSeats" required>
            
            <label for="nonAcSeats">Available Non-AC Seats:</label>
            <input type="number" id="nonAcSeats" name="nonAcSeats" required>
            
            <button type="submit">Add Train</button>
        </form>
        <h2>Train List</h2>
        <table>
            <thead>
                <tr>
                    <th>Train ID</th>
                    <th>Train Name</th>
                    <th>Available AC Seats</th>
                    <th>Available Non-AC Seats</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM trains");
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td data-column='train_id'>{$row['train_id']}</td>
                            <td data-column='train_name'>{$row['train_name']}</td>
                            <td data-column='ac_seats'>{$row['ac_seats']}</td>
                            <td data-column='non_ac_seats'>{$row['non_ac_seats']}</td>
                            <td>
                                <button class='edit-button' onclick='editTrain(this)'>Edit</button>
                                <button class='delete-button' onclick='deleteTrain(this)'>Delete</button>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Error fetching train data: " . $conn->error . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.querySelector('.sidebar-toggle');
            sidebar.classList.toggle('hidden');
            toggleButton.classList.toggle('hidden');
        }

        function editEmployee(button) {
            const row = button.closest("tr");
            const isEditing = button.textContent === "Save";

            if (isEditing) {
                // Save mode: Disable editing
                row.querySelectorAll(".edit-mode").forEach(element => {
                    element.classList.add("hidden");
                });
                row.querySelectorAll(".view-mode").forEach(element => {
                    element.classList.remove("hidden");
                    if (element.tagName === "SPAN") {
                        const select = element.nextElementSibling;
                        element.textContent = select.options[select.selectedIndex].text;
                    }
                });
                button.textContent = "Edit";
            } else {
                // Edit mode: Enable editing
                row.querySelectorAll(".edit-mode").forEach(element => {
                    element.classList.remove("hidden");
                });
                row.querySelectorAll(".view-mode").forEach(element => {
                    element.classList.add("hidden");
                });
                button.textContent = "Save";
            }
        }

        function deleteEmployee(button) {
            const row = button.closest("tr");
            if (confirm("Are you sure you want to delete this employee?")) {
                row.remove();
            }
        }

        function editTrain(button) {
            const row = button.closest("tr");
            const isEditing = button.textContent === "Save";

            if (isEditing) {
                // Save mode: Send updated data to the server
                const trainId = row.querySelector("td[data-column='train_id']").textContent;
                const trainName = row.querySelector("td[data-column='train_name']").textContent;
                const acSeats = row.querySelector("td[data-column='ac_seats']").textContent;
                const nonAcSeats = row.querySelector("td[data-column='non_ac_seats']").textContent;

                fetch("", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({
                        action: "update",
                        trainId,
                        trainName,
                        acSeats,
                        nonAcSeats
                    })
                }).then(response => response.text()).then(data => {
                    if (data) alert(data);
                });

                row.querySelectorAll("td[contenteditable]").forEach(cell => {
                    cell.setAttribute("contenteditable", "false");
                });
                button.textContent = "Edit";
            } else {
                // Edit mode: Enable editing
                row.querySelectorAll("td[data-column]").forEach(cell => {
                    cell.setAttribute("contenteditable", "true");
                });
                button.textContent = "Save";
            }
        }

        function deleteTrain(button) {
            const row = button.closest("tr");
            const trainId = row.querySelector("td[data-column='train_id']").textContent;

            if (confirm("Are you sure you want to delete this train?")) {
                fetch("", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({
                        action: "delete",
                        trainId
                    })
                }).then(response => response.text()).then(data => {
                    if (data) alert(data);
                    row.remove();
                });
            }
        }
    </script>
</body>
</html>