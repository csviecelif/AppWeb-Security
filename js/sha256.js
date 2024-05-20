// Função para calcular o hash SHA-256 de uma string usando CryptoJS
function calcularSHA256(str) {
    // Calcular o hash SHA-256
    var hash = CryptoJS.SHA256(str);
    
    // Retornar o hash como uma string hexadecimal
    return hash.toString(CryptoJS.enc.Hex);
}
