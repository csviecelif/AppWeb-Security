document.addEventListener('DOMContentLoaded', function() {
    const button = document.querySelector('button');
    if (button) {
        button.addEventListener('click', loadCertificate);
    } else {
        console.error("Botão não encontrado no DOM.");
    }
});

function generateSecretKey() {
    // Gerar uma chave secreta aleatória de 256 bits (32 bytes)
    return CryptoJS.lib.WordArray.random(32).toString(CryptoJS.enc.Hex);
}

async function loadCertificate() {
    try {
        const response = await fetch('enviar_certificado.php');
        if (!response.ok) {
            throw new Error('Erro ao carregar o certificado');
        }
        const certText = await response.text();
        console.log("Certificado carregado:", certText);
        const publicKey = extractPublicKey(certText);
        console.log("Chave pública extraída:", publicKey);
        encryptData(publicKey);
    } catch (error) {
        console.error("Erro ao carregar o certificado:", error);
    }
}

function extractPublicKey(cert) {
    const certificate = forge.pki.certificateFromPem(cert);
    const publicKey = forge.pki.publicKeyToPem(certificate.publicKey);
    return publicKey;
}

function encryptData(publicKey) {
    const encrypt = new JSEncrypt();
    encrypt.setPublicKey(publicKey);
    const secretKey = generateSecretKey();
    console.log("Chave secreta gerada: ", secretKey);
    const encrypted = encrypt.encrypt(secretKey);
    if (!encrypted) {
        console.error("Falha ao criptografar a chave secreta.");
        return;
    }
    console.log("Chave secreta criptografada: ", encrypted);

    // Gerar HMAC da chave secreta criptografada
    const hmac = CryptoJS.HmacSHA256(encrypted, secretKey).toString();
    console.log("HMAC gerado: ", hmac);

    sendEncryptedData(encrypted, hmac);
}

async function sendEncryptedData(encryptedData, hmac) {
    try {
        const response = await fetch('receber_dados.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ data: encryptedData, hmac: hmac })
        });
        const result = await response.json();
        if (result.error) {
            throw new Error(result.error);
        }
        console.log(result);
    } catch (error) {
        console.error("Erro ao enviar dados criptografados:", error);
    }
}
