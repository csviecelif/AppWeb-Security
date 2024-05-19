function validarFormularioCadastro() {
    var senha = document.getElementById('senha').value;
    if (!validarSenhaForte(senha)) {
        alert('A senha deve ter pelo menos 8 caracteres, incluindo pelo menos um número, uma letra maiúscula, uma letra minúscula e um caracter especial.');
        return false;
    }
    var senhaHash = calcularSHA256(senha);
    document.getElementById('senha').value = senhaHash;
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
