// Função para validar senha forte
function validarSenhaForte(senha) {
    // Pelo menos 8 caracteres, 1 número, 1 letra maiúscula, 1 letra minúscula, 1 caracter especial
    var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}$/;
    return regex.test(senha);
}

// Função para validar email com regex
function validarEmail(email) {
    var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Função para validar CPF com regex
function validarCPF(cpf) {
    var regex = /^\d{3}\.\d{3}\.\d{3}-\d{2}$/;
    return regex.test(cpf);
}

// Função para validar telefone com regex
function validarTelefone(telefone) {
    // Aceita números no formato (99) 99999-9999 ou (99) 9999-9999
    var regex = /^\(\d{2}\) \d{4,5}-\d{4}$/;
    return regex.test(telefone);
}

// Função para validar o formulário de cadastro antes de enviar
function validarFormularioCadastro() {
    var nomeCompleto = document.getElementById('nomeCompleto').value;
    var email = document.getElementById('email').value;
    var senha = document.getElementById('senha').value;
    var confirmarSenha = document.getElementById('confirmarSenha').value;
    var cpf = document.getElementById('cpf').value;
    var telefone = document.getElementById('telefone').value;

    // Validar email
    if (!validarEmail(email)) {
        alert('Por favor, informe um email válido.');
        return false;
    }

    // Validar senha forte
    if (!validarSenhaForte(senha)) {
        alert('A senha deve ter pelo menos 8 caracteres, incluindo pelo menos um número, uma letra maiúscula, uma letra minúscula e um caracter especial.');
        return false;
    }

    // Validar confirmação de senha
    if (senha !== confirmarSenha) {
        alert('As senhas informadas não coincidem.');
        return false;
    }

    // Validar CPF
    if (!validarCPF(cpf)) {
        alert('Por favor, informe um CPF válido no formato 999.999.999-99.');
        return false;
    }

    // Validar telefone
    if (!validarTelefone(telefone)) {
        alert('Por favor, informe um telefone válido no formato (99) 99999-9999');
        return false;
    }

    return true; // Permite o envio do formulário se todas as validações passarem
}
