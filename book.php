<?php
// Database connection
$host = 'localhost';
$dbname = 'RailwayManagement';
$username = 'root';
$password = '12345678';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT b.BookingID, p.Name AS passenger_name, p.Contact AS passenger_contact, 
                                t.train_name, b.ClassType, b.BookingDate, 
                                b.booked_seat
                         FROM Booking b
                         JOIN Passenger p ON b.PassengerID = p.PassengerID
                         JOIN trains t ON b.TrainNumber = t.train_id
                         ORDER BY b.BookingDate DESC");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action'])) {
        if ($input['action'] === 'delete' && isset($input['BookingID'])) {
            $stmt = $pdo->prepare("DELETE FROM Booking WHERE BookingID = :BookingID");
            $stmt->execute([':BookingID' => $input['BookingID']]);
            echo json_encode(['status' => 'success', 'message' => 'Booking deleted successfully']);
            exit;
        } elseif ($input['action'] === 'edit' && isset($input['BookingID'], $input['ClassType'])) {
            $stmt = $pdo->prepare("UPDATE Booking SET ClassType = :ClassType WHERE BookingID = :BookingID");
            $stmt->execute([':ClassType' => $input['ClassType'], ':BookingID' => $input['BookingID']]);
            echo json_encode(['status' => 'success', 'message' => 'Booking updated successfully']);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Railway Management System</title>
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
        <h2>Booking Details</h2>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Passenger Name</th>
                    <th>Contact</th>
                    <th>Train Name</th>
                    <th>Class Type</th>
                    <th>Booking Date</th>
                    <th>Booked Seat</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['BookingID']) ?></td>
                        <td><?= htmlspecialchars($booking['passenger_name']) ?></td>
                        <td><?= htmlspecialchars($booking['passenger_contact']) ?></td>
                        <td><?= htmlspecialchars($booking['train_name']) ?></td>
                        <td><?= htmlspecialchars($booking['ClassType']) ?></td>
                        <td><?= htmlspecialchars($booking['BookingDate']) ?></td>
                        <td><?= htmlspecialchars($booking['booked_seat']) ?></td>
                        <td>
                            <button onclick="editBooking(this)">Edit</button>
                            <button onclick="deleteBooking(this)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function editBooking(button) {
            const row = button.closest('tr');
            const classCell = row.cells[4];
            const actionsCell = row.cells[7];
            const original = classCell.textContent;
            const id = row.cells[0].textContent;

            classCell.innerHTML = `<input type="text" value="${original}" />`;
            actionsCell.innerHTML = `
                <button onclick="saveBooking(this, '${id}')">Save</button>
                <button onclick="cancelEdit(this, '${original}')">Cancel</button>
            `;
        }

        function saveBooking(button, bookingID) {
            const row = button.closest('tr');
            const newValue = row.cells[4].querySelector('input').value;

            fetch('book.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'edit', BookingID: bookingID, ClassType: newValue})
            }).then(res => res.json())
              .then(data => {
                  if (data.status === 'success') {
                      row.cells[4].textContent = newValue;
                      row.cells[7].innerHTML = `
                          <button onclick="editBooking(this)">Edit</button>
                          <button onclick="deleteBooking(this)">Delete</button>
                      `;
                  } else {
                      alert("Update failed");
                  }
              });
        }

        function cancelEdit(button, originalValue) {
            const row = button.closest('tr');
            row.cells[4].textContent = originalValue;
            row.cells[7].innerHTML = `
                <button onclick="editBooking(this)">Edit</button>
                <button onclick="deleteBooking(this)">Delete</button>
            `;
        }

        function deleteBooking(button) {
            const row = button.closest('tr');
            const bookingID = row.cells[0].textContent;

            if (confirm("Are you sure you want to delete this booking?")) {
                fetch('book.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'delete', BookingID: bookingID})
                }).then(res => res.json())
                  .then(data => {
                      if (data.status === 'success') {
                          row.remove();
                      } else {
                          alert("Deletion failed");
                      }
                  });
            }
        }
    </script>
</body>
</html>
