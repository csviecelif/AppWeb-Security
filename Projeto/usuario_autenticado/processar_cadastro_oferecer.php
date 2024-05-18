<?php
session_start();

require '../login/connection.php'; // Certifique-se de que o caminho está correto

if (!isset($_SESSION['userId'])) {
    // Redireciona para a página de login se a sessão não estiver ativa
    header('Location: ../login/index.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['userId'];
    $bio = $_POST['bio'];
    $photo = $_FILES['photo'];
    $company_name = $_POST['company_name'];
    $job_title = $_POST['position']; // Atualizando para capturar o campo correto
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

    // Verificar se o diretório uploads existe, caso contrário, criá-lo
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Renomear e salvar a foto
    $photo_extension = pathinfo($photo['name'], PATHINFO_EXTENSION);
    $photo_path = 'uploads/' . $userId . '-perfil.' . $photo_extension;
    if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
        echo "Erro ao fazer upload da foto.";
        exit();
    }

    // Inserir dados no banco de dados
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
