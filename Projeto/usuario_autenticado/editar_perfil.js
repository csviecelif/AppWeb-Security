document.addEventListener('DOMContentLoaded', function() {
    const profileDetails = document.getElementById('profile-details');
    const profilePhoto = document.getElementById('profile-photo');
    const saveProfileButton = document.getElementById('save-profile-button');
    let userData = null;

    fetch('carregar_perfil.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na rede: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                userData = data.data;
                let profileHtml = '';
                if (userData.foto) {
                    profilePhoto.src = userData.foto;
                    profilePhoto.style.display = 'block';
                }
                profileHtml += `
                    <div class="profile-details-row"><p><strong>Nome Completo:</strong> ${userData.nomeCompleto || 'Não fornecido'}</p></div>
                    <div class="profile-details-row"><p><strong>Email:</strong> <input type="text" id="email" value="${userData.email || ''}"></p></div>
                    <div class="profile-details-row"><p><strong>Telefone:</strong> <input type="text" id="telefone" value="${userData.telefone || ''}"></p></div>
                    <div class="profile-details-row"><p><strong>Biografia:</strong> <textarea id="bio">${userData.bio || ''}</textarea></p></div>
                `;

                if (userData.table === 'buscar') {
                    profileHtml += `
                        <div class="profile-details-row"><p><strong>Formação Acadêmica:</strong> <input type="text" id="formacao_academica" value="${userData.formacao_academica || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>Idiomas Falados:</strong> <input type="text" id="idiomas_falados" value="${userData.idiomas_falados || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>Data de Nascimento:</strong> <input type="date" id="data_nascimento" value="${userData.data_nascimento || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>Área de Interesse:</strong> <input type="text" id="area_interesse" value="${userData.area_interesse || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>Expectativa Salarial:</strong> <input type="text" id="expectativa_salarial" value="${userData.expectativa_salarial || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>País de Origem:</strong> <input type="text" id="pais_origem" value="${userData.pais_origem || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>Experiência Profissional:</strong> <textarea id="experiencia_profissional">${userData.experiencia_profissional || ''}</textarea></p></div>
                        <div class="profile-details-row"><p><strong>Habilidades e Competências:</strong> <textarea id="habilidades_competencias">${userData.habilidades_competencias || ''}</textarea></p></div>
                    `;
                } else if (userData.table === 'oferecer') {
                    profileHtml += `
                        <div class="profile-details-row"><p><strong>Nome da Empresa:</strong> ${userData.nome_empresa || 'Não fornecido'}</p></div>
                        <div class="profile-details-row"><p><strong>Cargo:</strong> <input type="text" id="cargo" value="${userData.cargo || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>País da Empresa:</strong> <input type="text" id="pais_empresa" value="${userData.pais_empresa || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>Setor:</strong> <input type="text" id="setor" value="${userData.setor || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>Descrição da Vaga:</strong> <textarea id="descricao_vaga">${userData.descricao_vaga || ''}</textarea></p></div>
                        <div class="profile-details-row"><p><strong>Requisitos da Vaga:</strong> <textarea id="requisitos_vaga">${userData.requisitos_vaga || ''}</textarea></p></div>
                        <div class="profile-details-row"><p><strong>Salário:</strong> <input type="text" id="salario" value="${userData.salario || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>Benefícios:</strong> <textarea id="beneficios">${userData.beneficios || ''}</textarea></p></div>
                        <div class="profile-details-row"><p><strong>Endereço da Empresa:</strong> <input type="text" id="endereco_empresa" value="${userData.endereco_empresa || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>Website da Empresa:</strong> <input type="text" id="website_empresa" value="${userData.website_empresa || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>Redes Sociais da Empresa:</strong> <input type="text" id="redes_sociais_empresa" value="${userData.redes_sociais_empresa || ''}"></p></div>
                        <div class="profile-details-row"><p><strong>Documento de Identidade:</strong> <input type="text" id="documento_identidade" value="${userData.documento_identidade || ''}"></p></div>
                    `;
                }

                profileHtml += `<div class="profile-details-row"><p><strong>Alterar Foto:</strong> <input type="file" id="foto"></p></div>`;

                profileDetails.innerHTML = profileHtml;
            } else {
                profileDetails.innerHTML = `<p>Erro ao carregar perfil: ${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            profileDetails.innerHTML = `<p>Erro ao carregar perfil</p>`;
        });

    saveProfileButton.addEventListener('click', function() {
        if (!userData) {
            alert('Dados do usuário não carregados');
            return;
        }
        
        const formData = new FormData();
        formData.append('table', userData.table);
        formData.append('userId', userData.userId);
        
        if (userData.table === 'buscar') {
            formData.append('email', document.getElementById('email').value);
            formData.append('telefone', document.getElementById('telefone').value);
            formData.append('bio', document.getElementById('bio').value);
            formData.append('formacao_academica', document.getElementById('formacao_academica').value);
            formData.append('idiomas_falados', document.getElementById('idiomas_falados').value);
            formData.append('data_nascimento', document.getElementById('data_nascimento').value);
            formData.append('area_interesse', document.getElementById('area_interesse').value);
            formData.append('expectativa_salarial', document.getElementById('expectativa_salarial').value);
            formData.append('pais_origem', document.getElementById('pais_origem').value);
            formData.append('experiencia_profissional', document.getElementById('experiencia_profissional').value);
            formData.append('habilidades_competencias', document.getElementById('habilidades_competencias').value);
        } else if (userData.table === 'oferecer') {
            formData.append('cargo', document.getElementById('cargo').value);
            formData.append('pais_empresa', document.getElementById('pais_empresa').value);
            formData.append('setor', document.getElementById('setor').value);
            formData.append('descricao_vaga', document.getElementById('descricao_vaga').value);
            formData.append('requisitos_vaga', document.getElementById('requisitos_vaga').value);
            formData.append('salario', document.getElementById('salario').value);
            formData.append('beneficios', document.getElementById('beneficios').value);
            formData.append('endereco_empresa', document.getElementById('endereco_empresa').value);
            formData.append('website_empresa', document.getElementById('website_empresa').value);
            formData.append('redes_sociais_empresa', document.getElementById('redes_sociais_empresa').value);
            formData.append('documento_identidade', document.getElementById('documento_identidade').value);
            formData.append('bio', document.getElementById('bio').value);
        }

        const fotoInput = document.getElementById('foto');
        if (fotoInput.files.length > 0) {
            formData.append('foto', fotoInput.files[0]);
        }

        fetch('atualizar_perfil.php', {
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
});
