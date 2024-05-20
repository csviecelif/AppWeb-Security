<?php
header('Content-Type: application/json');
include '../login/connection.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['userId'];
$mensagem = $data['mensagem'];
$destinatarioId = $data['destinatarioId'];

$query = $con->prepare("INSERT INTO mensagens (remetenteId, destinatarioId, mensagem, data_envio) VALUES (?, ?, ?, NOW())");
$query->bind_param("iis", $userId, $destinatarioId, $mensagem);

if ($query->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $con->error]);
}

$query->close();
$con->close();
?>
