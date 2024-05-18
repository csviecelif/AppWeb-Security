function hashSenhaSubmit() {
    var senha = document.getElementById('senha').value;
    var senhaHash = CryptoJS.SHA256(senha).toString(CryptoJS.enc.Hex);
    document.getElementById('senha').value = senhaHash; // Substitui a senha original pelo hash
    return true; // Permite que o formulário seja enviado
}