<?php
session_start();
require 'connection.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'], $_POST['senha'])) {
    $email = $_POST['email'];
    $senhaHashCliente = $_POST['senha'];

    $stmt = $con->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($senhaHashCliente === $row['senha']) {
            $_SESSION['userId'] = $email;
            $_SESSION['login_time'] = time();
            header("Location: autenticar.html");
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
