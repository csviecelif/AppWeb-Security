<?php
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $con->prepare("SELECT userId, senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($userId, $hashedPassword);
    
    if ($stmt->fetch() && hash_equals($hashedPassword, $senha)) {
        $_SESSION['userId'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['login_time'] = time();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'E-mail ou senha incorretos!']);
    }

    $stmt->close();
    $con->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Método de requisição inválido!']);
}
?>
