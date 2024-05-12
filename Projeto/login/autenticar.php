<?php
// Inclua aqui suas configurações de conexão com o banco de dados
$servername = "127.0.0.1";
$username = "root";
$password = "PUC@1234";
$dbname = "normal";

// Verifica se o método de requisição é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtém os dados do formulário
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    // Cria uma conexão com o banco de dados usando MySQLi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verifica a conexão
    if ($conn->connect_error) {
        die("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }

    // Prepara a consulta SQL para verificar o usuário
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Usuário encontrado, verificar a senha
        $row = $result->fetch_assoc();
        $senha_hash = $row["senha"]; // Senha armazenada no banco de dados (deve ser um hash)

        // Verifica se a senha fornecida corresponde à senha armazenada
        if (password_verify($senha, $senha_hash)) {
            // Senha correta, redireciona para a página logado.html
            header("Location: /../cadastro/logado.html");
            exit();
        } else {
            // Senha incorreta
            echo "Senha incorreta. Tente novamente.";
        }
    } else {
        // Usuário não encontrado
        echo "Usuário não encontrado. Verifique o e-mail digitado.";
    }

    // Fecha a declaração e a conexão com o banco de dados
    $stmt->close();
    $conn->close();
}
?>
