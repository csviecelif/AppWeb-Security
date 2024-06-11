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
        <form id="offer-job-form">
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
            <button type="button" id="submit-btn" class="btn btn-green">Cadastrar</button>
        </form>
    `;

    document.getElementById('submit-btn').addEventListener('click', async function() {
        const formData = new FormData(document.getElementById('offer-job-form'));

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
        fetch('../usuario_autenticado/processar_cadastro_oferecer.php', {
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
