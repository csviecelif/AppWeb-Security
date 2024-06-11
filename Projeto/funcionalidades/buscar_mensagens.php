<?php
header('Content-Type: application/json');

// Caminho correto para autoload.php
require __DIR__ . '/../vendor/autoload.php';

use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\PublicKeyLoader;

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

    $privateKey = file_get_contents($privateKeyPath);
    $rsa = PublicKeyLoader::loadPrivateKey($privateKey);
    $encryptedData = base64_decode($encryptedMessage['data']);
    $iv = hex2bin($encryptedMessage['iv']);
    $encryptedMessageText = base64_decode($encryptedMessage['mensagem']);
    
    logSecurityEvent("IV após decodificação: " . bin2hex($iv));
    logSecurityEvent("Chave secreta após decodificação: " . bin2hex($encryptedData));
    logSecurityEvent("Mensagem criptografada após decodificação: " . bin2hex($encryptedMessageText));
    
    $decryptedSecretKey = $rsa->decrypt($encryptedData);
    if ($decryptedSecretKey !== false) {
        logSecurityEvent("Chave Secreta Descriptografada: " . bin2hex($decryptedSecretKey));
    
        // Certifique-se de que a chave secreta tenha 32 bytes (256 bits) para AES-256-CBC
        if (strlen($decryptedSecretKey) > 32) {
            $decryptedSecretKey = substr($decryptedSecretKey, 0, 32);
        } else {
            $decryptedSecretKey = str_pad($decryptedSecretKey, 32, "\0");
        }
    
        logSecurityEvent("Chave Secreta ajustada: " . bin2hex($decryptedSecretKey));
    
        // Descriptografa a mensagem usando a chave secreta ajustada e o IV
        $aes = new AES('cbc');
        $aes->setIV($iv);
        $aes->setKey($decryptedSecretKey);

        $decryptedMessage = $aes->decrypt($encryptedMessageText);
        if ($decryptedMessage === false) {
            logSecurityEvent("Falha ao descriptografar a mensagem");
        } else {
            logSecurityEvent("Mensagem Descriptografada: " . $decryptedMessage);

            $mensagens[] = [
                'remetenteId' => $remetenteId,
                'remetenteNome' => 'Nome do Remetente', // Buscar o nome do remetente se necessário
                'mensagem' => $decryptedMessage,
                'data_envio' => $dataEnvio
            ];
        }
    } else {
        logSecurityEvent("Falha ao descriptografar a chave secreta");
    }
}

logSecurityEvent("Mensagens decodificadas: " . json_encode($mensagens));

echo json_encode($mensagens);
$query->close();
$con->close();
?>
