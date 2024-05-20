<?php
use OTPHP\TOTP;
require '../vendor/autoload.php';
require_once 'connection.php';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['OTP']) && isset($_SESSION['email'])) {
    $otp = $_POST['OTP'];
    $email = $_SESSION['email'];
    
    $stmt = $con->prepare("SELECT userId, twoef FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $secret = $row['twoef'];
        $totp = TOTP::create($secret);
        
        if ($totp->verify($otp)) {
            $_SESSION['userId'] = $row['userId'];
            echo json_encode(['success' => true, 'userId' => $row['userId']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'OTP inválido!']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Usuário não encontrado!']);
    }
    
    $stmt->close();
    $con->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Método de requisição inválido ou OTP não fornecido!']);
}
?>
