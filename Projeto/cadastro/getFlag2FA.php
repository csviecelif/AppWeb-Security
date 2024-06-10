<?php
session_start();
require_once '../login/connection.php';

// Verificar se o ID do usuário está na sessão
if (!isset($_SESSION['userId'])) {
    echo json_encode(['error' => 'Usuário não autenticado.']);
    exit();
}

$userId = $_SESSION['userId'];

// Consultar a Flag2FA no banco de dados
$query = "SELECT flag2FA FROM usuarios WHERE userId = ?";
$stmt = $con->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => 'Falha ao preparar a consulta: ' . $con->error]);
    exit();
}
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($flag2FA);
$stmt->fetch();

if ($flag2FA !== null) {
    echo json_encode(['flag2FA' => (int)$flag2FA]);
} else {
    echo json_encode(['error' => 'Flag2FA não encontrada.']);
}

$stmt->close();
$con->close();
?>
