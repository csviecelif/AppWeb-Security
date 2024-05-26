<?php
require __DIR__ . '/../vendor/autoload.php'; // Certifique-se de que este caminho está correto
use Dotenv\Dotenv;
use OTPHP\TOTP;
require_once '../login/connection.php';
session_start();

// Ajuste o caminho para o diretório onde o .env está localizado
$dotenv = Dotenv::createImmutable(__DIR__ . '/../Login');
$dotenv->load();

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

$response = array();

if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];

    if (isset($_POST['OTP'])) {
        $userInput = $_POST['OTP'];
        $query = "SELECT twoef FROM usuarios WHERE userId = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $row = $result->fetch_assoc();
            $secret = $row['twoef'];

            $otp = TOTP::createFromSecret($secret);
            if ($otp->verify($userInput)) {
                $updateQuery = "UPDATE usuarios SET flag2fa = 1 WHERE userId = ?";
                $updateStmt = $con->prepare($updateQuery);
                $updateStmt->bind_param("i", $userId);
                if ($updateStmt->execute()) {
                    $response['success'] = true;
                    $_SESSION["flag2fa"] = 1;
                } else {
                    $response['success'] = false;
                    $_SESSION["flag2fa"] = 0;
                    $response['error'] = 'Erro ao atualizar flag2fa no banco de dados.';
                }
            } else {
                $response['success'] = false;
                $_SESSION["flag2fa"] = 0;
                $response['error'] = 'OTP inválido.';
            }
        } else {
            $response['success'] = false;
            $_SESSION["flag2fa"] = 0;
            $response['error'] = 'Erro ao consultar o flag2fa no banco de dados.';
        }
    } else {
        $response['success'] = false;
        $response['error'] = 'OTP não fornecido.';
    }
} else {
    $response['success'] = false;
    $response['error'] = 'Usuário não autenticado.';
}

echo json_encode($response);
?>
