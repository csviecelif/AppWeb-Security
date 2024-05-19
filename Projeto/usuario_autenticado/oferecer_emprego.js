document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.getElementById('form-container');

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
    
    formContainer.innerHTML = `
        <form id="offer-job-form" action="../usuario_autenticado/processar_cadastro_oferecer.php" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label for="bio">Breve biografia:</label>
                        <textarea id="bio" name="bio" placeholder="Breve biografia" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="photo">Foto de Perfil:</label>
                        <input id="photo" type="file" name="photo" required>
                    </div>
                    <div class="form-group">
                        <label for="company_name">Nome da empresa:</label>
                        <input id="company_name" type="text" name="company_name" placeholder="Nome da empresa" required>
                    </div>
                    <div class="form-group">
                        <label for="position">Cargo/Função:</label>
                        <input id="position" type="text" name="position" placeholder="Cargo/Função" required>
                    </div>
                    <div class="form-group">
                        <label for="sector">Setor/Área de atuação:</label>
                        <input id="sector" type="text" name="sector" placeholder="Setor/Área de atuação" required>
                    </div>
                    <div class="form-group">
                        <label for="job_description">Descrição da vaga:</label>
                        <textarea id="job_description" name="job_description" placeholder="Descrição da vaga" required></textarea>
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label for="job_requirements">Requisitos da vaga:</label>
                        <textarea id="job_requirements" name="job_requirements" placeholder="Requisitos da vaga" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="salary">Salário oferecido:</label>
                        <input id="salary" type="number" name="salary" placeholder="Salário oferecido" required>
                    </div>
                    <div class="form-group">
                        <label for="benefits">Benefícios:</label>
                        <textarea id="benefits" name="benefits" placeholder="Benefícios" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="company_address">Endereço da empresa:</label>
                        <input id="company_address" type="text" name="company_address" placeholder="Endereço da empresa" required>
                    </div>
                    <div class="form-group">
                        <label for="company_website">Website da empresa (opcional):</label>
                        <input id="company_website" type="url" name="company_website" placeholder="Website da empresa (opcional)">
                    </div>
                    <div class="form-group">
                        <label for="company_social">Redes sociais da empresa (opcional):</label>
                        <input id="company_social" type="url" name="company_social" placeholder="Redes sociais da empresa (opcional)">
                    </div>
                    <div class="form-group">
                        <label for="company_country">País da Empresa:</label>
                        <input id="company_country" type="text" name="company_country" placeholder="País da Empresa" required>
                    </div>
                    <div class="form-group">
                        <label for="company_id">Documento de identidade (CNPJ ou equivalente):</label>
                        <input id="company_id" type="text" name="company_id" placeholder="Documento de identidade (CNPJ ou equivalente)" required>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-green">Cadastrar</button>
        </form>
    `;
});
