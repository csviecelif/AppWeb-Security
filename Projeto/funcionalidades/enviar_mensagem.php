<?php
header('Content-Type: application/json');
include '../login/connection.php';

function logSecurityEvent($message) {
    $logFile = 'security.log';
    $date = new DateTime();
    file_put_contents($logFile, $date->format('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

session_start();

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    logSecurityEvent("Usuário não autenticado");
    exit();
}

$iv = $_POST['iv'];
$encryptedSecretKey = $_POST['secretKey'];
$encryptedMessage = $_POST['mensagem'];

logSecurityEvent("Dados criptografados recebidos: IV = $iv, SecretKey = $encryptedSecretKey, Mensagem = $encryptedMessage");

if (!$iv || !$encryptedSecretKey || !$encryptedMessage) {
    echo json_encode(['error' => 'Dados criptografados não recebidos corretamente.']);
    logSecurityEvent("Dados criptografados não recebidos corretamente.");
    exit();
}

$iv = hex2bin($iv);
$encryptedSecretKey = base64_decode($encryptedSecretKey);
$encryptedMessage = base64_decode($encryptedMessage);

// Carrega a chave privada
$privateKeyPath = realpath(__DIR__ . '/../cert/private.key');
if (!file_exists($privateKeyPath)) {
    logSecurityEvent("Arquivo de chave privada não encontrado.");
    exit();
}

$privateKey = openssl_pkey_get_private('file://' . $privateKeyPath);
if (!$privateKey) {
    logSecurityEvent("Falha ao carregar a chave privada: " . openssl_error_string());
    exit();
}

// Descriptografa a chave secreta
$success = openssl_private_decrypt($encryptedSecretKey, $decryptedSecretKey, $privateKey);
logSecurityEvent('Chave Secreta Descriptografada: ' . $decryptedSecretKey);

if (!$success) {
    logSecurityEvent("Falha ao descriptografar a chave secreta: " . openssl_error_string());
    exit();
}

// Descriptografa a mensagem usando a chave secreta e o IV
$decryptedMessage = openssl_decrypt($encryptedMessage, 'aes-256-cbc', hex2bin($decryptedSecretKey), OPENSSL_RAW_DATA, $iv);
logSecurityEvent('Mensagem Descriptografada: ' . $decryptedMessage);

if ($decryptedMessage === false) {
    logSecurityEvent("Falha ao descriptografar a mensagem: " . openssl_error_string());
    exit();
}

$decodedData = json_decode($decryptedMessage, true);

if (!$decodedData) {
    logSecurityEvent("Falha ao decodificar JSON da mensagem: " . json_last_error_msg());
    exit();
}

$userId = $_SESSION['userId'];
$destinatarioId = $decodedData['destinatarioId'];
$mensagem = $decodedData['mensagem'];

logSecurityEvent("Preparando para inserir a mensagem no banco de dados. remetenteId: $userId, destinatarioId: $destinatarioId, mensagem: $mensagem");

$query = $con->prepare("INSERT INTO mensagens (remetenteId, destinatarioId, mensagem, data_envio) VALUES (?, ?, ?, NOW())");
$query->bind_param("iis", $userId, $destinatarioId, $mensagem);

if ($query->execute()) {
    echo json_encode(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
    logSecurityEvent("Mensagem enviada com sucesso de $userId para $destinatarioId.");
} else {
    echo json_encode(['success' => false, 'error' => $con->error]);
    logSecurityEvent("Erro ao enviar mensagem: " . $con->error);
}

$query->close();
$con->close();
?>
