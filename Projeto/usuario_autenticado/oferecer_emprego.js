document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.getElementById('form-container');
    formContainer.innerHTML = `
        <div class="form-container">
            <h1>Cadastro - Oferecer Emprego</h1>
            <form id="offer-job-form" action="../usuario_autenticado/processar_cadastro_oferecer.php" method="POST" enctype="multipart/form-data">
                <!-- Campos comuns -->
                <div class="form-group">
                    <label for="bio">Breve biografia:</label>
                    <textarea id="bio" name="bio" placeholder="Breve biografia" required></textarea>
                </div>
                <div class="form-group">
                    <label for="photo">Foto:</label>
                    <input id="photo" type="file" name="photo" accept="image/*" required>
                </div>
                
                <!-- Campos específicos para oferecer emprego -->
                <div class="form-group">
                    <label for="company_name">Nome da empresa:</label>
                    <input id="company_name" type="text" name="company_name" placeholder="Nome da empresa" required>
                </div>
                <div class="form-group">
                    <label for="job_title">Cargo/Função:</label>
                    <input id="job_title" type="text" name="job_title" placeholder="Cargo/Função" required>
                </div>
                <div class="form-group">
                    <label for="job_type">Tipo de emprego:</label>
                    <select id="job_type" name="job_type" required>
                        <option value="" disabled selected>Tipo de emprego</option>
                        <option value="tempo integral">Tempo Integral</option>
                        <option value="meio período">Meio Período</option>
                        <option value="freelancer">Freelancer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sector">Setor/Área de atuação:</label>
                    <input id="sector" type="text" name="sector" placeholder="Setor/Área de atuação" required>
                </div>
                <div class="form-group">
                    <label for="job_description">Descrição da vaga:</label>
                    <textarea id="job_description" name="job_description" placeholder="Descrição da vaga" required></textarea>
                </div>
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
                    <label for="company_id">Documento de identidade (CNPJ ou equivalente):</label>
                    <input id="company_id" type="text" name="company_id" placeholder="Documento de identidade (CNPJ ou equivalente)" required>
                </div>
                <button type="submit" class="btn btn-green">Cadastrar</button>
            </form>
        </div>
    `;
});
