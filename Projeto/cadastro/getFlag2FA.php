<?php
session_start();
require_once '../login/connection.php';
define('SESSION_EXPIRATION_TIME', 9000);

function isSessionExpired() {
    if (isset($_SESSION['login_time'])) {
        return (time() - $_SESSION['login_time'] > SESSION_EXPIRATION_TIME);
    }
    return true;
}

if (isSessionExpired()) {
    session_unset();
    session_destroy();
    header("Location: ../login/index.html");
    exit;
} else {
    $_SESSION['login_time'] = time();
}

$response = array();

if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    $query = "SELECT flag2FA FROM usuarios WHERE email = ?";
    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $flag2FA);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($flag2FA !== null) {
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

echo json_encode($response);
?>
