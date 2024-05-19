<?php
session_start();
require '../login/connection.php';

if (!isset($_SESSION['userId'])) {
    header('Location: ../login/index.html');
    exit();
}

$userId = $_SESSION['userId'];

$query = "INSERT INTO oferecer_emprego (userId, cargo, pais_empresa, setor, descricao_vaga, requisitos_vaga, salario, beneficios, endereco_empresa, website_empresa, redes_sociais_empresa, documento_identidade, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $con->prepare($query);
$stmt->bind_param(
    'issssssssssss', 
    $userId, 
    $_POST['cargo'], 
    $_POST['pais_empresa'], 
    $_POST['setor'], 
    $_POST['descricao_vaga'], 
    $_POST['requisitos_vaga'], 
    $_POST['salario'], 
    $_POST['beneficios'], 
    $_POST['endereco_empresa'], 
    $_POST['website_empresa'], 
    $_POST['redes_sociais_empresa'], 
    $_POST['documento_identidade'], 
    $_POST['bio']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'userId' => $userId, 'cargo' => $_POST['cargo'], 'pais_empresa' => $_POST['pais_empresa'], 'setor' => $_POST['setor']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar emprego']);
}
?>
