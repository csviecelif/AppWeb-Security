<?php
    $host = '127.0.0.1:3306';
    $usuario = 'root'; // nome de usuário do banco de dados
    $senha = 'PUC@1234'; // senha do banco de dados
    $banco = 'normal'; // nome do banco de dados

    // Conexão com o banco de dados
    $con = new mysqli($host, $usuario, $senha, $banco);

    // Verifica a conexão
    if ($con->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }
?>