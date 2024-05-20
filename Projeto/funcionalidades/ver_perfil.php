<?php
header('Content-Type: application/json');
require '../login/connection.php';

$userId = $_GET['userId'];

if (!$userId) {
    echo json_encode(['success' => false, 'error' => 'ID de usuário não fornecido']);
    exit();
}

$query = "SELECT nomeCompleto, email, telefone, formacao_academica, experiencia_profissional, habilidades, idiomas FROM usuarios WHERE userId = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $perfil = $result->fetch_assoc();
    echo json_encode(['success' => true, 'perfil' => $perfil]);
} else {
    echo json_encode(['success' => false, 'error' => 'Perfil não encontrado']);
}

$stmt->close();
$con->close();
?>
