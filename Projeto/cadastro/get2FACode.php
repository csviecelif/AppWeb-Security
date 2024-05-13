<?php
    session_start();

    require_once '../login/connection.php'; // conexao com o banco de dados

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