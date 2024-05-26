<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $token = $_POST['token'];
    include '../login/connection.php';

    if ($con->connect_error) {
        die("Falha na conexão com o banco de dados: " . $con->connect_error);
    }

    $sql = "SELECT * FROM usuarios WHERE email = ? AND token = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $sql_update = "UPDATE usuarios SET email_validado = 1 WHERE email = ?";
        $stmt_update = $con->prepare($sql_update);
        $stmt_update->bind_param("s", $email);
        $stmt_update->execute();

        header("Location: 2fa.html");
        exit;
    } else {
        echo "Token inválido. Crie um novo cadastro";
    }

    $stmt->close();
    $con->close();
} else {
    echo "Erro ao processar o token. Tente um novo cadastro";
}
?>
