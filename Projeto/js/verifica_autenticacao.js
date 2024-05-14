// verifica_autenticacao.js
window.onload = function() {
    fetch('../cadastro/verificarsessao.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na solicitação. Código de status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data !== 'True') {
            alert('Você deve estar logado para acessar esta página.');
            window.location.href = '../login/index.html';
        }
    })
    .catch(error => console.error('Erro durante a verificação de autenticação:', error));
};
