window.onload = function() {
    // Seu código vai aqui
    alert('A página foi completamente carregada!');

function get2FACode() {
        fetch('../cadastro/get2FACode.php')  // Ajuste o caminho conforme necessário
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na solicitação. Código de status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log("Dados recebidos para QR Code:", data);

                // Gerar o QR Code
                var otpAuthUrl = `otpauth://totp/SecuredApp:${data.email}?secret=${data.segredo}&issuer=SecuredApp`;
                console.log("URL PARA QR CODE:", otpAuthUrl);
                new QRCode(document.getElementById('qrCodeContainer'), {
                    text: otpAuthUrl,
                    width: 128,
                    height: 128,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            })
            .catch(error => console.error('Erro durante a solicitação da chave secreta:', error));
    }

    fetch('../cadastro/getFlag2FA.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na solicitação. Código de status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.flag2FA === 1) {
            document.getElementById('box').innerHTML = '<p>O 2FA já está ativado.</p>';
        } else if (data.flag2FA === 0) {
            console.log('Flag2FA está desativada. Procedendo para gerar QR Code.');
            get2FACode();
        } else {
            console.error('Erro: Valor inesperado para Flag2FA.');
        }
    })
    .catch(error => {
        console.error('Erro durante a solicitação da Flag2FA:', error);
    });
}