<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require '../login/connection.php';

function gerarTokenNumerico($tamanho = 6) {
    return mt_rand(pow(10, $tamanho - 1), pow(10, $tamanho) - 1);
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['email'])) {
        $email = $data['email'];
        $stmt = $con->prepare("SELECT userId FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $novoToken = gerarTokenNumerico();
            $stmt = $con->prepare("UPDATE usuarios SET token = ? WHERE email = ?");
            $stmt->bind_param("ss", $novoToken, $email);
            if ($stmt->execute()) {
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->SMTPDebug = 0;
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
                    echo json_encode(['success' => true, 'message' => 'E-mail enviado com sucesso! Verifique sua caixa de entrada.']);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Erro ao enviar o e-mail: ' . $mail->ErrorInfo]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o registro: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'E-mail não encontrado']);
        }
        $stmt->close();
        $con->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'E-mail não fornecido']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitação inválido']);
}
?>
