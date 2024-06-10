<?php
session_start();
require_once '../vendor/autoload.php'; // Certifique-se de que o autoload está no caminho correto
use OTPHP\TOTP;
require_once '../login/connection.php';

$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados || !isset($dados['OTP'])) {
    echo json_encode(['error' => 'Dados criptografados não recebidos corretamente.']);
    exit();
}

$encryptedOTP = $dados['OTP'];

$privateKeyPath = realpath(__DIR__ . '/../cert/private.key');
if (!file_exists($privateKeyPath)) {
    echo json_encode(['error' => 'Arquivo de chave privada não encontrado.']);
    exit();
}

$privateKey = openssl_pkey_get_private('file://' . $privateKeyPath);
if (!$privateKey) {
    echo json_encode(['error' => 'Falha ao carregar a chave privada: ' . openssl_error_string()]);
    exit();
}

$success = openssl_private_decrypt(base64_decode($encryptedOTP), $decryptedOTP, $privateKey);
if (!$success) {
    echo json_encode(['error' => 'Falha ao descriptografar o OTP: ' . openssl_error_string()]);
    exit();
}

$sql = "SELECT twoef FROM usuarios WHERE userId = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $_SESSION['userId']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['error' => 'Usuário não encontrado.']);
    exit();
}

$otp = TOTP::createFromSecret($user['twoef']);
if ($otp->verify($decryptedOTP)) {
    echo json_encode(['success' => true, 'userId' => $_SESSION['userId']]);
} else {
    echo json_encode(['error' => 'OTP inválido.']);
}

$stmt->close();
$con->close();
?>
