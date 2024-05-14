<?php
require 'connection.php';  // Certifique-se de que o caminho está correto e que 'connection.php' configura $con
session_start();

define('SESSION_EXPIRATION_TIME', 900);

function isSessionExpired() {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_EXPIRATION_TIME) {
            return true;
        }
    }
    return false;
}

if (isSessionExpired()) {
    session_unset(); // Remove todas as variáveis de sessão
    session_destroy(); // Destroi a sessão
    header("Location: ../login/index.html"); // Redireciona para a página de login
    exit;
} else {
    // Atualiza o timestamp da sessão
    $_SESSION['login_time'] = time();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'], $_POST['senha'])) {
    $email = $_POST['email'];
    $senhaHashCliente = $_POST['senha']; // Hash SHA-256 da senha recebido do cliente

    // Preparar a consulta SQL
    $stmt = $con->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verificar senha comparando os hashes diretamente
        if ($senhaHashCliente === $row['senha']) {
            $_SESSION['email'] = $email; // Definir a variável de sessão 'email'
            header("Location: autenticar.html");  // Página que solicita o código 2FA
            exit;
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }
    $stmt->close();
    $con->close();
}
?>
