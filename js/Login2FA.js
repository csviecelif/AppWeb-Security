window.addEventListener('pageshow', function (event) {
    fetch("../cadastro/verificarsessao.php", {
        method: "GET",
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === false) {
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

            fetch('../cadastro/getflag2fa.php')
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
                            <input type="text" name="OTP" placeholder="Coloque seu Código OTP" required="">
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
        }
    })
    .catch(error => {
        console.error('Erro durante a verificação de sessão:', error);
    });
});

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
fetch('../cadastro/getflag2fa.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na solicitação. Código de status: ' + response.status);
        }
        return response.text(); // Alterado de response.json() para response.text()
    })
    .then(data => {
        console.log(data); // Exibe a resposta no console para análise
        // Restante do código...
    })
    .catch(error => {
        console.error('Erro durante a solicitação da Flag2FA:', error);
    });
