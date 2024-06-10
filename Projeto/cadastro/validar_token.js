document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const email = urlParams.get('email');

    if (email) {
        document.getElementById('emailDisplay').textContent = email;
        document.getElementById('emailInput').value = email;
    }

    const form = document.getElementById('validarTokenForm');
    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        const token = document.getElementById('token').value;
        const email = document.getElementById('emailInput').value;

        const data = { email, token };

        const response = await fetch('validar_token.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            alert('Token validado com sucesso!');
            // Redirecione para a página de autenticação de dois fatores
            window.location.href = '2fa.html';
        } else {
            alert('Erro ao validar token: ' + result.error);
        }
    });
});
