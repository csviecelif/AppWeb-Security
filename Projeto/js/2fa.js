window.addEventListener('pageshow', function (event) {
    fetch('../cadastro/getFlag2FA_Cadastro.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na solicitação. Código de status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.flag2FA === 1) {
                alert('Sucesso: A Flag2FA está ativada.');
                const content = `
                    <form id="form">
                        <input type="text" name="OTP" placeholder="Escaneie e insira o código" required="">
                    </form>
                    <button onclick="VerifyOTP()" type="button">Verificar</button>
                `;
                document.getElementById('box').innerHTML += content;
            } else if (data.flag2FA === 0) {
                alert('Flag2FA está desativada. Procedendo para gerar QR Code.');
                get2FACode();
                const content = `
                    <div id="qrCodeContainer"></div>
                    <form id="form">
                        <input type="text" name="OTP" placeholder="Escaneie e insira o código" required="">
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

function get2FACode() {
    fetch('../cadastro/get2FACode.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na solicitação. Código de status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.qrCode) {
                const qrCodeContainer = document.getElementById('qrCodeContainer');
                qrCodeContainer.innerHTML = '<img src="data:image/png;base64,' + data.qrCode + '" alt="QRCode do 2FA">';
            } else {
                console.error('Erro ao obter o código 2FA:', data.error);
            }
        })
        .catch(error => console.error('Erro ao obter o código 2FA:', error));
}

function Ativar2FA() {
    const userInput = document.getElementById('form').elements['OTP'].value;
    getCertificate().then(cert => {
        const publicKey = extractPublicKey(cert);
        const encryptedOTP = encryptMessage(userInput, publicKey);
        
        // Verifique o conteúdo criptografado no console
        console.log('OTP criptografado:', encryptedOTP);
        
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
                alert('OTP válido! 2FA ATIVADO!!');
                location.href = "../login/logado.html";
            } else {
                alert('OTP Inválido. Não ativado.');
            }
        })
        .catch(error => console.error('Erro ao ativar 2FA:', error));
    });
}


function VerifyOTP() {
    const userInput = document.getElementById('form').elements['OTP'].value;
    getCertificate().then(cert => {
        const publicKey = extractPublicKey(cert);
        const encryptedOTP = encryptMessage(userInput, publicKey);
        fetch('../cadastro/Verificar2FA.php', {
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
                alert('OTP válido!');
                location.href = "../login/logado.html";
            } else {
                alert('OTP inválido!');
            }
        })
        .catch(error => console.error('Erro ao verificar OTP:', error));
    });
}

async function getCertificate() {
    const response = await fetch('../cert/enviar_certificado.php');
    const cert = await response.text();
    return cert;
}

function encryptMessage(message, publicKey) {
    const encrypt = new JSEncrypt();
    encrypt.setPublicKey(publicKey);
    return encrypt.encrypt(message);
}

function extractPublicKey(cert) {
    const certificate = forge.pki.certificateFromPem(cert);
    const publicKey = forge.pki.publicKeyToPem(certificate.publicKey);
    return publicKey;
}
