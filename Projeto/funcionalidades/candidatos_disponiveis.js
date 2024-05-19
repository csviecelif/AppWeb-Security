document.addEventListener('DOMContentLoaded', function() {
    fetch("../cadastro/verificarsessao.php", {
        method: "GET",
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === false) {
            alert('Você deve estar logado para acessar esta página');
            location.href = "../login/index.html";
        }
    })
    .catch(error => console.error('Erro ao verificar sessão:', error));
    
    fetch('buscar_candidatos.php')
        .then(response => response.json())
        .then(data => {
            const candidatosDiv = document.getElementById('candidatos');
            data.forEach(candidato => {
                const candidatoDiv = document.createElement('div');
                candidatoDiv.className = 'candidato';
                candidatoDiv.innerHTML = `
                    <h3>${candidato.email}</h3>
                    <p>Telefone: ${candidato.telefone}</p>
                    <p>Bio: ${candidato.bio}</p>
                `;
                candidatosDiv.appendChild(candidatoDiv);
            });
        })
        .catch(error => console.error('Erro ao buscar candidatos:', error));
});
