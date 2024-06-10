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
if (!$data || !isset($data['data']) || !isset($data['hmac'])) {
    echo json_encode(['error' => 'Dados criptografados não recebidos corretamente.']);
    logSecurityEvent("Dados criptografados não recebidos corretamente.");
    exit();
}

$encryptedData = base64_decode($data['data']);
if (!$encryptedData) {
    echo json_encode(['error' => 'Erro ao decodificar os dados criptografados.']);
    logSecurityEvent("Erro ao decodificar os dados criptografados.");
    exit();
}

// Carrega a chave privada
$privateKeyPath = realpath('private.key');
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

// Descriptografa os dados
$success = openssl_private_decrypt($encryptedData, $decryptedData, $privateKey);
if (!$success) {
    echo json_encode(['error' => 'Falha ao descriptografar os dados: ' . openssl_error_string()]);
    logSecurityEvent("Falha ao descriptografar os dados: " . openssl_error_string());
    exit();
}

// Verifica o HMAC para garantir a integridade
$calculatedHmac = hash_hmac('sha256', base64_encode($encryptedData), $decryptedData);
if ($calculatedHmac !== $data['hmac']) {
    echo json_encode(['error' => 'Falha na verificação de integridade.']);
    logSecurityEvent("Falha na verificação de integridade.");
    exit();
}

echo json_encode(['data' => $decryptedData]);
logSecurityEvent("Dados descriptografados com sucesso.");
?>