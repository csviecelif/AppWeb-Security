function validarSenhaForte(senha) {
    var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}$/;
    return regex.test(senha);
}

function validarEmail(email) {
    var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function validarCPF(cpf) {
    var regex = /^\d{3}\.\d{3}\.\d{3}-\d{2}$/;
    return regex.test(cpf);
}

function validarTelefone(telefone) {
    var regex = /^\(\d{2}\) \d{4,5}-\d{4}$/;
    return regex.test(telefone);
}

function validarFormularioCadastro() {
    var nomeCompleto = document.getElementById('nomeCompleto').value;
    var email = document.getElementById('email').value;
    var senha = document.getElementById('senha').value;
    var confirmarSenha = document.getElementById('confirmarSenha').value;
    var cpf = document.getElementById('cpf').value;
    var telefone = document.getElementById('telefone').value;

    if (!validarEmail(email)) {
        alert('Por favor, informe um email válido.');
        return false;
    }

    if (!validarSenhaForte(senha)) {
        alert('A senha deve ter pelo menos 8 caracteres, incluindo pelo menos um número, uma letra maiúscula, uma letra minúscula e um caracter especial.');
        return false;
    }

    if (senha !== confirmarSenha) {
        alert('As senhas informadas não coincidem.');
        return false;
    }

    if (senha == confirmarSenha) {
        var senha = CryptoJS.SHA256(senha).toString(CryptoJS.enc.Hex);
    }

    if (!validarCPF(cpf)) {
        alert('Por favor, informe um CPF válido no formato 999.999.999-99.');
        return false;
    }

    if (!validarTelefone(telefone)) {
        alert('Por favor, informe um telefone válido no formato (99) 99999-9999');
        return false;
    }

    return true;
}
