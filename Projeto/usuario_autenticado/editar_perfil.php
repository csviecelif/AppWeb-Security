<?php
session_start();

require '../login/connection.php'; // Certifique-se de que o caminho está correto

$response = array('success' => false, 'message' => '');

ob_start(); // Captura qualquer saída inesperada

if (!isset($_SESSION['userId'])) {
    $response['message'] = 'Usuário não autenticado';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['userId'];
    $bio = $_POST['bio'];
    $photo = $_FILES['photo'];
    $cv = $_FILES['cv'];
    $certificates = $_FILES['certificates'];

    // Verificar se o diretório uploads existe, caso contrário, criá-lo
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Renomear e salvar a foto, se fornecida
    if (!empty($photo['name'])) {
        $photo_extension = pathinfo($photo['name'], PATHINFO_EXTENSION);
        $photo_path = 'uploads/' . $userId . '-perfil.' . $photo_extension;
        if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
            $response['message'] = 'Erro ao fazer upload da foto';
            echo json_encode($response);
            exit();
        }
    } else {
        $photo_path = null;
    }

    // Renomear e salvar o CV, se fornecido
    if (!empty($cv['name'])) {
        $cv_extension = pathinfo($cv['name'], PATHINFO_EXTENSION);
        $cv_path = 'uploads/' . $userId . '-CV.' . $cv_extension;
        if (!move_uploaded_file($cv['tmp_name'], $cv_path)) {
            $response['message'] = 'Erro ao fazer upload do CV';
            echo json_encode($response);
            exit();
        }
    } else {
        $cv_path = null;
    }

    // Renomear e salvar os certificados, se fornecidos
    $certificates_paths = [];
    if (!empty($certificates['name'][0])) {
        foreach ($certificates['name'] as $key => $name) {
            $certificate_extension = pathinfo($name, PATHINFO_EXTENSION);
            $path = 'uploads/' . $userId . '-certificados' . $key . '.' . $certificate_extension;
            if (!move_uploaded_file($certificates['tmp_name'][$key], $path)) {
                $response['message'] = 'Erro ao fazer upload dos certificados';
                echo json_encode($response);
                exit();
            }
            $certificates_paths[] = $path;
        }
    }
    $certificates_paths_str = implode(',', $certificates_paths);

    // Atualizar dados no banco de dados
    $sql = "UPDATE usuarios SET bio = ?, foto = ?, cv = ?, certificados = ? WHERE userId = ?";
    if ($stmt = $con->prepare($sql)) {
        $stmt->bind_param("ssssi", $bio, $photo_path, $cv_path, $certificates_paths_str, $userId);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['message'] = 'Erro ao atualizar perfil: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Erro ao preparar a consulta: ' . $con->error;
    }

    $con->close();

    ob_end_clean(); // Limpa qualquer saída inesperada
    echo json_encode($response);
}
?>
