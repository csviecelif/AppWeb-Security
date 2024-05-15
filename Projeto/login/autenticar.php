<?php
session_start();
require 'connection.php';  // Certifique-se de que o caminho está correto e que 'connection.php' configura $con

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
            $_SESSION['userId'] = $email;
            $_SESSION['login_time'] = time(); // Configura o tempo de login
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
