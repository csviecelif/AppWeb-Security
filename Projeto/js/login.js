function hashSenhaSubmit() {
    var senha = document.getElementById('senha').value;
    var senhaHash = CryptoJS.SHA256(senha).toString(CryptoJS.enc.Hex);
    document.getElementById('senha').value = senhaHash;
    return true;
}