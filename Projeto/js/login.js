document.getElementById('loginForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;

    // Gerar chave secreta e IV
    const secretKey = generateSecretKey();
    const iv = generateIV();

    // Criptografar a senha usando AES
    const encryptedPassword = encryptMessage(senha, CryptoJS.enc.Hex.parse(secretKey), CryptoJS.enc.Hex.parse(iv));

    // Obter a chave pública do servidor para criptografar a chave secreta
    getCertificate().then(cert => {
        const publicKey = extractPublicKey(cert);
        const encryptedSecretKey = encryptSecretKey(secretKey, publicKey);

        // Enviar os dados criptografados ao servidor
        fetch('../login/autenticar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                senha: encryptedPassword,
                secretKey: encryptedSecretKey,
                iv: iv
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'autenticar.html';
            } else {
                alert(data.error);
            }
        })
        .catch(error => console.error('Erro:', error));
    });
});

// Função para criptografar a senha usando AES
function encryptMessage(message, secretKey, iv) {
    return CryptoJS.AES.encrypt(message, secretKey, { iv: iv }).toString();
}

// Função para gerar uma chave secreta AES de 256 bits
function generateSecretKey() {
    return CryptoJS.lib.WordArray.random(32).toString();
}

// Função para gerar um IV de 128 bits
function generateIV() {
    return CryptoJS.lib.WordArray.random(16).toString();
}

// Função para criptografar a chave AES usando RSA
function encryptSecretKey(secretKey, publicKey) {
    const encrypt = new JSEncrypt();
    encrypt.setPublicKey(publicKey);
    return encrypt.encrypt(secretKey);
}

// Função para extrair a chave pública do certificado
function extractPublicKey(cert) {
    const certificate = forge.pki.certificateFromPem(cert);
    const publicKey = forge.pki.publicKeyToPem(certificate.publicKey);
    return publicKey;
}

// Função para obter o certificado do servidor
async function getCertificate() {
    const response = await fetch('../cert/enviar_certificado.php');
    const cert = await response.text();
    return cert;
}
