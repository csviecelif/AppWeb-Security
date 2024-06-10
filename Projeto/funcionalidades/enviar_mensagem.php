<?php
header('Content-Type: application/json');

// Função para logar mensagens de segurança
function logSecurityEvent($message) {
    $logFile = 'security.log';
    $date = new DateTime();
    file_put_contents($logFile, $date->format('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Recebe os dados criptografados do cliente
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['data']) || !isset($data['iv']) || !isset($data['mensagem'])) {
    echo json_encode(['error' => 'Dados criptografados não recebidos corretamente.']);
    logSecurityEvent("Dados criptografados não recebidos corretamente.");
    exit();
}

$encryptedData = base64_decode($data['data']);
$iv = $data['iv'];  // Aqui estamos assumindo que o IV já está em formato hexadecimal
$encryptedMessage = $data['mensagem'];
if (!$encryptedData || !$iv) {
    echo json_encode(['error' => 'Erro ao decodificar os dados criptografados.']);
    logSecurityEvent("Erro ao decodificar os dados criptografados.");
    exit();
}

// Carrega a chave privada
$privateKeyPath = realpath(__DIR__ . '/../cert/private.key');
if (!file_exists($privateKeyPath)) {
    echo json_encode(['error' => 'Arquivo de chave privada não encontrado.']);
    logSecurityEvent("Arquivo de chave privada não encontrado.");
    exit();
}

$privateKey = openssl_pkey_get_private('file://' . $privateKeyPath);
if (!$privateKey) {
    echo json_encode(['error' => 'Falha ao carregar a chave privada: ' . openssl_error_string()]);
    logSecurityEvent("Falha ao carregar a chave privada: " . openssl_error_string());
    exit();
}

// Descriptografa a chave secreta
$success = openssl_private_decrypt($encryptedData, $decryptedSecretKey, $privateKey);
if (!$success) {
    echo json_encode(['error' => 'Falha ao descriptografar a chave secreta: ' . openssl_error_string()]);
    logSecurityEvent("Falha ao descriptografar a chave secreta: " . openssl_error_string());
    exit();
}

// Descriptografa a mensagem usando a chave secreta e o IV
$decryptedMessage = openssl_decrypt(base64_decode($encryptedMessage), 'aes-256-cbc', hex2bin($decryptedSecretKey), OPENSSL_RAW_DATA, hex2bin($iv));
if ($decryptedMessage === false) {
    echo json_encode(['error' => 'Falha ao descriptografar a mensagem: ' . openssl_error_string()]);
    logSecurityEvent("Falha ao descriptografar a mensagem: " . openssl_error_string());
    exit();
}

// Prepara a mensagem para armazenar no banco de dados
$jsonMessage = json_encode([
    'data' => $data['data'],
    'iv' => $data['iv'],
    'mensagem' => $data['mensagem']
]);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Erro ao codificar a mensagem em JSON.']);
    logSecurityEvent("Erro ao codificar a mensagem em JSON: " . json_last_error_msg());
    exit();
}

include '../login/connection.php';
session_start();
$userId = $_SESSION['userId'];
$destinatarioId = $data['destinatarioId'];

// Salva a mensagem no banco de dados
$query = $con->prepare("INSERT INTO mensagens (remetenteId, destinatarioId, mensagem, data_envio) VALUES (?, ?, ?, NOW())");
$query->bind_param("iis", $userId, $destinatarioId, $jsonMessage);

if ($query->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $con->error]);
}

$query->close();
$con->close();
?>
