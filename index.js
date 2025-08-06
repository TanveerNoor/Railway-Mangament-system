document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.querySelector("table tbody");

    // Fetch train schedule data from the server
    fetch('index.php?action=get_train_schedule')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateTable(data.trains, data.trainNames, data.stations);
                localStorage.setItem("trainNames", JSON.stringify(data.trainNames)); // Cache train names
            } else {
                alert('Failed to fetch train schedule.');
            }
        })
        .catch(error => console.error('Error fetching train schedule:', error));
});

function populateTable(trains, trainNames, stations) {
    const tableBody = document.querySelector("table tbody");
    tableBody.innerHTML = ""; // Clear existing rows

    trains.forEach(train => {
        const row = document.createElement("tr");

        const trainNameOptions = trainNames.map(
            tn => `<option value="${tn.train_id}" ${tn.train_name === train.name ? "selected" : ""}>${tn.train_name}</option>`
        ).join("");

        row.innerHTML = `
            <td>${train.number}</td>
            <td>${train.name}</td>
            <td>${train.from}</td>
            <td>${train.to}</td>
            <td>${train.departure}</td>
            <td>${train.arrival}</td>
            <td>${train.status}</td>
            <td>${train.ac_seats}</td>
            <td>${train.non_ac_seats}</td>
            <td>${train.contact}</td>
            <td>${train.price}</td>
            <td>${train.date}</td>
            <td>
                <button class="edit-button">Edit</button>
                <button class="delete-button">Delete</button>
            </td>
        `;

        tableBody.appendChild(row);
    });
}

document.querySelector("table tbody").addEventListener("click", event => {
    if (event.target.classList.contains("edit-button")) {
        editTrain(event.target);
    } else if (event.target.classList.contains("delete-button")) {
        deleteTrain(event.target);
    }
});

function editTrain(button) {
    const row = button.closest("tr");
    const isEditing = button.textContent === "Save";

    if (isEditing) {
        const trainData = {
            number: row.querySelector("td:nth-child(1)").textContent.trim(),
            name: row.querySelector("td:nth-child(2)").querySelector("select").value.trim(),
            from: row.querySelector("td:nth-child(3)").querySelector("input").value.trim(),
            to: row.querySelector("td:nth-child(4)").querySelector("input").value.trim(),
            departure: row.querySelector("td:nth-child(5)").querySelector("input").value.trim(),
            arrival: row.querySelector("td:nth-child(6)").querySelector("input").value.trim(),
            status: row.querySelector("td:nth-child(7)").querySelector("input").value.trim(),
            acSeats: row.querySelector("td:nth-child(8)").querySelector("input").value.trim(),
            nonAcSeats: row.querySelector("td:nth-child(9)").querySelector("input").value.trim(),
            contact: row.querySelector("td:nth-child(10)").querySelector("input").value.trim(),
            price: row.querySelector("td:nth-child(11)").querySelector("input").value.trim(),
            date: row.querySelector("td:nth-child(12)").querySelector("input").value.trim()
        };

        fetch("index.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" }, // Ensure correct Content-Type
            body: JSON.stringify({ edit_train: true, ...trainData })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert("Train details updated successfully.");
                location.reload();
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(error => {
            console.error("Error occurred:", error);
            alert("An error occurred: " + error.message);
        });
    } else {
        const trainNames = JSON.parse(localStorage.getItem("trainNames"));

        row.querySelectorAll("td:not(:last-child)").forEach((cell, index) => {
            if (index === 1) { // Train Name column
                const currentValue = cell.textContent.trim();
                const select = document.createElement("select");
                trainNames.forEach(option => {
                    const opt = document.createElement("option");
                    opt.value = option.train_id;
                    opt.textContent = option.train_name;
                    if (option.train_name === currentValue) opt.selected = true;
                    select.appendChild(opt);
                });
                cell.textContent = "";
                cell.appendChild(select);
            } else {
                const currentValue = cell.textContent.trim();
                const input = document.createElement("input");
                input.type = index === 11 ? "number" : "text"; // Price is a number
                input.value = currentValue;
                cell.textContent = "";
                cell.appendChild(input);
            }
        });

        button.textContent = "Save";
    }
}

function deleteTrain(button) {
    const row = button.closest("tr");
    const trainNumber = row.querySelector("td:nth-child(1)").textContent.trim();

    if (confirm("Are you sure you want to delete this train?")) {
        fetch("index.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" }, // Ensure correct Content-Type
            body: JSON.stringify({ delete_train: true, trainNumber })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert("Train deleted successfully.");
                row.remove();
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(error => {
            console.error("Error occurred:", error);
            alert("An error occurred: " + error.message);
        });
    }
}
