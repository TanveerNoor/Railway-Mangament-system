<?php
$conn = new mysqli("localhost", "root", "12345678", "RailwayManagement");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete request
if (isset($_GET["delete_id"])) {
    $deleteId = intval($_GET["delete_id"]);
    if (!$conn->query("DELETE FROM Passenger WHERE PassengerID = $deleteId")) {
        die("Error deleting record: " . $conn->error);
    }
    header("Location: user.php");
    exit;
}

// Handle edit request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_id"])) {
    $editId = intval($_POST["edit_id"]);
    $name = $conn->real_escape_string($_POST["name"]);
    $age = intval($_POST["age"]);
    $gender = $conn->real_escape_string($_POST["gender"]); // Ensure gender is captured
    $contact = $conn->real_escape_string($_POST["contact"]); // Ensure contact is captured

    $updateQuery = "UPDATE Passenger SET Name='$name', Age=$age, Gender='$gender', Contact='$contact' WHERE PassengerID=$editId";
    if (!$conn->query($updateQuery)) {
        die("Error updating record: " . $conn->error);
    }
    header("Location: user.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Railway Management System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .edit-button, .save-button, .delete-button {
            cursor: pointer;
            font-size: 14px;
            width: 80px; /* Ensure consistent button width */
            text-align: center;
            color: black; 
        }

        .edit-button:hover, .save-button:hover, .delete-button:hover {
            
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <main>
        <div class="sidebar">
            <a href="user.php">Manage User</a>
            <a href="train.php">Add Train</a>
            <a href="destination.php">Add Destination</a>
            <a href="posting.php">Add Designation</a>
            <a href="department.php">Add Department</a> <!-- Corrected file name -->
            <a href="index.php">Info</a>
            <a href="employee.php">Employee Management</a>
            <a href="book.php">Booking Details</a>
        </div>
        <div class="container">
            <header>
                <h1>Railway Management System - Manage Users</h1>
            </header>
            <h2>Registered Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>Passenger ID</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Contact</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM Passenger");
                    if (!$result) {
                        die("Error fetching data: " . $conn->error);
                    }
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['PassengerID']}</td>
                            <td contenteditable='false' data-name='name'>{$row['Name']}</td>
                            <td contenteditable='false' data-name='age'>{$row['Age']}</td>
                            <td contenteditable='false' data-name='gender'>{$row['Gender']}</td>
                            <td contenteditable='false' data-name='contact'>{$row['Contact']}</td>
                            <td>{$row['RegistrationDate']}</td>
                            <td>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='edit_id' value='{$row['PassengerID']}'>
                                    <input type='hidden' name='name' value='{$row['Name']}'>
                                    <input type='hidden' name='age' value='{$row['Age']}'>
                                    <input type='hidden' name='gender' value='{$row['Gender']}'>
                                    <input type='hidden' name='contact' value='{$row['Contact']}'>
                                    <button type='button' class='edit-button' onclick='editUser(this)'>Edit</button>
                                    <button type='button' class='save-button' style='display:none;' onclick='saveUser(this)'>Save</button>
                                </form>
                                <a href='?delete_id={$row['PassengerID']}' class='delete-button'>Delete</a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.querySelector('.sidebar-toggle');
            sidebar.classList.toggle('hidden');
            toggleButton.classList.toggle('hidden');
        }

        function editUser(button) {
            const row = button.closest("tr");
            const saveButton = row.querySelector(".save-button");
            const editButton = row.querySelector(".edit-button");

            if (editButton.textContent === "Edit") {
                row.querySelectorAll("td[contenteditable]").forEach(cell => {
                    cell.setAttribute("contenteditable", "true");
                    cell.style.backgroundColor = "#f9f9f9";
                });
                saveButton.style.display = "inline-block";
                editButton.textContent = "Cancel";
            } else {
                row.querySelectorAll("td[contenteditable]").forEach(cell => {
                    cell.setAttribute("contenteditable", "false");
                    cell.style.backgroundColor = "";
                });
                saveButton.style.display = "none";
                editButton.textContent = "Edit";
            }
        }

        function saveUser(button) {
            const row = button.closest("tr");
            const form = row.querySelector("form");
            const cells = row.querySelectorAll("td[contenteditable]");

            cells.forEach(cell => {
                const inputName = cell.dataset.name;
                const hiddenInput = form.querySelector(`input[name="${inputName}"]`);
                if (hiddenInput) {
                    hiddenInput.value = cell.textContent.trim(); // Ensure gender and contact are updated
                }
            });

            form.submit();
        }
    </script>
    <?php
    // Close the database connection
    $conn->close();
    ?>
</body>
</html>