<?php
session_start();
require '../login/connection.php';

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    header('Location: ../login/index.html');
    exit();
}

$userId = $_SESSION['userId'];
$table = $_POST['table'];

if ($table === 'buscar') {
    $query = "UPDATE buscar_emprego SET 
        email = ?, 
        telefone = ?, 
        bio = ?, 
        formacao_academica = ?, 
        idiomas_falados = ?, 
        data_nascimento = ?, 
        area_interesse = ?, 
        expectativa_salarial = ?, 
        pais_origem = ?, 
        experiencia_profissional = ?, 
        habilidades_competencias = ? 
        WHERE userId = ?";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param(
        'sssssssssssi', 
        $_POST['email'], 
        $_POST['telefone'], 
        $_POST['bio'], 
        $_POST['formacao_academica'], 
        $_POST['idiomas_falados'], 
        $_POST['data_nascimento'], 
        $_POST['area_interesse'], 
        $_POST['expectativa_salarial'], 
        $_POST['pais_origem'], 
        $_POST['experiencia_profissional'], 
        $_POST['habilidades_competencias'], 
        $userId
    );
} else if ($table === 'oferecer') {
    $query = "UPDATE oferecer_emprego SET 
        cargo = ?, 
        pais_empresa = ?, 
        setor = ?, 
        descricao_vaga = ?, 
        requisitos_vaga = ?, 
        salario = ?, 
        beneficios = ?, 
        endereco_empresa = ?, 
        website_empresa = ?, 
        redes_sociais_empresa = ?, 
        documento_identidade = ?, 
        bio = ? 
        WHERE userId = ?";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param(
        'ssssssssssssi', 
        $_POST['cargo'], 
        $_POST['pais_empresa'], 
        $_POST['setor'], 
        $_POST['descricao_vaga'], 
        $_POST['requisitos_vaga'], 
        $_POST['salario'], 
        $_POST['beneficios'], 
        $_POST['endereco_empresa'], 
        $_POST['website_empresa'], 
        $_POST['redes_sociais_empresa'], 
        $_POST['documento_identidade'], 
        $_POST['bio'], 
        $userId
    );
}

if ($stmt->execute()) {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $fotoPath = 'uploads/' . basename($_FILES['foto']['name']);
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $fotoPath)) {
            $query = "UPDATE $table SET foto = ? WHERE userId = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param('si', $fotoPath, $userId);
            $stmt->execute();
        }
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar perfil']);
}
?>
