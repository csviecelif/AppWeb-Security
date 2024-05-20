<?php

require_once 'connection.php'; // Inclui o arquivo de conexão
require __DIR__ . '/../vendor/autoload.php';
use OTPHP\TOTP;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['senha'], $_POST['token'])) {
    $novaSenha = $_POST['senha'];
    $novotoken = $_POST['token'];

    // Preparar a consulta para confirmar se o token corresponde
    $stmt = $con->prepare("SELECT email FROM usuarios WHERE token = ?");
    $stmt->bind_param("s", $novotoken);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $otp = TOTP::create();
        $novotwoef = $otp->getSecret();
        $novaflag = 0;

        $stmt = $con->prepare("UPDATE usuarios SET senha = ?, twoef = ?, flag2fa = ? WHERE email = ?");
        $stmt->bind_param("ssss", $novaSenha, $novotwoef, $novaflag, $email);
        
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Senha atualizada com sucesso!";
            header("Location: ../login/logado.html");
            // Redirecionar para a página de configuração do 2FA ou forçar logout para relogin
        } else {
            echo "Erro ao atualizar senha.";
        }
        exit;
    } 
    else {
        echo "Não foi possível realizar a troca da senha";
    }
    // Fecha a conexão com o banco de dados
    $stmt->close();
    $conn->close();
}
?>
