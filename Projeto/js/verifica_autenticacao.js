async function getCertificate() {
    const response = await fetch('../cert/enviar_certificado.php');
    const cert = await response.text();
    return cert;
}

function extractPublicKey(cert) {
    const certificate = forge.pki.certificateFromPem(cert);
    const publicKey = forge.pki.publicKeyToPem(certificate.publicKey);
    return publicKey;
}

window.onload = function() {
    getPublicKeyFromServer().then(publicKey => {
        const secretKey = CryptoJS.lib.WordArray.random(32).toString(CryptoJS.enc.Hex);
        const iv = CryptoJS.lib.WordArray.random(16).toString(CryptoJS.enc.Hex);
        const encryptedSecretKey = encryptSecretKey(secretKey, publicKey);

        fetch('../cadastro/verificarsessao.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                iv: iv,
                secretKey: encryptedSecretKey
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.status) {
                window.location.href = '../login/index.html';
            }
        })
        .catch(error => console.error('Erro durante a verificação de autenticação:', error));

        document.getElementById('offer-job').addEventListener('click', function() {
            window.location.href = '../usuario_autenticado/oferecer_emprego.html';
        });

        document.getElementById('seek-job').addEventListener('click', function() {
            window.location.href = '../usuario_autenticado/buscar_emprego.html';
        });
    });
};

function encryptSecretKey(secretKey, publicKey) {
    const encrypt = new JSEncrypt();
    encrypt.setPublicKey(publicKey);
    return encrypt.encrypt(secretKey);
}