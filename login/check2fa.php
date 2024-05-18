<?php
use OTPHP\TOTP; //import da biblioteca OTPHP
require '..\vendor\autoload.php';
require_once 'connection.php'; // Inclui o arquivo de conexão
session_start();

// Tempo máximo de sessão em segundos (1 hora)
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
    session_unset(); // Remove todas as variáveis de sessão
    session_destroy(); // Destroi a sessão
    header("Location: ../login/index.html"); // Redireciona para a página de login
    exit;
} else {
    // Atualiza o timestamp da sessão
    $_SESSION['login_time'] = time();
}
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['OTP']) && isset($_SESSION['userId'])) {
    $otp = $_POST['OTP'];
    $userId = $_SESSION['userId'];

    // Verifique o OTP com base no userId do usuário
    $stmt = $con->prepare("SELECT twoef FROM usuarios WHERE email = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $secret = $row['twoef'];
        $totp = TOTP::create($secret);
        
        // Verifique o OTP usando a biblioteca OTPHP
        if ($totp->verify($otp)) {
            echo json_encode(['success' => true]);
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
