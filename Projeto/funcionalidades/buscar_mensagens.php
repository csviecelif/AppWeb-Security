<?php
header('Content-Type: application/json');
include '../login/connection.php';

session_start();

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

$userId = $_SESSION['userId'];

$query = "
    SELECT m.mensagem, m.data_envio, u.nomeCompleto as remetenteNome, m.remetenteId
    FROM mensagens m
    JOIN usuarios u ON m.remetenteId = u.userId
    WHERE m.destinatarioId = ?
    ORDER BY m.data_envio DESC
";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$mensagens = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mensagens[] = $row;
    }
}

echo json_encode($mensagens);

$con->close();
?>
