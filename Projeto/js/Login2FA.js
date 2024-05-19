document.getElementById('loginForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const formData = new FormData(this);

    fetch('autenticar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'autenticar.html';
        } else {
            alert(data.error);
        }
    })
    .catch(error => console.error('Erro:', error));
});
