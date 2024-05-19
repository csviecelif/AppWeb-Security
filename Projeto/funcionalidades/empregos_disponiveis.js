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
    fetch('buscar_empregos.php')
        .then(response => response.json())
        .then(data => {
            const empregosDiv = document.getElementById('empregos');
            data.forEach(emprego => {
                const empregoDiv = document.createElement('div');
                empregoDiv.className = 'emprego';
                empregoDiv.innerHTML = `
                    <h3>${emprego.cargo}</h3>
                    <p>País: ${emprego.pais_empresa}</p>
                    <p>Setor: ${emprego.setor}</p>
                `;
                empregosDiv.appendChild(empregoDiv);
            });
        })
        .catch(error => console.error('Erro ao buscar empregos:', error));
});
