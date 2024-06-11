document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.getElementById('form-container');

    fetch("../cadastro/verificarsessao.php", {
        method: "GET",
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === false) {
            location.href = "../login/index.html";
        }
    })
    .catch(error => console.error('Erro ao verificar sessão:', error));
    
    formContainer.innerHTML = `
        <form id="seek-job-form">
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
                    <div class="form-group">
                        <label for="job_type">Tipo de emprego desejado:</label>
                        <input id="job_type" type="text" name="job_type" placeholder="Tipo de emprego desejado" required>
                    </div>
                    <div class="form-group">
                        <label for="availability">Disponibilidade:</label>
                        <input id="availability" type="text" name="availability" placeholder="Disponibilidade" required>
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
            <button type="button" id="submit-btn" class="btn btn-green">Cadastrar</button>
        </form>
    `;

    document.getElementById('submit-btn').addEventListener('click', async function() {
        const formData = new FormData(document.getElementById('seek-job-form'));

        // Obtendo a chave pública do servidor
        const publicKey = await getPublicKeyFromServer();

        // Gerando chave secreta e IV
        const secretKey = CryptoJS.lib.WordArray.random(32).toString(CryptoJS.enc.Hex);
        const iv = CryptoJS.lib.WordArray.random(16).toString(CryptoJS.enc.Hex);

        // Criptografando os dados do formulário
        const jsonObject = {};
        formData.forEach((value, key) => {
            if (typeof value === 'string') {
                jsonObject[key] = value;
            }
        });
        const jsonString = JSON.stringify(jsonObject);
        const encryptedMessage = CryptoJS.AES.encrypt(jsonString, CryptoJS.enc.Hex.parse(secretKey), { iv: CryptoJS.enc.Hex.parse(iv) }).toString();

        // Criptografando a chave secreta com a chave pública
        const encryptedSecretKey = encryptSecretKey(secretKey, publicKey);

        // Adicionando dados criptografados ao FormData
        formData.append('iv', iv);
        formData.append('secretKey', encryptedSecretKey);
        formData.append('mensagem', encryptedMessage);

        // Enviando os dados para o servidor
        fetch('../usuario_autenticado/processar_cadastro_buscar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cadastro realizado com sucesso!');
                window.location.href = 'mostrar_perfil.html';
            } else {
                alert('Erro ao realizar cadastro: ' + data.message);
            }
        })
        .catch(error => console.error('Erro ao enviar dados:', error));
    });
});

async function getPublicKeyFromServer() {
    const response = await fetch('../cert/public.key');
    const publicKey = await response.text();
    return publicKey;
}

function encryptSecretKey(secretKey, publicKey) {
    const encrypt = new JSEncrypt();
    encrypt.setPublicKey(publicKey);
    return encrypt.encrypt(secretKey);
}
