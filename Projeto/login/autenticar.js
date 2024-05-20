
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

function VerifyOTP() {
    const userInput = document.getElementById('form').elements['OTP'].value;
    fetch('check2fa.php', {
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
                console.log('User ID:', data.userId);
                const userId = data.userId; //check2fa.php
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

                    if (result.oferecerEmprego) {
                        location.href = "../funcionalidades/candidatos_disponiveis.html";
                    } else if (result.buscarEmprego) {
                        location.href = "../funcionalidades/empregos_disponiveis.html";
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
}



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
                location.href = "logado.html";
            } else {
                console.log('Falha: ' + data.error);
                alert('OTP Inválido. Não ativado.');
            }
        })
        .catch(error => console.error(error.message));
}