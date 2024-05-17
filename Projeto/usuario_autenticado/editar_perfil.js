document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('edit-profile-form');

    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(form);

            fetch('editar_perfil.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na rede: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Perfil atualizado com sucesso!');
                    window.location.href = 'mostrar_perfil.html';
                } else {
                    alert('Erro ao atualizar perfil: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar perfil');
            });
        });

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
                document.getElementById('bio').value = data.bio || '';
                // Podemos mostrar a foto, CV e certificados se necessário
                if (data.foto) {
                    const img = document.createElement('img');
                    img.src = data.foto;
                    img.alt = 'Foto de perfil';
                    form.appendChild(img);
                }
            } else {
                alert('Erro ao carregar perfil: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar perfil');
        });
    }
});
