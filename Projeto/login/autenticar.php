<?php
session_start();
require_once '../login/connection.php';

define('SESSION_EXPIRATION_TIME', 9000);

function isSessionExpired() {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_EXPIRATION_TIME) {
            return true;
        }
    }
    return false;
}

if (isSessionExpired()) {
    session_unset();
    session_destroy();
    header("Location: ../login/index.html");
    exit;
} else {
    $_SESSION['login_time'] = time();
}

$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados || !isset($dados['email']) || !isset($dados['senha']) || !isset($dados['iv']) || !isset($dados['secretKey'])) {
    echo json_encode(['error' => 'Dados criptografados não recebidos corretamente.']);
    exit();
}

$email = $dados['email'];
$senha = $dados['senha'];
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

$decryptedPassword = openssl_decrypt(base64_decode($senha), 'aes-256-cbc', hex2bin($decryptedSecretKey), OPENSSL_RAW_DATA, hex2bin($iv));
if ($decryptedPassword === false) {
    echo json_encode(['error' => 'Falha ao descriptografar a senha: ' . openssl_error_string()]);
    exit();
}

$senhaHash = hash('sha256', $decryptedPassword);

$sql = "SELECT * FROM usuarios WHERE email = ? AND senha = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('ss', $email, $senhaHash);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['userId'] = $user['userId'];
    $_SESSION['login_time'] = time();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Senha incorreta.']);
}

$stmt->close();
$con->close();
?>
