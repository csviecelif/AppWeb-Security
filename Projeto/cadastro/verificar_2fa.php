<?php
require_once '../vendor/autoload.php'; // Garanta que o caminho está correto.
require_once '../login/connection.php'; // Conexão com banco de dados

session_start();

$ga = new PHPGangsta_GoogleAuthenticator();

$userId = $_SESSION['userId'];

// Prepara a consulta para evitar injeções de SQL
$query = $con->prepare("SELECT twoef FROM usuarios WHERE userId = ?");
$query->bind_param("i", $userId);  // 'i' indica que a variável é do tipo inteiro
$query->execute();
$result = $query->get_result();

if ($row = $result->fetch_assoc()) {
    $secret = $row['twoef'];  // Extrai a chave secreta do resultado
} else {
    die('Chave secreta não encontrada para o usuário.');
}

$query->close();

$token = $_POST['token'];  // O token inserido pelo usuário no formulário

// Verifica o código contra a chave secreta
$checkResult = $ga->verifyCode($secret, $token, 2); // Janela de 2 * 30 segundos para códigos TOTP

if ($checkResult) {
    echo 'Código Válido!';
    // Adicionar a lógica após a validação bem-sucedida, como redirecionar para a página principal
} else {
    echo 'Código Inválido!';
    // Opcionalmente, enviar o usuário de volta ao formulário de login ou mostrar uma mensagem de erro
}

// Fecha a conexão com o banco de dados
$con->close();
