<?php
session_start();

require_once '../login/connection.php';

$response = array();

if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];

    $query = "SELECT flag2FA FROM usuarios WHERE userId = ?";
    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $flag2FA);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($flag2FA !== null) {
            // Retornar a Flag2FA como JSON
            $response['flag2FA'] = (int)$flag2FA;
        } else {
            $response['error'] = 'Erro ao consultar a Flag2FA no banco de dados.';
        }
    } else {
        $response['error'] = 'Erro na preparação da consulta: ' . mysqli_error($con);
    }
} else {
    $response['error'] = 'Nenhuma sessão ativa.';
}

mysqli_close($con);

header('Content-Type: application/json');
echo json_encode($response);
?>
