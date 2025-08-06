document.addEventListener('DOMContentLoaded', function() {
    const loginRegisterBox = document.querySelector('.login-register-box');
    loginRegisterBox.addEventListener('click', function() {
        alert('Welcome to the Railway Management System!');
    });

    const bookingOption = document.querySelector('.option-box a[href="bookings.html"]');
    const modal = document.getElementById('booking-modal');
    const closeModal = document.querySelector('.modal .close');

    bookingOption.addEventListener('click', function(event) {
        event.preventDefault();
        modal.style.display = 'block';
    });

    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Add options for AC, Non-AC, and Cargo
    const acOption = document.createElement('div');
    acOption.textContent = 'AC';
    modal.appendChild(acOption);

    const nonAcOption = document.createElement('div');
    nonAcOption.textContent = 'Non-AC';
    modal.appendChild(nonAcOption);

    const cargoOption = document.createElement('div');
    cargoOption.textContent = 'Cargo';
    modal.appendChild(cargoOption);
});
