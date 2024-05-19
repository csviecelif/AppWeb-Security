<?php
session_start();
require '../login/connection.php';

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    header('Location: ../login/index.html');
    exit();
}
$userId = $_SESSION['userId'];
$query = "SELECT 'oferecer' AS table_name FROM oferecer_emprego WHERE userId = ?
          UNION
          SELECT 'buscar' AS table_name FROM buscar_emprego WHERE userId = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado em nenhuma tabela']);
    exit();
}
$row = $result->fetch_assoc();
$table = $row['table_name'];
$table_name = $table === 'oferecer' ? 'oferecer_emprego' : 'buscar_emprego';
$query = "SELECT * FROM $table_name WHERE userId = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user['table'] = $table;
    echo json_encode(['success' => true, 'data' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
}
?>
