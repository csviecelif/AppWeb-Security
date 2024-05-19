<?php
session_start();
require_once '../login/connection.php';

$response = array();

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    $query = "SELECT flag2FA FROM usuarios WHERE email = ?";
    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $flag2FA);
        
        if (mysqli_stmt_fetch($stmt)) {
            $response['flag2FA'] = (int)$flag2FA;
        } else {
            $response['error'] = 'Erro ao consultar a Flag2FA no banco de dados.';
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $response['error'] = 'Erro na preparação da consulta: ' . mysqli_error($con);
    }
} else {
    $response['error'] = 'E-mail não encontrado na sessão.';
}

mysqli_close($con);

echo json_encode($response);
?>
