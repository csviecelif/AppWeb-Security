window.addEventListener('pageshow', function (event) {
    fetch('../cadastro/getFlag2FA.php')
    .then(response => response.json())
    .then(data => {
        const logged = `
            <div id="container" class="container">
                <div class="divisao">
                    <div id="box" class="box-login"></div>
                </div>
            </div>
        `;
        document.body.innerHTML += logged;

        if (data.flag2FA === 0) {
            console.log('Flag2FA está desativada. Procedendo para gerar QR Code.');
            get2FACode();
        } else if (data.flag2FA === 1) {
            displayOTPInput();
        } else {
            console.error('Erro: Valor inesperado para Flag2FA.');
        }
    })
    .catch(error => console.error('Erro na solicitação do Flag2FA:', error));
});

function displayOTPInput() {
    const content = `
        <form id="form">
            <input type="text" name="OTP" placeholder="Coloque seu Código OTP" required>
            <button onclick="VerifyOTP()" type="button">Verificar</button>
        </form>
    `;      
    document.getElementById('box').innerHTML = content;
}

function get2FACode() {
    fetch('../cadastro/get2FACode.php')
        .then(response => response.json())
        .then(data => gerarQRCode(data.secret))
        .catch(error => console.error('Erro na solicitação do QR Code:', error));
}

function gerarQRCode(secret) {
    const tag = "otpauth://totp/GlobalOpportuna?secret=";
    const qrCodeUri = 'https://api.qrserver.com/v1/create-qr-code/?data=' + encodeURIComponent(tag + secret) + '&size=200x200&ecc=M';
    document.getElementById('box').innerHTML = '<img src="' + qrCodeUri + '" alt="QRCode do 2FA">';
}

function VerifyOTP() {
    const userInput = document.getElementById('form').elements['OTP'].value;

    fetch('../login/check2fa.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'OTP=' + encodeURIComponent(userInput)
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
            location.href = "../login/logado.html";
        } else {
            console.log('Falha: ' + data.error);
            alert('OTP inválido!');
        }
    })
    .catch(error => console.error(error.message));
}
