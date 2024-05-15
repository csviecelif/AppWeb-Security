<?php
session_start();

$response = array();

if (!isset($_SESSION['userId'])) {
    $response = "False";
    $_SESSION['mensagem'] = "Você deve estar logado para acessar esta página.";
} else {
    $response = "True";
}

header('Content-Type: application/json');
echo json_encode($response);
exit();  // Garante que nenhum outro conteúdo seja enviado
?>
