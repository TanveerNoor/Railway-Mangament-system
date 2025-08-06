<?php
// Database connection
$conn = new mysqli("localhost", "root", "12345678", "RailwayManagement"); // Ensure the password matches your MySQL setup
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch designations from the database
$designations = [];
$result = $conn->query("SELECT DISTINCT id, designation FROM post WHERE designation IS NOT NULL");
if ($result && $result->num_rows > 0) { // Check if rows exist
    while ($row = $result->fetch_assoc()) {
        $designations[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $designation = $conn->real_escape_string($_POST['designation']);

    if (!empty($designation)) { // Allow at least one field to be filled
        $query = "INSERT INTO post (designation) VALUES (
            " . (!empty($designation) ? "'$designation'" : "NULL") . "
        )";

        if ($conn->query($query) === TRUE) {
            header("Location: posting.php"); // Refresh the page
            exit;
        } else {
            // Debugging output for SQL errors
            echo "<script>alert('SQL Error: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('At least one field is required.');</script>";
    }
}

// Handle edit request
if (isset($_POST['edit_id']) && isset($_POST['edit_designation'])) {
    $edit_id = $conn->real_escape_string($_POST['edit_id']);
    $edit_designation = $conn->real_escape_string($_POST['edit_designation']);

    $query = "UPDATE post SET designation = '$edit_designation' WHERE id = $edit_id";
    if ($conn->query($query) === TRUE) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
    exit;
}

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $conn->real_escape_string($_POST['delete_id']);

    $query = "DELETE FROM post WHERE id = $delete_id";
    if ($conn->query($query) === TRUE) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Posting - Railway Management System</title>
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
        <h1>Railway Management System - Add Designation</h1>
    </header>
    <div class="container">
        <h2>Add Posting</h2>
        <form id="add-posting-form" method="POST">
            <label for="designation">Designation:</label>
            <input type="text" id="designation" name="designation">
            
            <button type="submit">Add Posting</button>
        </form>
        <div style="display: flex; gap: 100px;">
            <div style="flex: 1;">
                <h2>Designation List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Designation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="designation-list">
                        <?php foreach ($designations as $designation): ?>
                        <tr>
                            <td><?= htmlspecialchars($designation['id']) ?></td>
                            <td><?= htmlspecialchars($designation['designation']) ?></td>
                            <td style="display: flex; gap: 5px;">
                                <button class="edit-button" onclick="editPosting(this)">Edit</button>
                                <button class="delete-button" onclick="deletePosting(this)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        const form = document.getElementById("add-posting-form");

        form.addEventListener("submit", (event) => {
            const designation = document.getElementById("designation").value.trim();

            if (!designation) { // Require at least one field to be filled
                event.preventDefault(); // Prevent form submission
                alert("At least one field is required.");
            }
        });

        function editPosting(button) {
            const row = button.closest("tr");
            const id = row.querySelector("td:first-child").textContent.trim();
            const designationCell = row.querySelector("td:nth-child(2)");
            const isEditing = button.textContent === "Save";

            if (isEditing) {
                const newDesignation = designationCell.textContent.trim();

                // Send the updated designation to the server
                fetch("posting.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `edit_id=${id}&edit_designation=${encodeURIComponent(newDesignation)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Designation updated successfully.");
                        designationCell.setAttribute("contenteditable", "false");
                        button.textContent = "Edit"; // Revert button text to "Edit"
                    } else {
                        console.error("Server error:", data.error); // Log server error
                        alert("Error updating designation: " + data.error);
                    }
                })
                .catch((error) => {
                    console.error("Fetch error:", error); // Log fetch error
                    alert("An error occurred while updating the designation.");
                });
            } else {
                // Enable editing mode
                designationCell.setAttribute("contenteditable", "true");
                designationCell.focus();
                button.textContent = "Save"; // Change button text to "Save"
            }
        }

        function deletePosting(button) {
            const row = button.closest("tr");
            const id = row.querySelector("td:first-child").textContent.trim();

            if (confirm("Are you sure you want to delete this posting?")) {
                fetch("posting.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `delete_id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Designation deleted successfully.");
                        row.remove();
                    } else {
                        alert("Error deleting designation: " + data.error);
                    }
                })
                .catch(() => {
                    alert("An error occurred while deleting the designation.");
                });
            }
        }
    </script>
</body>
</html>
