// validar_token.js
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

function encryptToken(token, publicKey) {
    const encrypt = new JSEncrypt();
    encrypt.setPublicKey(publicKey);
    return encrypt.encrypt(token);
}

document.getElementById('validateButton').addEventListener('click', function() {
    const token = document.getElementById('token').value;
    const email = new URLSearchParams(window.location.search).get('email');

    getCertificate().then(cert => {
        const publicKey = extractPublicKey(cert);
        const encryptedToken = encryptToken(token, publicKey);

        fetch('validar_token.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                token: encryptedToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Token validado com sucesso!');
                window.location.href = '2fa.html';
            } else {
                alert('Falha na validação do token: ' + data.error);
            }
        })
        .catch(error => console.error('Erro:', error));
    });
});
