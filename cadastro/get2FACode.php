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
        session_unset(); // Remove todas as variáveis de sessão
        session_destroy(); // Destroi a sessão
        header("Location: ../login/index.html"); // Redireciona para a página de login
        exit;
    } else {
        // Atualiza o timestamp da sessão
        $_SESSION['login_time'] = time();
    }

    require_once 'connection.php'; // Inclui o arquivo de conexão

    if (isset($_SESSION['userId'])) {
        $userId = $_SESSION['userId'];
        
        // Consultar o 2FACode para o usuário
        $query = "SELECT twoef FROM usuarios WHERE userId = $userId";
        $result = mysqli_query($con, $query);
        
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $secret = $row['twoef'];

            // Retornar o 2FACode e o email como resposta JSON
            echo json_encode(array('secret' => $secret));
        } else {
            echo json_encode(array('error' => 'Erro ao consultar o 2FACode no banco de dados.'));
        }
    } else {
        echo json_encode(array('error' => 'Nenhuma sessão ativa.'));
    }
?>