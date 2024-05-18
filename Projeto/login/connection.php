<?php

$key = file_get_contents('../login/key.key');

function decrypt($data, $key) {
    $encryption_key = base64_decode($key);
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}

$config = json_decode(file_get_contents('../login/config.enc'), true);

$host = decrypt($config['host'], $key);
$usuario = decrypt($config['usuario'], $key);
$senha = decrypt($config['senha'], $key);
$banco = decrypt($config['banco'], $key);

// Conexão com o banco de dados
$con = new mysqli($host, $usuario, $senha, $banco);

// Verifica a conexão
if ($con->connect_error) {
    die("Erro na conexão com o banco de dados: " . $con->connect_error);
}
?>
