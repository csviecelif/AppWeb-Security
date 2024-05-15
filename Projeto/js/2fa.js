window.addEventListener('pageshow', function (event) {
    fetch("../cadastro/verificarsessao.php", {
        method: "GET",
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === false) { // Corrigido de `data === "False"` para `data.status === false`
            alert('Você deve estar logado para acessar esta página');
            location.href = "../login/index.html";
        } else {
            const logged = `
                <div id="container" class="container">
                    <div class="divisao">
                        <div id="box" class="box-login"></div>
                    </div>
                </div>
            `;
            document.body.innerHTML += logged;

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
        }
    })
    .catch(error => {
        console.error('Erro durante a verificação de sessão:', error);
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
        .then(data => gerarQRCode(data.secret))
        .catch(error => console.error);
}

function gerarQRCode (secret) {
    const tag = "otpauth://totp/GlobalOpportuna?secret=";
    const qrCodeUri = 'https://api.qrserver.com/v1/create-qr-code/?data=' + encodeURIComponent(tag + secret) + '&size=200x200&ecc=M';
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    qrCodeContainer.innerHTML = '<img src="' + qrCodeUri + '" alt = "QRCode do 2FA">';
}

function Ativar2FA() {
    const userInput = document.getElementById('form').elements['OTP'].value;
    fetch('../cadastro/Ativar2FA.php', {
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
                console.log('OTP Válido');
                alert('OTP válido! 2FA ATIVADO!!');
                location.href = "../login/logado.html";
            } else {
                console.log('Falha: ' + data.error);
                alert('OTP Inválido. Não ativado.');
            }
        })
        .catch(error => console.error(error.message));
}

function VerifyOTP() {
    const userInput = document.getElementById('form').elements['OTP'].value;
    fetch('../cadastro/Verificar2FA.php', {
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