<?php
session_start();
use OTPHP\TOTP;
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('SESSION_EXPIRATION_TIME', 9000);

function isSessionExpired() {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_EXPIRATION_TIME) {
            return true;
        }
    }
    return false;
}

if (isSessionExpired()) {
    session_unset();
    session_destroy();
    header("Location: ../login/index.html");
    exit;
} else {
    $_SESSION['login_time'] = time();
}

function gerarTokenNumerico($tamanho = 6) {
    return mt_rand(pow(10, $tamanho - 1), pow(10, $tamanho) - 1);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomeCompleto = $_POST["nomeCompleto"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $senhaHash = hash('sha256', $senha);
    $cpf = $_POST["cpf"];
    $telefone = $_POST["telefone"];

    $servername = "127.0.0.1";
    $username = "root";
    $password = "PUC@1234";
    $dbname = "normal";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }

    $token = gerarTokenNumerico();

    $sql_check_email = "SELECT * FROM usuarios WHERE email = ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $result_check_email = $stmt_check_email->get_result();

    if ($result_check_email->num_rows > 0) {
        echo "Este email já está em uso. Por favor, escolha outro.";
        $stmt_check_email->close();
        $conn->close();
        exit;
    }

    $sql_check_cpf = "SELECT * FROM usuarios WHERE cpf = ?";
    $stmt_check_cpf = $conn->prepare($sql_check_cpf);
    $stmt_check_cpf->bind_param("s", $cpf);
    $stmt_check_cpf->execute();
    $result_check_cpf = $stmt_check_cpf->get_result();

    if ($result_check_cpf->num_rows > 0) {
        echo "Este CPF já está em uso. Recupere a senha se for necessário";
        $stmt_check_cpf->close();
        $conn->close();
        exit;
    }

    $sql_check_name = "SELECT *FROM usuarios WHERE nomeCompleto = ?";
    $stmt_check_name = $conn->prepare($sql_check_name);
    $stmt_check_name->bind_param("s", $nomeCompleto);
    $stmt_check_name->execute();
    $result_check_name = $stmt_check_name->get_result();

    if ($result_check_name->num_rows > 0) {
        echo "Este nome já está em uso. Utilize outro nome";
        $stmt_check_name->close();
        $conn->close();
        exit;
    }

    $otp = TOTP::generate();
    $twoef = $otp->getSecret();

    $sql_insert_user = "INSERT INTO usuarios (nomeCompleto, email, senha, cpf, telefone, token, twoef) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert_user = $conn->prepare($sql_insert_user);
    $stmt_insert_user->bind_param("sssssss", $nomeCompleto, $email, $senhaHash, $cpf, $telefone, $token, $twoef);

    $query = "SELECT * FROM usuarios WHERE email='$email'";

    if ($stmt_insert_user->execute()) {
        try {
            $userId = $conn->insert_id;
            $_SESSION['userId'] = $userId;

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
            $mail->addAddress($email, '');
            $mail->isHTML(true);
            $mail->Subject = 'Token de Confirmação de Cadastro';
            $mail->Body = 'Seu token de confirmação é: ' . $token;

            $mail->send();
            echo 'E-mail enviado com sucesso! Verifique sua caixa de entrada.';

            header("Location: validar_token.php?email=" . urlencode($email));
            exit;
        } catch (Exception $e) {
            echo "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
        }
    } else {
        echo "Erro ao cadastrar o usuário: " . $stmt_insert_user->error;
    }

    $stmt_insert_user->close();
    $conn->close();
}
?>
