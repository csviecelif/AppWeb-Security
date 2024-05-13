// Função para validar senha forte e aplicar hash
function validarFormularioCadastro() {
    var senha = document.getElementById('senha').value;

    // Validar senha forte
    if (!validarSenhaForte(senha)) {
        alert('A senha deve ter pelo menos 8 caracteres, incluindo pelo menos um número, uma letra maiúscula, uma letra minúscula e um caracter especial.');
        return false;
    }
    
    // Calcular o hash SHA-256 da senha
    var senhaHash = calcularSHA256(senha);

    // Substituir a senha original pelo hash antes de enviar o formulário
    document.getElementById('senha').value = senhaHash;

    return true; // Permite o envio do formulário se todas as validações passarem
}

// Função para calcular o hash SHA-256 de uma string usando CryptoJS
function calcularSHA256(str) {
    var hash = CryptoJS.SHA256(str);
    return hash.toString(CryptoJS.enc.Hex);
}

// Função para validar senha forte
function validarSenhaForte(senha) {
    var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}$/;
    return regex.test(senha);
}
