<?php
header('Content-Type: application/json');
session_start();
require_once 'connection.php';

$userId = $_SESSION['userId'];

$stmt = $con->prepare("SELECT twoef FROM usuarios WHERE userId = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($twoef);

if ($stmt->fetch()) {
    echo json_encode(['secret' => $twoef]);
} else {
    echo json_encode(['error' => 'Usuário não encontrado ou erro ao recuperar o segredo.']);
}

$stmt->close();
$con->close();
?>
