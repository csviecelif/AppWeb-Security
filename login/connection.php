<?php
require '../vendor/autoload.php';

use Dotenv\Dotenv;
  
// Carregar variáveis de ambiente do arquivo .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
  
$host = getenv('DB_HOST');
$usuario = getenv('DB_USER');
$senha = getenv('DB_PASSWORD');
$banco = getenv('DB_NAME');
  
// Conexão com o banco de dados
$con = new mysqli($host, $usuario, $senha, $banco);
  
// Verifica a conexão
if ($con->connect_error) {
    die("Erro na conexão com o banco de dados: " . $con->connect_error);
}
?>



