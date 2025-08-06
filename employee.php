<?php
// Database connection
$host = 'localhost';
$dbname = 'RailwayManagement';
$username = 'root';
$password = '12345678';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch employee data
$employees = [];
try {
    $stmt = $pdo->query("SELECT EmployeeID AS id, Name AS name, Designation AS designation, Department AS department, Contact AS contact, Salary AS salary FROM Employee");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching employees: " . $e->getMessage());
}

// Fetch designations and departments
$designations = [];
$departments = [];
try {
    $designations = $pdo->query("SELECT id, designation FROM post")->fetchAll(PDO::FETCH_ASSOC);
    $departments = $pdo->query("SELECT dept_id, department FROM department")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching designations or departments: " . $e->getMessage());
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Employee WHERE EmployeeID = :id");
        $stmt->execute(['id' => $deleteId]);
        header("Location: employee.php");
        exit;
    } catch (PDOException $e) {
        die("Error deleting employee: " . $e->getMessage());
    }
}

// Handle update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $updateId = $_POST['update_id'];
    $name = $_POST['name'];
    $designation = $_POST['designation'];
    $department = $_POST['department'];
    $contact = $_POST['contact'];
    $salary = $_POST['salary'];

    try {
        $stmt = $pdo->prepare("UPDATE Employee SET Name = :name, Designation = :designation, Department = :department, Contact = :contact, Salary = :salary WHERE EmployeeID = :id");
        $stmt->execute([
            'id' => $updateId,
            'name' => $name,
            'designation' => $designation,
            'department' => $department,
            'contact' => $contact,
            'salary' => $salary
        ]);
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Handle add employee request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $name = $_POST['name'];
    $designation = $_POST['designation'];
    $department = $_POST['department'];
    $contact = $_POST['contact'];
    $salary = $_POST['salary'];

    try {
        $stmt = $pdo->prepare("INSERT INTO Employee (Name, Designation, Department, Contact, Salary) VALUES (:name, :designation, :department, :contact, :salary)");
        $stmt->execute([
            'name' => $name,
            'designation' => $designation,
            'department' => $department,
            'contact' => $contact,
            'salary' => $salary
        ]);
        header("Location: employee.php");
        exit;
    } catch (PDOException $e) {
        die("Error adding employee: " . $e->getMessage());
    }
}

// Ensure the fetched employee data is available before rendering the table
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css"> <!-- Added CSS file -->
</head>
<body>
<main>
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
    <div class="container">
        <header>
            <h1>Employee Management</h1>
        </header>
        <h2>Add New Employee</h2>
        <form method="POST" action="employee.php">
            <label for="employee_id">Employee ID:</label>
            <input type="text" id="employee_id" name="employee_id" required>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="designation">Designation:</label>
            <select id="designation" name="designation" required>
                <option value="">Select Designation</option>
                <?php foreach ($designations as $designation): ?>
                    <option value="<?= htmlspecialchars($designation['designation']) ?>"><?= htmlspecialchars($designation['designation']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="department">Department:</label>
            <select id="department" name="department" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= htmlspecialchars($department['department']) ?>"><?= htmlspecialchars($department['department']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="contact">Contact:</label>
            <input type="text" id="contact" name="contact" required>
            <label for="salary">Salary:</label>
            <input type="number" id="salary" name="salary" required>
            <button type="submit" name="add_employee">Add Employee</button>
        </form>

        <h2>Employee List</h2>
        <table>
            <thead>
            <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Designation</th>
                <th>Department</th>
                <th>Contact</th>
                <th>Salary</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($employees)): ?>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td><?= htmlspecialchars($employee['id']) ?></td>
                        <td><?= htmlspecialchars($employee['name']) ?></td>
                        <td><?= htmlspecialchars($employee['designation']) ?></td>
                        <td><?= htmlspecialchars($employee['department']) ?></td>
                        <td><?= htmlspecialchars($employee['contact']) ?></td>
                        <td><?= htmlspecialchars($employee['salary']) ?></td>
                        <td>
                            <button class="edit-button" onclick="editEmployee(this)">Edit</button>
                            <button class="delete-button" onclick="deleteEmployee(this)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No employees found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="editModal" style="display:none;">
    <form method="POST" action="employee.php">
        <input type="hidden" id="edit_id" name="update_id">
        <label for="edit_name">Name:</label>
        <input type="text" id="edit_name" name="name" required>
        <label for="edit_designation">Designation:</label>
        <input type="text" id="edit_designation" name="designation" required>
        <label for="edit_department">Department:</label>
        <input type="text" id="edit_department" name="department" required>
        <label for="edit_contact">Contact:</label>
        <input type="text" id="edit_contact" name="contact" required>
        <label for="edit_salary">Salary:</label>
        <input type="number" id="edit_salary" name="salary" required>
        <button type="submit">Update Employee</button>
        <button type="button" onclick="closeEditModal()">Cancel</button>
    </form>
</div>

<style>
    .btn {
        padding: 3px 8px;
        border: none; /* Removed border for outer box */
        border-radius: px;
        cursor: pointer;
        font-size: 12px;
    }
    .btn-edit {
        background-color: #4CAF50;
        color: white;
    }
    .btn-edit:hover {
        background-color: #45a049;
    }
    .btn-delete {
        background-color: #f44336;
        color: white;
    }
    .btn-delete:hover {
        background-color: #da190b;
    }
</style>

<script>
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function editEmployee(button) {
    const row = button.closest("tr");
    const isEditing = button.textContent === "Save";

    if (isEditing) {
        const id = row.querySelector("td:nth-child(1)").textContent.trim();
        const name = row.querySelector("td:nth-child(2)").textContent.trim();
        const designation = row.querySelector("td:nth-child(3)").querySelector("select").value.trim();
        const department = row.querySelector("td:nth-child(4)").querySelector("select").value.trim();
        const contact = row.querySelector("td:nth-child(5)").textContent.trim();
        const salary = row.querySelector("td:nth-child(6)").textContent.trim();

        const formData = new FormData();
        formData.append("update_id", id);
        formData.append("name", name);
        formData.append("designation", designation);
        formData.append("department", department);
        formData.append("contact", contact);
        formData.append("salary", salary);

        fetch("employee.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Save success");
                location.reload();
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(error => {
            alert("An error occurred: " + error.message);
        });
    } else {
        const designationOptions = <?= json_encode($designations) ?>;
        const departmentOptions = <?= json_encode($departments) ?>;

        row.querySelectorAll("td:not(:last-child)").forEach((cell, index) => {
            if (index === 2) { // Designation column
                const currentValue = cell.textContent.trim();
                const select = document.createElement("select");
                designationOptions.forEach(option => {
                    const opt = document.createElement("option");
                    opt.value = option.designation;
                    opt.textContent = option.designation;
                    if (option.designation === currentValue) opt.selected = true;
                    select.appendChild(opt);
                });
                cell.textContent = "";
                cell.appendChild(select);
            } else if (index === 3) { // Department column
                const currentValue = cell.textContent.trim();
                const select = document.createElement("select");
                departmentOptions.forEach(option => {
                    const opt = document.createElement("option");
                    opt.value = option.department;
                    opt.textContent = option.department;
                    if (option.department === currentValue) opt.selected = true;
                    select.appendChild(opt);
                });
                cell.textContent = "";
                cell.appendChild(select);
            } else {
                cell.setAttribute("contenteditable", "true");
            }
        });
        button.textContent = "Save";
    }
}

function deleteEmployee(button) {
    const row = button.closest("tr");
    const id = row.querySelector("td:nth-child(1)").textContent.trim();

    if (confirm("Are you sure you want to delete this employee?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.style.display = "none";

        const deleteIdInput = document.createElement("input");
        deleteIdInput.name = "delete_id";
        deleteIdInput.value = id;
        form.appendChild(deleteIdInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</body>
</html>
