User
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
    $encryptedMessage = json_decode($encryptedMessageJson, true);
    if ($encryptedMessage === null) {
        logSecurityEvent("Falha ao decodificar JSON da mensagem: " . json_last_error_msg());
        continue;
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

    // Verifica se todos os campos necessários estão presentes
    if (!isset($encryptedMessage['data']) || !isset($encryptedMessage['iv']) || !isset($encryptedMessage['mensagem'])) {
        logSecurityEvent("Dados criptografados incompletos na mensagem.");
        continue;
    }

    // Descriptografa a chave secreta
    $encryptedData = base64_decode($encryptedMessage['data']);
    $iv = $encryptedMessage['iv'];  // Aqui estamos assumindo que o IV já está em formato hexadecimal
    $encryptedMessageText = $encryptedMessage['mensagem'];
    
    $success = openssl_private_decrypt($encryptedData, $decryptedSecretKey, $privateKey);
    if (!$success) {
        logSecurityEvent("Falha ao descriptografar a chave secreta: " . openssl_error_string());
        continue;
    }

    // Descriptografa a mensagem usando a chave secreta e o IV
    $decryptedMessage = openssl_decrypt(base64_decode($encryptedMessageText), 'aes-256-cbc', hex2bin($decryptedSecretKey), OPENSSL_RAW_DATA, hex2bin($iv));
    if ($decryptedMessage === false) {
        logSecurityEvent("Falha ao descriptografar a mensagem: " . openssl_error_string());
        continue;
    }

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