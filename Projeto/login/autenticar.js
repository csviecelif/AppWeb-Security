window.addEventListener('pageshow', function (event) {
    fetch('../cadastro/getFlag2FA.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na solicitação. Código de status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error('Erro ao consultar a Flag2FA no banco de dados:', data.error);
                alert('Erro ao consultar a Flag2FA no banco de dados: ' + data.error);
            } else if (data.flag2FA === 1) {
                console.log('Sucesso: A Flag2FA está ativada.');
                const content = `
                    <form id="form">
                        <input type="text" name="OTP" placeholder="Coloque seu Código OTP" required>
                    </form>
                    <button onclick="VerifyOTP()" type="button">Verificar</button>
                `;
                document.getElementById('box').innerHTML += content;
            } else if (data.flag2FA === 0) {
                console.log('Flag2FA está desativada. Procedendo para gerar QR Code.');
                get2FACode();
                const content = `
                    <div id="qrCodeContainer"></div>
                    <form id="form">
                        <input type="text" name="OTP" placeholder="Coloque seu Código OTP">
                    </form>
                    <button onclick="Ativar2FA()" type="button">Ativar</button>
                `;
                document.getElementById('box').innerHTML += content;
            } else {
                console.error('Erro: Valor inesperado para Flag2FA.');
            }
        })
        .catch(error => {
            console.error('Erro durante a solicitação da Flag2FA:', error);
        });
});

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

function encryptSecretKey(secretKey, publicKey) {
    const encrypt = new JSEncrypt();
    encrypt.setPublicKey(publicKey);
    return encrypt.encrypt(secretKey);
}

function VerifyOTP() {
    const userInput = document.getElementById('form').elements['OTP'].value;

    getCertificate().then(cert => {
        const publicKey = extractPublicKey(cert);
        const encryptedOTP = encryptSecretKey(userInput, publicKey);

        fetch('check2fa.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                OTP: encryptedOTP
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na solicitação. Código de status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Sucesso: OTP válido.');
                alert('OTP válido!');
                const userId = data.userId;
                fetch('pegar_cadastro.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ userId: userId })
                })
                .then(response => response.json())
                .then(result => {
                    console.log('Resultado da busca:', result);

                    if (result.buscarEmprego) {
                        location.href = "../funcionalidades/empregos_disponiveis.html";
                    } else if (result.oferecerEmprego) {
                        location.href = "../funcionalidades/candidatos_disponiveis.html";
                    } else {
                        location.href = "logado.html";
                    }
                })
                .catch(error => console.error('Erro ao buscar cadastro:', error));
            } else {
                console.log('Falha: ' + data.error);
                alert('OTP inválido!');
            }
        })
        .catch(error => console.error(error.message));
    });
}

function gerarQRCode(secret) {
    const label = encodeURIComponent('GlobalOpportuna'); 
    const issuer = encodeURIComponent('GlobalOpportuna'); 
    const tag = `otpauth://totp/${label}?secret=${secret}&issuer=${issuer}`;
    const qrCodeUri = 'https://api.qrserver.com/v1/create-qr-code/?data=' + encodeURIComponent(tag) + '&size=200x200&ecc=M';
    
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    qrCodeContainer.innerHTML = '<img src="' + qrCodeUri + '" alt="QRCode do 2FA">';
}

function get2FACode() {
    fetch('get2FACode.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na solicitação. Código de status: ' + response.status);
            }
            return response.json();
        })
        .then(data => gerarQRCode(data.secret))
        .catch(error => console.error(error.message));
}

function Ativar2FA() {
    const userInput = document.getElementById('form').elements['OTP'].value;

    getCertificate().then(cert => {
        const publicKey = extractPublicKey(cert);
        const encryptedOTP = encryptSecretKey(userInput, publicKey);

        fetch('../cadastro/Ativar2FA.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                OTP: encryptedOTP
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na solicitação. Código de status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('OTP Válido');
                alert('OTP válido! 2FA ATIVADO!!');
                location.href = "logado.html";
            } else {
                console.log('Falha: ' + data.error);
                alert('OTP Inválido. Não ativado.');
            }
        })
        .catch(error => console.error(error.message));
    });
}
