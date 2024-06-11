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
$experience = $decodedData['experience'];
$skills = $decodedData['skills'];
$education = $decodedData['education'];
$languages = $decodedData['languages'];
$job_type = $decodedData['job_type'];
$interest_area = $decodedData['interest_area'];
$expected_salary = $decodedData['expected_salary'];
$availability = $decodedData['availability'];
$birthdate = $decodedData['birthdate'];
$country = $decodedData['country'];

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

$cvPath = null;
if (!empty($_FILES['cv']['name'])) {
    $cvPath = $uploadDir . $userId . '-CV.' . pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
    if (!move_uploaded_file($_FILES['cv']['tmp_name'], $cvPath)) {
        echo json_encode(['error' => 'Erro ao fazer upload do CV.']);
        logSecurityEvent("Erro ao fazer upload do CV.");
        exit();
    }
}

$certificatePaths = [];
if (!empty($_FILES['certificates']['name'][0])) {
    foreach ($_FILES['certificates']['name'] as $key => $name) {
        $certificatePath = $uploadDir . $userId . '-certificados' . $key . '.' . pathinfo($name, PATHINFO_EXTENSION);
        if (!move_uploaded_file($_FILES['certificates']['tmp_name'][$key], $certificatePath)) {
            echo json_encode(['error' => 'Erro ao fazer upload dos certificados.']);
            logSecurityEvent("Erro ao fazer upload dos certificados.");
            exit();
        }
        $certificatePaths[] = $certificatePath;
    }
}
$certificatesPathsStr = implode(',', $certificatePaths);

// Insere os dados no banco de dados
$sql = "INSERT INTO buscar_emprego (
            userId, experiencia_profissional, habilidades_competencias, 
            formacao_academica, idiomas_falados, tipo_emprego_desejado, 
            area_interesse, expectativa_salarial, disponibilidade_inicio, 
            data_nascimento, pais_origem, cv, certificados, bio, foto
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $con->prepare($sql)) {
    $stmt->bind_param(
        "issssssssssssss",
        $userId, $experience, $skills, $education, $languages, $job_type, 
        $interest_area, $expected_salary, $availability, $birthdate, 
        $country, $cvPath, $certificatesPathsStr, $bio, $photoPath
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
