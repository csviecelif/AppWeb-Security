<?php
session_start();
require_once '../login/connection.php';

$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados || !isset($dados['userId']) || !isset($dados['iv']) || !isset($dados['secretKey'])) {
    echo json_encode(['error' => 'Dados criptografados não recebidos corretamente.']);
    exit();
}

$iv = $dados['iv'];
$secretKey = base64_decode($dados['secretKey']);
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

$success = openssl_private_decrypt($secretKey, $decryptedSecretKey, $privateKey);
if (!$success) {
    echo json_encode(['error' => 'Falha ao descriptografar a chave secreta: ' . openssl_error_string()]);
    exit();
}

$decryptedUserId = openssl_decrypt(base64_decode($dados['userId']), 'aes-256-cbc', hex2bin($decryptedSecretKey), OPENSSL_RAW_DATA, hex2bin($iv));
if ($decryptedUserId === false) {
    echo json_encode(['error' => 'Falha ao descriptografar o userId: ' . openssl_error_string()]);
    exit();
}

$userId = $decryptedUserId;

$sqlBuscar = "SELECT * FROM buscar_emprego WHERE userId = ?";
$stmtBuscar = $con->prepare($sqlBuscar);
$stmtBuscar->bind_param("i", $userId);
$stmtBuscar->execute();
$resultBuscar = $stmtBuscar->get_result();
$buscarEmprego = $resultBuscar->fetch_assoc();

$sqlOferecer = "SELECT * FROM oferecer_emprego WHERE userId = ?";
$stmtOferecer = $con->prepare($sqlOferecer);
$stmtOferecer->bind_param("i", $userId);
$stmtOferecer->execute();
$resultOferecer = $stmtOferecer->get_result();
$oferecerEmprego = $resultOferecer->fetch_assoc();

if ($buscarEmprego) {
    echo json_encode(['buscarEmprego' => true, 'dados' => $buscarEmprego]);
} elseif ($oferecerEmprego) {
    echo json_encode(['oferecerEmprego' => true, 'dados' => $oferecerEmprego]);
} else {
    // Verificar se o usuário existe na tabela usuários
    $sqlUsuario = "SELECT * FROM usuarios WHERE userId = ?";
    $stmtUsuario = $con->prepare($sqlUsuario);
    $stmtUsuario->bind_param("i", $userId);
    $stmtUsuario->execute();
    $resultUsuario = $stmtUsuario->get_result();
    $usuario = $resultUsuario->fetch_assoc();

    if ($usuario) {
        echo json_encode(['usuario' => true, 'dados' => $usuario]);
    } else {
        echo json_encode(['error' => 'Nenhum cadastro encontrado.']);
    }

    $stmtUsuario->close();
}

$stmtBuscar->close();
$stmtOferecer->close();
$con->close();
?>
