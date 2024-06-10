<?php
session_start();
use OTPHP\TOTP;
require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

// Receber os dados enviados pelo cliente
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['token'])) {
    echo json_encode(['error' => 'Dados incompletos.']);
    exit;
}

$email = $data['email'];
$token = $data['token'];

include '../login/connection.php';

if ($con->connect_error) {
    die(json_encode(['error' => 'Falha na conexão com o banco de dados: ' . $con->connect_error]));
}

// Verificar se o e-mail existe no banco de dados
$sql = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['error' => 'E-mail não encontrado.']);
    exit;
}

$user = $result->fetch_assoc();

// Verificar o token
if ($user['token'] == $token) {
    // Atualizar o status do e-mail para validado
    $sql = "UPDATE usuarios SET email_validado = 1 WHERE email = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $email);
    if ($stmt->execute()) {
        // Criar sessão para o usuário
        $_SESSION['userId'] = $user['userId'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nomeCompleto'] = $user['nomeCompleto'];
        $_SESSION['flag2fa'] = $user['flag2fa'];

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Erro ao atualizar status de e-mail.']);
    }
} else {
    echo json_encode(['error' => 'Token inválido.']);
}

$con->close();
?>
