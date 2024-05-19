<?php
session_start();
require '../login/connection.php';

if (!isset($_SESSION['userId'])) {
    header('Location: ../login/index.html');
    exit();
}

$userId = $_SESSION['userId'];

$query = "INSERT INTO buscar_emprego (userId, email, telefone, bio, formacao_academica, idiomas_falados, data_nascimento, area_interesse, expectativa_salarial, pais_origem, experiencia_profissional, habilidades_competencias) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $con->prepare($query);
$stmt->bind_param(
    'isssssssssss', 
    $userId, 
    $_POST['email'], 
    $_POST['telefone'], 
    $_POST['bio'], 
    $_POST['formacao_academica'], 
    $_POST['idiomas_falados'], 
    $_POST['data_nascimento'], 
    $_POST['area_interesse'], 
    $_POST['expectativa_salarial'], 
    $_POST['pais_origem'], 
    $_POST['experiencia_profissional'], 
    $_POST['habilidades_competencias']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'userId' => $userId, 'email' => $_POST['email'], 'telefone' => $_POST['telefone'], 'bio' => $_POST['bio']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar perfil']);
}
?>
