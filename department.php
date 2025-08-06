<?php
require_once "db_connection.php"; // Include the database connection

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $departmentName = trim($_POST["departmentName"]);

    if (!empty($departmentName)) {
        $stmt = $conn->prepare("INSERT INTO department (department) VALUES (?)"); // Correct table and column name
        if ($stmt) {
            $stmt->bind_param("s", $departmentName);
            if ($stmt->execute()) {
                header("Location: department.php"); // Refresh the page
                exit;
            } else {
                $error = ($conn->errno === 1062) ? "Department already exists." : $stmt->error;
            }
            $stmt->close();
        } else {
            $error = $conn->error;
        }
    } else {
        $error = "Department name cannot be empty.";
    }
}

// Handle edit request
if (isset($_GET["edit_id"]) && isset($_GET["updated_name"])) {
    $editId = $_GET["edit_id"];
    $updatedName = trim($_GET["updated_name"]);

    if (!empty($updatedName)) {
        $stmt = $conn->prepare("UPDATE department SET department = ? WHERE dept_id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $updatedName, $editId);
            if ($stmt->execute()) {
                echo "success";
            } else {
                echo $stmt->error;
            }
            $stmt->close();
        } else {
            echo $conn->error;
        }
    } else {
        echo "Department name cannot be empty.";
    }
    exit;
}

// Handle delete request
if (isset($_GET["delete_id"])) {
    $deleteId = $_GET["delete_id"];
    $stmt = $conn->prepare("DELETE FROM department WHERE dept_id = ?"); // Correct column name
    if ($stmt) {
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo $stmt->error;
        }
        $stmt->close();
    } else {
        echo $conn->error;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Department - Railway Management System</title>
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
        <h1>Railway Management System - Add Department</h1>
    </header>
    <div class="container">
        <h2>Add Department</h2>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="departmentName">Department Name:</label>
            <input type="text" id="departmentName" name="departmentName" required>
            <button type="submit">Add Department</button>
        </form>
        <h2>Department List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT dept_id, department FROM department"); // Correct column names
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id = htmlspecialchars($row['dept_id']);
                        $name = htmlspecialchars($row['department']);
                        echo "<tr>
                            <td>{$id}</td>
                            <td>{$name}</td>
                            <td>
                                <button class='edit-button' onclick='editDepartment(this)'>Edit</button>
                                <button class='delete-button' onclick='deleteDepartment(this)'>Delete</button>
                            </td>
                        </tr>";
                    }
                    $result->free();
                } else {
                    echo "<tr><td colspan='3'>No departments found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
        function editDepartment(button) {
            const row = button.closest("tr");
            const id = row.querySelector("td:first-child").textContent.trim();
            const nameCell = row.querySelector("td:nth-child(2)");
            const isEditing = button.textContent === "Save";

            if (isEditing) {
                const updatedName = nameCell.textContent.trim();
                if (updatedName) {
                    fetch(`department.php?edit_id=${id}&updated_name=${encodeURIComponent(updatedName)}`)
                        .then(response => response.text())
                        .then(data => {
                            if (data === "success") {
                                nameCell.setAttribute("contenteditable", "false");
                                button.textContent = "Edit";
                            } else {
                                alert("Error updating department: " + data);
                            }
                        });
                } else {
                    alert("Department name cannot be empty.");
                }
            } else {
                nameCell.setAttribute("contenteditable", "true");
                nameCell.focus();
                button.textContent = "Save";
            }
        }

        function deleteDepartment(button) {
            const row = button.closest("tr");
            const id = row.querySelector("td:first-child").textContent.trim();

            if (confirm("Are you sure you want to delete this department?")) {
                fetch(`department.php?delete_id=${id}`)
                    .then(response => response.text())
                    .then(data => {
                        if (data === "success") {
                            row.remove();
                        } else {
                            alert("Error deleting department: " + data);
                        }
                    });
            }
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
