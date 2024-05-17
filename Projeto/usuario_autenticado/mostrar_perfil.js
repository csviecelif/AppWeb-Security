document.addEventListener('DOMContentLoaded', function() {
    const profileDetails = document.getElementById('profile-details');

    // Carregar dados do perfil
    fetch('carregar_perfil.php', {
        method: 'GET'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na rede: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            profileDetails.innerHTML = `
                <p><strong>Biografia:</strong> ${data.bio || ''}</p>
                ${data.foto ? `<p><strong>Foto:</strong><br><img src="${data.foto}" alt="Foto de perfil"></p>` : ''}
                ${data.cv ? `<p><strong>CV:</strong> <a href="${data.cv}" target="_blank">Baixar CV</a></p>` : ''}
                ${data.certificados ? `<p><strong>Certificados:</strong><br>${data.certificados.split(',').map(cert => `<a href="${cert}" target="_blank">Ver Certificado</a>`).join('<br>')}</p>` : ''}
            `;
        } else {
            alert('Erro ao carregar perfil: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao carregar perfil');
    });
});
