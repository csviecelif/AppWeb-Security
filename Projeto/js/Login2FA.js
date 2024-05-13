document.addEventListener('DOMContentLoaded', function() {
    checkSessionAnd2FAStatus();

    function checkSessionAnd2FAStatus() {
        fetch("../cadastro/verificarsessao.php")
            .then(response => response.json())
            .then(data => {
                if (data === "False") {
                    Swal.fire({
                        title: 'Você deve estar logado para acessar esta página',
                        text: 'Clique no botão abaixo para ir à página de login',
                        icon: 'error',
                        confirmButtonText: 'Logue',
                        position: "center"
                    }).then(() => {
                        window.location.href = "../login/index.html";
                    });
                } else {
                    fetch('../cadastro/getFlag2FA.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.flag2FA === 0) {
                                console.log('Flag2FA está desativada. Procedendo para gerar QR Code.');
                                get2FACode();
                            } else if (data.flag2FA === 1) {
                                displayOTPInput();
                            } else {
                                console.error('Erro: Valor inesperado para Flag2FA.');
                            }
                        });
                }
            })
            .catch(error => console.error('Erro durante a verificação de sessão:', error));
    }

    function displayOTPInput() {
        const content = `
            <form id="form">
                <input type="text" name="OTP" placeholder="Coloque seu Código OTP" required="">
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
});
