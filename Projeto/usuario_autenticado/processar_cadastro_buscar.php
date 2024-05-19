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
    $experience = $_POST['experience'];
    $skills = $_POST['skills'];
    $education = $_POST['education'];
    $languages = $_POST['languages'];
    $job_type = $_POST['job_type'];
    $interest_area = $_POST['interest_area'];
    $expected_salary = $_POST['expected_salary'];
    $availability = $_POST['availability'];
    $cv = $_FILES['cv'];
    $certificates = $_FILES['certificates'];
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }
    $photo_extension = pathinfo($photo['name'], PATHINFO_EXTENSION);
    $photo_path = 'uploads/' . $userId . '-perfil.' . $photo_extension;
    if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
        echo "Erro ao fazer upload da foto.";
        exit();
    }

    $cv_path = null;
    if (!empty($cv['name'])) {
        $cv_extension = pathinfo($cv['name'], PATHINFO_EXTENSION);
        $cv_path = 'uploads/' . $userId . '-CV.' . $cv_extension;
        if (!move_uploaded_file($cv['tmp_name'], $cv_path)) {
            echo "Erro ao fazer upload do CV.";
            exit();
        }
    }

    $certificates_paths = [];
    if (!empty($certificates['name'][0])) {
        foreach ($certificates['name'] as $key => $name) {
            $certificate_extension = pathinfo($name, PATHINFO_EXTENSION);
            $path = 'uploads/' . $userId . '-certificados' . $key . '.' . $certificate_extension;
            if (!move_uploaded_file($certificates['tmp_name'][$key], $path)) {
                echo "Erro ao fazer upload dos certificados.";
                exit();
            }
            $certificates_paths[] = $path;
        }
    }
    $certificates_paths_str = implode(',', $certificates_paths);

    $sql = "INSERT INTO buscar_emprego (userId, experiencia_profissional, habilidades_competencias, formacao_academica, idiomas_falados, tipo_emprego_desejado, area_interesse, expectativa_salarial, disponibilidade_inicio, cv, certificados, bio, foto)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $con->prepare($sql)) {
        $stmt->bind_param("issssssssssss", $userId, $experience, $skills, $education, $languages, $job_type, $interest_area, $expected_salary, $availability, $cv_path, $certificates_paths_str, $bio, $photo_path);
        if ($stmt->execute()) {
            echo "Cadastro realizado com sucesso!";
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
