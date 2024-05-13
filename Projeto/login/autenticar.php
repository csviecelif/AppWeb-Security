<?php
session_start();
require 'connection.php';  // Certifique-se de que o caminho está correto e que 'connection.php' configura $con

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'], $_POST['senha'])) {
    $email = $_POST['email'];
    $senhaHashCliente = $_POST['senha']; // Hash SHA-256 da senha recebido do cliente

    $stmt = $con->prepare("SELECT senha, flag2fa FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verificar senha comparando os hashes diretamente
        if ($senhaHashCliente === $row['senha']) {
            $_SESSION['email'] = $email;  // Armazena o email na sessão para uso posterior
            $_SESSION['userId'] = $row['userId'];  // Armazena o ID do usuário na sessão
            // Verificar status da flag 2FA
            if ($row['flag2fa'] == 0) {
                // Redirecionar para a página de configuração do 2FA
                header("Location: ../cadastro/2fa.html");
                exit;
            } else {
                // Se 2FA está ativa, redirecionar para a página que solicita o código 2FA
                $_SESSION['email'] = $email;  // Armazena o email na sessão para uso posterior
                header("Location: ../cadastro/2fa.html");  // Página que solicita o código 2FA
                exit;
            }
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }
    $stmt->close();
}
$con->close();
?>
