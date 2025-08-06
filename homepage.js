document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelector('.btn');
    btn.addEventListener('click', function() {
        fetch('check_login.php')
            .then(response => response.json())
            .then(data => {
                if (data.loggedIn) {
                    window.location.href = 'next_page.html';
                } else {
                    window.location.href = 'login.php';
                }
            })
            .catch(error => console.error('Error:', error));
    });
});
