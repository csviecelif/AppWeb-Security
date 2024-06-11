<?php
session_start();
require '../login/connection.php';

header('Content-Type: application/json');

// Função para logar mensagens de segurança
function logSecurityEvent($message) {
    $logFile = 'security.log';
    $date = new DateTime();
    file_put_contents($logFile, $date->format('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    logSecurityEvent("Usuário não autenticado");
    exit();
}

$iv = $_POST['iv'];
$encryptedSecretKey = $_POST['secretKey'];
$encryptedMessage = $_POST['mensagem'];

if (!$iv || !$encryptedSecretKey || !$encryptedMessage) {
    echo json_encode(['error' => 'Dados criptografados não recebidos corretamente.']);
    logSecurityEvent("Dados criptografados não recebidos corretamente.");
    exit();
}

$iv = hex2bin($iv);
$encryptedSecretKey = base64_decode($encryptedSecretKey);
$encryptedMessage = base64_decode($encryptedMessage);

// Carrega a chave privada
$privateKeyPath = realpath(__DIR__ . '/../cert/private.key');
if (!file_exists($privateKeyPath)) {
    echo json_encode(['error' => 'Arquivo de chave privada não encontrado.']);
    logSecurityEvent("Arquivo de chave privada não encontrado.");
    exit();
}

$privateKey = openssl_pkey_get_private('file://' . $privateKeyPath);
if (!$privateKey) {
    echo json_encode(['error' => 'Falha ao carregar a chave privada: ' . openssl_error_string()]);
    logSecurityEvent("Falha ao carregar a chave privada: " . openssl_error_string());
    exit();
}

// Descriptografa a chave secreta
$success = openssl_private_decrypt($encryptedSecretKey, $decryptedSecretKey, $privateKey);
logSecurityEvent('Chave Secreta Descriptografada: ' . $decryptedSecretKey);

if (!$success) {
    echo json_encode(['error' => 'Falha ao descriptografar a chave secreta: ' . openssl_error_string()]);
    logSecurityEvent("Falha ao descriptografar a chave secreta: " . openssl_error_string());
    exit();
}

// Descriptografa a mensagem usando a chave secreta e o IV
$decryptedMessage = openssl_decrypt($encryptedMessage, 'aes-256-cbc', hex2bin($decryptedSecretKey), OPENSSL_RAW_DATA, $iv);
logSecurityEvent('Mensagem Descriptografada: ' . $decryptedMessage);

if ($decryptedMessage === false) {
    echo json_encode(['error' => 'Falha ao descriptografar a mensagem: ' . openssl_error_string()]);
    logSecurityEvent("Falha ao descriptografar a mensagem: " . openssl_error_string());
    exit();
}

// Processa os dados descriptografados
$decodedData = json_decode($decryptedMessage, true);
$userId = $_SESSION['userId'];
$bio = $decodedData['bio'];
$company_name = $decodedData['company_name'];
$position = $decodedData['position'];
$sector = $decodedData['sector'];
$job_description = $decodedData['job_description'];
$job_requirements = $decodedData['job_requirements'];
$salary = $decodedData['salary'];
$benefits = $decodedData['benefits'];
$company_address = $decodedData['company_address'];
$company_website = $decodedData['company_website'];
$company_social = $decodedData['company_social'];
$company_country = $decodedData['company_country'];
$company_id = $decodedData['company_id'];

// Manipulação de arquivos
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$photoPath = $uploadDir . $userId . '-perfil.' . pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
    echo json_encode(['error' => 'Erro ao fazer upload da foto.']);
    logSecurityEvent("Erro ao fazer upload da foto.");
    exit();
}

// Insere os dados no banco de dados
$sql = "INSERT INTO oferecer_emprego (
            userId, nome_empresa, cargo, setor, descricao_vaga, requisitos_vaga,
            salario, beneficios, endereco_empresa, website_empresa, 
            redes_sociais_empresa, pais_empresa, documento_identidade, bio, foto
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $con->prepare($sql)) {
    $stmt->bind_param(
        "issssssssssssss",
        $userId, $company_name, $position, $sector, $job_description, $job_requirements,
        $salary, $benefits, $company_address, $company_website, $company_social, 
        $company_country, $company_id, $bio, $photoPath
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cadastro realizado com sucesso!']);
        logSecurityEvent("Cadastro realizado com sucesso para o usuário $userId.");
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao realizar cadastro: ' . $stmt->error]);
        logSecurityEvent("Erro ao realizar cadastro: " . $stmt->error);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Erro ao preparar a consulta: ' . $con->error]);
    logSecurityEvent("Erro ao preparar a consulta: " . $con->error);
}

$con->close();
?>
