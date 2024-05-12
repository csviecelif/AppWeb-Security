<?php
    session_start();

    require_once '../login/connection.php'; // conexao com o banco de dados

    if (isset($_SESSION['userId'])) {
        $userId = $_SESSION['userId'];
        
        // Verificar a Flag2FA para o usuário
        $query = "SELECT flag2fa FROM usuarios WHERE userId = $userId";
        $result = mysqli_query($con, $query);
        
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $flag2FA = $row['flag2fa'];
            
            // Retornar a Flag2FA como JSON
            echo json_encode(array('flag2FA' => (int)$row['flag2fa']));
        } else {
            // Se houver um erro na consulta
            echo json_encode(array('error' => 'Erro ao consultar a Flag2FA no banco de dados.'));
        }
    } else {
        // Se não houver uma sessão ativa
        echo json_encode(array('error' => 'Nenhuma sessão ativa.'));
    }
?>