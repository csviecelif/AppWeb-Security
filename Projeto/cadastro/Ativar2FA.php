<?php
use OTPHP\TOTP;
require '..\vendor\autoload.php';
require_once '../login/connection.php';
session_start();
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
        $query = "SELECT twoef FROM usuarios WHERE userId = $userId";
        $result = mysqli_query($con, $query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $secret = $row['twoef'];

            $otp = TOTP::createFromSecret($secret);
            if ($otp->verify($userInput)) {
                $updateQuery = "UPDATE usuarios SET flag2fa = 1 WHERE userId = $userId";
                $updateResult = mysqli_query($con, $updateQuery);
                if ($updateResult) {
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
        $_SESSION["flag2fa"] = 0;
        $response['error'] = 'Nenhum OTP fornecido.';
    }
} else {
    $response['success'] = false;
    $_SESSION["flag2fa"] = 0;
    $response['error'] = 'Nenhuma sessão ativa.';
}

echo json_encode($response);
?>
