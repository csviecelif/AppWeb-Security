<?php
session_start();

define('SESSION_EXPIRATION_TIME', 900);

function isSessionExpired() {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_EXPIRATION_TIME) {
            return true;
        }
    }
    return false;
}

$response = array();

if (!isset($_SESSION['userId']) || isSessionExpired()) {
    session_unset(); // Remove todas as variáveis de sessão
    session_destroy(); // Destroi a sessão
    echo json_encode("False");
} else {
    // Atualiza o timestamp da sessão
    $_SESSION['login_time'] = time();
    echo json_encode("True");
}
?>
