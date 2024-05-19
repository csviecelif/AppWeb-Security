<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $token = $_POST['token'];

    $servername = "127.0.0.1:3006";
    $username = "root";
    $password = "PUC@1234";
    $dbname = "normal";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM usuarios WHERE email = ? AND token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: 2fa.html");
        exit;
    } else {
        echo "Token inválido. Crie um novo cadastro";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Erro ao processar o token. Tente um novo cadastro";
}
?>
