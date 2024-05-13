<?php
session_start();

use OTPHP\TOTP; //import da biblioteca OTPHP

require '..\vendor\autoload.php';
require_once '../login/connection.php'; // conexão com o banco de dados

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['OTP']) && isset($_SESSION['userId'])) {
    $otp = $_POST['OTP'];
    $userId = $_SESSION['userId'];

    // Verifique o OTP com base no userId do usuário
    $stmt = $con->prepare("SELECT twoef FROM usuarios WHERE userId = ?");
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
