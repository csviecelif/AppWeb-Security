<?php
session_start();
define('SESSION_EXPIRATION_TIME', 9000);

function isSessionExpired() {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_EXPIRATION_TIME) {
            return true;
        }
    }
    return false;
}

if (isSessionExpired()) {
    session_unset();
    session_destroy();
    header("Location: ../login/index.html");
    exit;
} else {
    $_SESSION['login_time'] = time();
}

require_once '../login/connection.php';

if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    $query = "SELECT twoef FROM usuarios WHERE userId = $userId";
    $result = mysqli_query($con, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $secret = $row['twoef'];
        echo json_encode(array('secret' => $secret));
    } else {
        echo json_encode(array('error' => 'Erro ao consultar o 2FACode no banco de dados.'));
    }
} else {
    echo json_encode(array('error' => 'Nenhuma sessão ativa.'));
}
?>
