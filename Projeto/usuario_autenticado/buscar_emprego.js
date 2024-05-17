document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.getElementById('form-container');
    formContainer.innerHTML = `
        <div class="form-container">
            <h1>Cadastro - Buscar Emprego</h1>
            <form id="seek-job-form" action="../usuario_autenticado/processar_cadastro_buscar.php" method="POST" enctype="multipart/form-data">
                <!-- Campos comuns -->
                <div class="form-group">
                    <label for="bio">Breve biografia:</label>
                    <textarea id="bio" name="bio" placeholder="Breve biografia" required></textarea>
                </div>
                <div class="form-group">
                    <label for="photo">Foto:</label>
                    <input id="photo" type="file" name="photo" accept="image/*" required>
                </div>
                
                <!-- Campos específicos para buscar emprego -->
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
                <div class="form-group">
                    <label for="languages">Idiomas falados:</label>
                    <input id="languages" type="text" name="languages" placeholder="Idiomas falados" required>
                </div>
                <div class="form-group">
                    <label for="job_type">Tipo de emprego desejado:</label>
                    <select id="job_type" name="job_type" required>
                        <option value="" disabled selected>Tipo de emprego desejado</option>
                        <option value="tempo integral">Tempo Integral</option>
                        <option value="meio período">Meio Período</option>
                        <option value="freelancer">Freelancer</option>
                    </select>
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
                    <label for="availability">Disponibilidade para começar:</label>
                    <input id="availability" type="date" name="availability" placeholder="Disponibilidade para começar" required>
                </div>
                <div class="form-group">
                    <label for="cv">CV:</label>
                    <input id="cv" type="file" name="cv" accept=".pdf,.doc,.docx">
                </div>
                <div class="form-group">
                    <label for="certificates">Certificados:</label>
                    <input id="certificates" type="file" name="certificates" accept=".pdf,.doc,.docx" multiple>
                </div>
                <button type="submit" class="btn btn-green">Cadastrar</button>
            </form>
        </div>
    `;
});
