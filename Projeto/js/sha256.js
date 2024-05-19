function calcularSHA256(str) {
    var hash = CryptoJS.SHA256(str);
    return hash.toString(CryptoJS.enc.Hex);
}
