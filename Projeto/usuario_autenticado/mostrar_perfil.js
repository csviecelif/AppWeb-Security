document.addEventListener('DOMContentLoaded', function() {
    const profileDetails = document.getElementById('profile-details');
    const profilePhoto = document.getElementById('profile-photo');
    const editProfileButton = document.getElementById('edit-profile-button');
    const visitSiteButton = document.getElementById('visit-site-button');

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

    fetch('carregar_perfil.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na rede: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const userData = data.data;
                let profileHtml = '';
                if (userData.foto) {
                    profilePhoto.src = userData.foto;
                    profilePhoto.style.display = 'block';
                }

                profileHtml += `
                    <div class="profile-details-row"><p><strong>Nome Completo:</strong> ${userData.nomeCompleto || 'Não fornecido'}</p></div>
                    <div class="profile-details-row"><p><strong>Email:</strong> ${userData.email || 'Não fornecido'}</p></div>
                `;

                if (userData.table === 'buscar') {
                    profileHtml += `
                        <div class="profile-details-row"><p><strong>Telefone:</strong> ${userData.telefone || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Formação Acadêmica:</strong> ${userData.formacao_academica || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Idiomas Falados:</strong> ${userData.idiomas_falados || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Data de Nascimento:</strong> ${userData.data_nascimento || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Área de Interesse:</strong> ${userData.area_interesse || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Expectativa Salarial:</strong> ${userData.expectativa_salarial || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>País de Origem:</strong> ${userData.pais_origem || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Experiência Profissional:</strong> ${userData.experiencia_profissional || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Habilidades e Competências:</strong> ${userData.habilidades_competencias || 'Não fornecido'}</p></div>
                    `;
                } else if (userData.table === 'oferecer') {
                    profileHtml += `
                        <div class="profile-details-row"><p><strong>Nome da Empresa:</strong> ${userData.nome_empresa || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Cargo:</strong> ${userData.cargo || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>País da Empresa:</strong> ${userData.pais_empresa || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Setor:</strong> ${userData.setor || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Descrição da Vaga:</strong> ${userData.descricao_vaga || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Requisitos da Vaga:</strong> ${userData.requisitos_vaga || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Salário:</strong> ${userData.salario || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Benefícios:</strong> ${userData.beneficios || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Endereço da Empresa:</strong> ${userData.endereco_empresa || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Website da Empresa:</strong> ${userData.website_empresa || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Redes Sociais da Empresa:</strong> ${userData.redes_sociais_empresa || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Documento de Identidade:</strong> ${userData.documento_identidade || 'Não fornecido'}</p></div>
                    `;
                }

                profileDetails.innerHTML = profileHtml;
            } else {
                profileDetails.innerHTML = `<p>Erro ao carregar perfil: ${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            profileDetails.innerHTML = `<p>Erro ao carregar perfil</p>`;
        });

    editProfileButton.addEventListener('click', function() {
        window.location.href = 'editar_perfil.html';
    });

    visitSiteButton.addEventListener('click', function() {
        window.location.href = '../funcionalidades/index.html';
    });
});
