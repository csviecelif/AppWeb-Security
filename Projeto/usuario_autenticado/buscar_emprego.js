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
        <form id="seek-job-form" action="../usuario_autenticado/processar_cadastro_buscar.php" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label for="bio">Breve biografia:</label>
                        <textarea id="bio" name="bio" placeholder="Breve biografia" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="photo">Foto de Perfil:</label>
                        <input id="photo" type="file" name="photo" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="experience">Experiência profissional:</label>
                        <textarea id="experience" name="experience" placeholder="Experiência profissional" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="skills">Habilidades e competências:</label>
                        <textarea id="skills" name="skills" placeholder="Habilidades e competências" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="education">Formação acadêmica:</label>
                        <input id="education" type="text" name="education" placeholder="Formação acadêmica" required>
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label for="languages">Idiomas falados:</label>
                        <input id="languages" type="text" name="languages" placeholder="Idiomas falados" required>
                    </div>
                    <div class="form-group">
                        <label for="birthdate">Data de nascimento:</label>
                        <input id="birthdate" type="date" name="birthdate" placeholder="Data de nascimento" required>
                    </div>
                    <div class="form-group">
                        <label for="country">País de origem:</label>
                        <input id="country" type="text" name="country" placeholder="País de origem" required>
                    </div>
                    <div class="form-group">
                        <label for="interest_area">Área de interesse:</label>
                        <input id="interest_area" type="text" name="interest_area" placeholder="Área de interesse" required>
                    </div>
                    <div class="form-group">
                        <label for="expected_salary">Expectativa salarial:</label>
                        <input id="expected_salary" type="number" name="expected_salary" placeholder="Expectativa salarial" required>
                    </div>
                    <div class="form-group">
                        <label for="cv">CV:</label>
                        <input id="cv" type="file" name="cv" accept=".pdf,.doc,.docx">
                    </div>
                    <div class="form-group">
                        <label for="certificates">Certificados:</label>
                        <input id="certificates" type="file" name="certificates" accept=".pdf,.doc,.docx" multiple>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-green">Cadastrar</button>
        </form>
    `;
});
