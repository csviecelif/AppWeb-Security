document.getElementById('cadastroForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const nomeCompleto = document.getElementById('nomeCompleto').value;
    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;
    const confirmarSenha = document.getElementById('confirmarSenha').value;
    const cpf = document.getElementById('cpf').value;
    const telefone = document.getElementById('telefone').value;

    if (senha !== confirmarSenha) {
        alert('As senhas não coincidem.');
        return;
    }

    const secretKey = generateSecretKey();
    const iv = generateIV();
    const encryptedPassword = encryptMessage(senha, CryptoJS.enc.Hex.parse(secretKey), CryptoJS.enc.Hex.parse(iv));

    fetch('../cert/enviar_certificado.php')
        .then(response => response.text())
        .then(certText => {
            const publicKey = extractPublicKey(certText);
            const encryptedSecretKey = encryptSecretKey(secretKey, publicKey);

            if (!encryptedSecretKey) {
                throw new Error('Falha ao criptografar a chave secreta.');
            }

            fetch('confirmar_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nomeCompleto: nomeCompleto,
                    email: email,
                    senha: encryptedPassword,
                    secretKey: encryptedSecretKey,
                    iv: iv,
                    cpf: cpf,
                    telefone: telefone
                })
            })
            .then(response => response.text())
            .then(text => {
                console.log(text);  // Adicionado para verificar a resposta
                return JSON.parse(text);
            })
            .then(data => {
                if (data.success) {
                    window.location.href = 'validar_token.html?email=' + encodeURIComponent(email);
                } else {
                    alert(data.error);
                }
            })
            .catch(error => console.error('Erro:', error));
        })
        .catch(error => console.error('Erro ao obter a chave pública:', error));
});

function encryptMessage(message, secretKey, iv) {
    return CryptoJS.AES.encrypt(message, secretKey, { iv: iv }).toString();
}

function generateSecretKey() {
    return CryptoJS.lib.WordArray.random(32).toString();
}

function generateIV() {
    return CryptoJS.lib.WordArray.random(16).toString();
}

function encryptSecretKey(secretKey, publicKey) {
    const encrypt = new JSEncrypt();
    encrypt.setPublicKey(publicKey);
    return encrypt.encrypt(secretKey);
}

function extractPublicKey(cert) {
    const certificate = forge.pki.certificateFromPem(cert);
    const publicKey = forge.pki.publicKeyToPem(certificate.publicKey);
    return publicKey;
}
