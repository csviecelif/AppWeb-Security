<?php
session_start();

define('SESSION_EXPIRATION_TIME', 9000);

function isSessionExpired() {
    if (isset($_SESSION['login_time'])) {
        return (time() - $_SESSION['login_time'] > SESSION_EXPIRATION_TIME);
    }
    return true;
}

$response = array();

if (!isset($_SESSION['userId']) || isSessionExpired()) {
    session_unset();
    session_destroy();
    $response['status'] = false;
    $response['message'] = 'Sessão expirada ou usuário não autenticado.';
} else {
    $_SESSION['login_time'] = time();
    $response['status'] = true;
}

header('Content-Type: application/json');
echo json_encode($response);
?>
