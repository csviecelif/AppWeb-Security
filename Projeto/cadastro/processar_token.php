<?php
// Verifica se o formulário foi submetido via método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera os dados do formulário
    $email = $_POST['email'];
    $token = $_POST['token'];

    // Configurações de conexão com o banco de dados
    $servername = "127.0.0.1"; // Endereço do servidor MySQL
    $username = "root"; // Nome de usuário do banco de dados
    $password = "PUC@1234"; // Senha do banco de dados
    $dbname = "normal"; // Nome do banco de dados

    // Cria uma conexão com o banco de dados usando MySQLi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verifica a conexão
    if ($conn->connect_error) {
        die("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }

    // Prepara uma consulta SQL para verificar se o token é válido
    $sql = "SELECT * FROM usuarios WHERE email = ? AND token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se o token é válido (se existe uma correspondência no banco de dados)
    if ($result->num_rows > 0) {
        // Token válido, realizar ação de confirmação (ex: atualizar status de confirmação no banco de dados)

        // Redireciona para a página logado.html após validar o token com sucesso
        header("Location: logado.html");
        exit;
    } else {
        // Token inválido, exibir uma mensagem de erro
        echo "Token inválido. Crie um novo cadastro";
    }

    // Fecha a conexão com o banco de dados
    $stmt->close();
    $conn->close();
} else {
    // Se não foi submetido via POST, exibir uma mensagem de erro
    echo "Erro ao processar o token. Tente um novo cadastro";
}
?>
