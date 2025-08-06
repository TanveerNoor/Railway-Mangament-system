<?php
// Database connection
$conn = new mysqli("localhost", "root", "12345678", "RailwayManagement");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle adding a new destination
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["placeName"]) && isset($_POST["location"])) {
    $placeName = trim($_POST["placeName"]);
    $location = trim($_POST["location"]);

    if (!empty($placeName) && !empty($location)) {
        $stmt = $conn->prepare("INSERT INTO DESTINATION (placeName, location) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $placeName, $location);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: destination.php"); // Redirect to avoid form resubmission
                exit;
            } else {
                $error = ($conn->errno === 1062) ? "Place Name already exists." : $stmt->error;
            }
            $stmt->close();
        } else {
            $error = $conn->error;
        }
    } else {
        $error = "Place Name and Location cannot be empty.";
    }
}

// Handle editing a destination
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editPlaceName"])) {
    $originalPlaceName = trim($_POST["originalPlaceName"]);
    $editPlaceName = trim($_POST["editPlaceName"]);
    $editLocation = trim($_POST["editLocation"]);

    if (!empty($editPlaceName) && !empty($editLocation)) {
        $stmt = $conn->prepare("UPDATE DESTINATION SET placeName = ?, location = ? WHERE placeName = ?");
        if ($stmt) {
            $stmt->bind_param("sss", $editPlaceName, $editLocation, $originalPlaceName);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: destination.php"); // Redirect to avoid form resubmission
                exit;
            } else {
                $error = $stmt->error;
            }
        } else {
            $error = $conn->error;
        }
    } else {
        $editError = "Place Name and Location cannot be empty.";
    }
}

// Handle deleting a destination
if (isset($_GET["delete_id"])) {
    $deleteId = trim($_GET["delete_id"]);
    if (!empty($deleteId)) {
        $stmt = $conn->prepare("DELETE FROM DESTINATION WHERE placeName = ?");
        if ($stmt) {
            $stmt->bind_param("s", $deleteId);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: destination.php"); // Redirect to avoid form resubmission
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Destination - Railway Management System</title>
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
        <h1>Railway Management System - Add Destination</h1>
    </header>
    <div class="container">
        <h2>Add Destination</h2>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($editError)): ?>
            <p style="color: red;"><?php echo $editError; ?></p>
        <?php endif; ?>
        <form id="add-destination-form" method="POST">
            <label for="placeName">Place Name:</label>
            <input type="text" id="placeName" name="placeName" required>
            
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required>
            
            <button type="submit">Add Destination</button>
        </form>
        <h2>Destination List</h2>
        <table>
            <thead>
                <tr>
                    <th>Place Name</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM DESTINATION");
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['placeName']}</td>
                            <td>{$row['location']}</td>
                            <td>
                                <button class='edit-button' onclick='editDestination(this)'>Edit</button>
                                <button class='delete-button' onclick='deleteDestination(this)'>Delete</button>
                            </td>
                        </tr>";
                    }
                    $result->free();
                } else {
                    echo "<tr><td colspan='3'>No destinations found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
        function editDestination(button) {
            const row = button.closest("tr");
            const isEditing = button.textContent === "Save";

            if (isEditing) {
                const placeName = row.querySelector("td:nth-child(1)").textContent.trim();
                const location = row.querySelector("td:nth-child(2)").textContent.trim();

                const form = document.createElement("form");
                form.method = "POST";
                form.style.display = "none";

                const originalPlaceNameInput = document.createElement("input");
                originalPlaceNameInput.name = "originalPlaceName";
                originalPlaceNameInput.value = button.dataset.originalPlaceName;
                form.appendChild(originalPlaceNameInput);

                const editPlaceNameInput = document.createElement("input");
                editPlaceNameInput.name = "editPlaceName";
                editPlaceNameInput.value = placeName;
                form.appendChild(editPlaceNameInput);

                const editLocationInput = document.createElement("input");
                editLocationInput.name = "editLocation";
                editLocationInput.value = location;
                form.appendChild(editLocationInput);

                document.body.appendChild(form);
                form.submit();
            } else {
                button.dataset.originalPlaceName = row.querySelector("td:nth-child(1)").textContent.trim();
                row.querySelectorAll("td:not(:last-child)").forEach(cell => {
                    cell.setAttribute("contenteditable", "true");
                });
                button.textContent = "Save";
            }
        }

        function deleteDestination(button) {
            const row = button.closest("tr");
            const placeName = row.querySelector("td:nth-child(1)").textContent.trim();

            if (confirm("Are you sure you want to delete this destination?")) {
                const form = document.createElement("form");
                form.method = "GET";
                form.style.display = "none";

                const deleteIdInput = document.createElement("input");
                deleteIdInput.name = "delete_id";
                deleteIdInput.value = placeName;
                form.appendChild(deleteIdInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>