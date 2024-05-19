<?php

// Função para converter binário para string
function toString($binary)
{
    $chunks = str_split($binary, 8);
    $string = '';
    foreach ($chunks as $chunk) {
        $string .= chr(bindec($chunk));
    }
    return $string;
}

// Função para converter uma string em binário
function toBin($string)
{
    $characters = str_split($string);
    $binary = '';
    foreach ($characters as $char) {
        $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
    }
    return $binary;
}

// Carrega a imagem com a chave
$image_path = '../converted_image.png'; // Caminho para a imagem que contém a chave
$image = imagecreatefrompng($image_path);

$width = imagesx($image);
$height = imagesy($image);
$binary_key_content = '';

for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
        $rgb = imagecolorat($image, $x, $y);
        $b = $rgb & 0xFF;

        // Extrai o bit menos significativo do canal azul
        $binary_key_content .= ($b & 1);
    }
}

// Converte o conteúdo binário de volta para a string original
$key_content = toString($binary_key_content);

// Uso chave
$key = $key_content;

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
