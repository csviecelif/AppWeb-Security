document.addEventListener('DOMContentLoaded', () => {
    const recuperarSenhaForm = document.getElementById('recuperarSenhaForm');
    const redefinirForm = document.getElementById('redefinirForm');

    if (recuperarSenhaForm) {
        recuperarSenhaForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = new FormData(recuperarSenhaForm);
            const email = formData.get('email');

            try {
                const response = await fetch('enviar_email.php', {
                    method: 'POST',
                    body: JSON.stringify({ email: email }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.href = 'redefinir.html';
                } else {
                    alert(`Erro: ${result.message}`);
                }
            } catch (error) {
                console.error('Erro ao enviar a solicitação:', error);
                alert('Ocorreu um erro ao enviar a solicitação. Tente novamente mais tarde.');
            }
        });
    }

    if (redefinirForm) {
        redefinirForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!validarFormularioCadastro()) {
                return;
            }

            const formData = new FormData(redefinirForm);
            const token = formData.get('token');
            const senha = document.getElementById('senha').value;
            const senhaHash = calcularSHA256(senha);

            try {
                const response = await fetch('redefinir.php', {
                    method: 'POST',
                    body: JSON.stringify({ token: token, senha: senhaHash }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.href = '../login/index.html';
                } else {
                    alert(`Erro: ${result.message}`);
                }
            } catch (error) {
                console.error('Erro ao enviar a solicitação:', error);
                alert('Ocorreu um erro ao enviar a solicitação. Tente novamente mais tarde.');
            }
        });
    }
});

function validarFormularioCadastro() {
    var senha = document.getElementById('senha').value;
    if (!validarSenhaForte(senha)) {
        alert('A senha deve ter pelo menos 8 caracteres, incluindo pelo menos um número, uma letra maiúscula, uma letra minúscula e um caracter especial.');
        return false;
    }
    return true;
}

function calcularSHA256(str) {
    var hash = CryptoJS.SHA256(str);
    return hash.toString(CryptoJS.enc.Hex);
}

function validarSenhaForte(senha) {
    var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}$/;
    return regex.test(senha);
}
