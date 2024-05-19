<?php
session_start();

require '../login/connection.php';

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    header('Location: ../login/index.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['userId'];
    $bio = $_POST['bio'];
    $photo = $_FILES['photo'];
    $company_name = $_POST['company_name'];
    $job_title = $_POST['position'];
    $sector = $_POST['sector'];
    $job_description = $_POST['job_description'];
    $job_requirements = $_POST['job_requirements'];
    $salary = $_POST['salary'];
    $benefits = $_POST['benefits'];
    $company_address = $_POST['company_address'];
    $company_website = $_POST['company_website'];
    $company_social = $_POST['company_social'];
    $company_id = $_POST['company_id'];
    $company_country = $_POST['company_country'];


    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $photo_extension = pathinfo($photo['name'], PATHINFO_EXTENSION);
    $photo_path = 'uploads/' . $userId . '-perfil.' . $photo_extension;
    if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
        echo "Erro ao fazer upload da foto.";
        exit();
    }

    $sql = "INSERT INTO oferecer_emprego (userId, nome_empresa, cargo, setor, descricao_vaga, requisitos_vaga, salario, beneficios, endereco_empresa, website_empresa, redes_sociais_empresa, documento_identidade, pais_empresa, bio, foto)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $con->prepare($sql)) {
        $stmt->bind_param("issssssssssssss", $userId, $company_name, $job_title, $sector, $job_description, $job_requirements, $salary, $benefits, $company_address, $company_website, $company_social, $company_id, $company_country, $bio, $photo_path);
        if ($stmt->execute()) {
            header('Location: mostrar_perfil.html');
            exit();
        } else {
            echo "Erro ao realizar cadastro: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro ao preparar a consulta: " . $con->error;
    }

    $con->close();
}
?>
