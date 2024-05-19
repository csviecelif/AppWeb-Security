<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require '../login/connection.php';

function gerarTokenNumerico($tamanho = 6) {
    return mt_rand(pow(10, $tamanho - 1), pow(10, $tamanho) - 1);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    $stmt = $con->prepare("SELECT userId FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $novoToken = gerarTokenNumerico();  
        $stmt = $con->prepare("UPDATE usuarios SET token = ? WHERE email = ?");
        $stmt->bind_param("ssis", $novoToken, $email);
        if ($stmt->execute()) {
            echo "Atualização realizada com sucesso!";
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPDebug = 2;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 465;
            $mail->Username = 'globaloportuna@gmail.com';
            $mail->Password = 'qgvm brod pjrl wflz';
            $mail->setFrom('globaloportuna@gmail.com', 'Global Oportuna');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Token de Recuperação de senha';
            $mail->Body = 'Seu token de Recuperação de senha é: ' . $novoToken;
            $mail->send();
            echo 'E-mail enviado com sucesso! Verifique sua caixa de entrada.';
            header("Location: redefinir.html");
        } else {
            echo "Erro ao atualizar o registro: " . $stmt->error;
        }
        $stmt->close();
        $con->close();
    } else {
        echo "E-mail não encontrado";
    }
}
?>
