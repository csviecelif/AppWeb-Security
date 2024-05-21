<?php
require '../login/connection.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($data['token']) && isset($data['senha'])) {
    $token = $data['token'];
    $novaSenha = $data['senha'];

    $stmt = $con->prepare("SELECT userId FROM usuarios WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $con->prepare("UPDATE usuarios SET senha = ?, token = '' WHERE token = ?");
        $stmt->bind_param("ss", $novaSenha, $token);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Senha redefinida com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar a senha: ' . $stmt->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Token inválido']);
    }
    $stmt->close();
    $con->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
}
?>
