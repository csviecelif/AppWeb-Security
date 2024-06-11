<?php
header('Content-Type: application/json');

// Função para logar mensagens de segurança
function logSecurityEvent($message) {
    $logFile = 'security.log';
    $date = new DateTime();
    file_put_contents($logFile, $date->format('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Conexão com o banco de dados
include '../login/connection.php';
session_start();

if (!isset($_SESSION['userId'])) {
    echo json_encode([]);
    logSecurityEvent("Usuário não autenticado");
    exit();
}

$userId = $_SESSION['userId'];

// Recupera as mensagens do banco de dados
$query = $con->prepare("SELECT remetenteId, mensagem, data_envio FROM mensagens WHERE destinatarioId = ?");
$query->bind_param("i", $userId);
$query->execute();
$result = $query->get_result();

$mensagens = [];
while ($row = $result->fetch_assoc()) {
    $remetenteId = $row['remetenteId'];
    $encryptedMessageJson = $row['mensagem'];
    $dataEnvio = $row['data_envio'];

    logSecurityEvent("Processando mensagem do remetenteId: $remetenteId");

    // Verifica se a mensagem está no formato JSON correto
    logSecurityEvent("Mensagem Criptografada JSON: " . $encryptedMessageJson);
    $encryptedMessage = json_decode($encryptedMessageJson, true);
    if ($encryptedMessage === null) {
        logSecurityEvent("Falha ao decodificar JSON da mensagem: " . json_last_error_msg());
        continue;
    }

    // Carrega a chave privada
    $privateKeyPath = realpath(__DIR__ . '/../cert/private.key');
    if (!file_exists($privateKeyPath)) {
        logSecurityEvent("Arquivo de chave privada não encontrado.");
        continue;
    }

    $privateKey = openssl_pkey_get_private('file://' . $privateKeyPath);
    if (!$privateKey) {
        logSecurityEvent("Falha ao carregar a chave privada: " . openssl_error_string());
        continue;
    }

    // Descriptografa a chave secreta
    $encryptedData = base64_decode($encryptedMessage['data']);
    $iv = hex2bin($encryptedMessage['iv']);
    $encryptedMessageText = base64_decode($encryptedMessage['mensagem']);
    
    $success = openssl_private_decrypt($encryptedData, $decryptedSecretKey, $privateKey);
    if (!$success) {
        logSecurityEvent("Falha ao descriptografar a chave secreta: " . openssl_error_string());
        continue;
    }

    logSecurityEvent("Chave Secreta Descriptografada: " . $decryptedSecretKey);

    // Descriptografa a mensagem usando a chave secreta e o IV
    $decryptedMessage = openssl_decrypt($encryptedMessageText, 'aes-256-cbc', $decryptedSecretKey, OPENSSL_RAW_DATA, $iv);
    if ($decryptedMessage === false) {
        logSecurityEvent("Falha ao descriptografar a mensagem: " . openssl_error_string());
        continue;
    }

    logSecurityEvent("Mensagem Descriptografada: " . $decryptedMessage);

    $mensagens[] = [
        'remetenteId' => $remetenteId,
        'remetenteNome' => 'Nome do Remetente', // Buscar o nome do remetente se necessário
        'mensagem' => $decryptedMessage,
        'data_envio' => $dataEnvio
    ];
}

logSecurityEvent("Mensagens decodificadas: " . json_encode($mensagens));

echo json_encode($mensagens);
$query->close();
$con->close();

?>
