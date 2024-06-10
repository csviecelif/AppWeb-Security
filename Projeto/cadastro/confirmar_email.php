<?php
session_start();
use OTPHP\TOTP;
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__) . '/Login');
$dotenv->load();

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

function gerarTokenNumerico($tamanho = 6) {
    return mt_rand(pow(10, $tamanho - 1), pow(10, $tamanho) - 1);
}

// Recebe os dados criptografados do cliente
$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados || !isset($dados['nomeCompleto']) || !isset($dados['email']) || !isset($dados['senha']) || !isset($dados['iv']) || !isset($dados['secretKey']) || !isset($dados['cpf']) || !isset($dados['telefone'])) {
    echo json_encode(['error' => 'Dados criptografados não recebidos corretamente.']);
    exit();
}

// Extrai os dados do array
$nomeCompleto = $dados['nomeCompleto'];
$email = $dados['email'];
$senha = $dados['senha'];
$iv = $dados['iv'];
$secretKey = base64_decode($dados['secretKey']);
$cpf = $dados['cpf'];
$telefone = $dados['telefone'];

// Carrega a chave privada
$privateKeyPath = realpath(__DIR__ . '/../cert/private.key');
if (!file_exists($privateKeyPath)) {
    echo json_encode(['error' => 'Arquivo de chave privada não encontrado.']);
    exit();
}

$privateKey = openssl_pkey_get_private('file://' . $privateKeyPath);
if (!$privateKey) {
    echo json_encode(['error' => 'Falha ao carregar a chave privada: ' . openssl_error_string()]);
    exit();
}

// Descriptografa a chave secreta
$success = openssl_private_decrypt($secretKey, $decryptedSecretKey, $privateKey);
if (!$success) {
    echo json_encode(['error' => 'Falha ao descriptografar a chave secreta: ' . openssl_error_string()]);
    exit();
}

// Descriptografa a senha usando a chave secreta e o IV
$decryptedPassword = openssl_decrypt(base64_decode($senha), 'aes-256-cbc', hex2bin($decryptedSecretKey), OPENSSL_RAW_DATA, hex2bin($iv));
if ($decryptedPassword === false) {
    echo json_encode(['error' => 'Falha ao descriptografar a senha: ' . openssl_error_string()]);
    exit();
}

// Hash da senha
$senhaHash = hash('sha256', $decryptedPassword);

include '../login/connection.php';

if ($con->connect_error) {
    die("Falha na conexão com o banco de dados: " . $con->connect_error);
}

$token = gerarTokenNumerico();

$sql_check_email = "SELECT * FROM usuarios WHERE email = ?";
$stmt_check_email = $con->prepare($sql_check_email);
$stmt_check_email->bind_param("s", $email);
$stmt_check_email->execute();
$result_check_email = $stmt_check_email->get_result();

if ($result_check_email->num_rows > 0) {
    echo json_encode(["error" => "Este email já está em uso. Por favor, escolha outro."]);
    $stmt_check_email->close();
    $con->close();
    exit;
}

$sql_check_cpf = "SELECT * FROM usuarios WHERE cpf = ?";
$stmt_check_cpf = $con->prepare($sql_check_cpf);
$stmt_check_cpf->bind_param("s", $cpf);
$stmt_check_cpf->execute();
$result_check_cpf = $stmt_check_cpf->get_result();

if ($result_check_cpf->num_rows > 0) {
    echo json_encode(["error" => "Este CPF já está em uso. Recupere a senha se for necessário."]);
    $stmt_check_cpf->close();
    $con->close();
    exit;
}

$sql_check_name = "SELECT * FROM usuarios WHERE nomeCompleto = ?";
$stmt_check_name = $con->prepare($sql_check_name);
$stmt_check_name->bind_param("s", $nomeCompleto);
$stmt_check_name->execute();
$result_check_name = $stmt_check_name->get_result();

if ($result_check_name->num_rows > 0) {
    echo json_encode(["error" => "Este nome já está em uso. Utilize outro nome."]);
    $stmt_check_name->close();
    $con->close();
    exit;
}

$otp = TOTP::generate();
$twoef = $otp->getSecret();

$sql_insert_user = "INSERT INTO usuarios (nomeCompleto, email, senha, cpf, telefone, token, twoef) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt_insert_user = $con->prepare($sql_insert_user);
$stmt_insert_user->bind_param("sssssss", $nomeCompleto, $email, $senhaHash, $cpf, $telefone, $token, $twoef);

if ($stmt_insert_user->execute()) {
    try {
        $userId = $con->insert_id;
        $_SESSION['userId'] = $userId;

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug = 0;  // Alterado para evitar saída de depuração no cliente
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->Port = $_ENV['SMTP_PORT'];
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->setFrom('globaloportuna@gmail.com', 'Global Oportuna');
        $mail->addAddress($email, '');
        $mail->isHTML(true);
        $mail->Subject = 'Token de Confirmação de Cadastro';
        $mail->Body = 'Seu token de confirmação é: ' . $token;

        $mail->send();
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(["error" => "Erro ao enviar o e-mail: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(["error" => "Erro ao cadastrar o usuário: " . $stmt_insert_user->error]);
}

$stmt_insert_user->close();
$con->close();
?>

