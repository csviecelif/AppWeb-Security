<?php
// validar_token.php
require_once '../login/connection.php';

$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados || !isset($dados['email']) || !isset($dados['token'])) {
    echo json_encode(['error' => 'Dados criptografados não recebidos corretamente.']);
    exit();
}

$email = $dados['email'];
$encryptedToken = $dados['token'];

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

$success = openssl_private_decrypt(base64_decode($encryptedToken), $decryptedToken, $privateKey);
if (!$success) {
    echo json_encode(['error' => 'Falha ao descriptografar o token: ' . openssl_error_string()]);
    exit();
}

$sql = "SELECT * FROM usuarios WHERE email = ? AND token = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('ss', $email, $decryptedToken);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    // Marcar o email como validado
    $updateSql = "UPDATE usuarios SET email_validado = 1 WHERE email = ?";
    $updateStmt = $con->prepare($updateSql);
    $updateStmt->bind_param('s', $email);
    $updateStmt->execute();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Token inválido.']);
}

$stmt->close();
$con->close();
