<?php
session_start();

require '../login/connection.php'; // Certifique-se de que o caminho está correto

$response = array('success' => false, 'message' => '');

ob_start(); // Captura qualquer saída inesperada

if (!isset($_SESSION['userId'])) {
    $response['message'] = 'Usuário não autenticado';
    echo json_encode($response);
    exit();
}

$userId = $_SESSION['userId'];

$sql = "SELECT bio, foto, cv, certificados FROM usuarios WHERE userId = ?";
if ($stmt = $con->prepare($sql)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($bio, $foto, $cv, $certificados);
    if ($stmt->fetch()) {
        $response['success'] = true;
        $response['bio'] = $bio;
        $response['foto'] = $foto;
        $response['cv'] = $cv;
        $response['certificados'] = $certificados;
    } else {
        $response['message'] = 'Perfil não encontrado';
    }
    $stmt->close();
} else {
    $response['message'] = 'Erro ao preparar a consulta: ' . $con->error;
}

$con->close();

ob_end_clean(); // Limpa qualquer saída inesperada
echo json_encode($response);
?>
