<?php
// Inclui o autoload do Composer para carregar as dependências
require __DIR__ . '/../vendor/autoload.php';

// Função para gerar um token numérico de 6 dígitos
function gerarTokenNumerico($tamanho = 6) {
    return mt_rand(pow(10, $tamanho - 1), pow(10, $tamanho) - 1);
}

// Verifica se o formulário foi submetido via método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtém os dados do formulário
    $nomeCompleto = $_POST["nomeCompleto"];
    $email = $_POST["email"];
    $senhaHash = $_POST["senha"]; // Hash SHA-256 da senha recebida do formulário
    $cpf = $_POST["cpf"]; // Supondo que também haja um campo CPF
    $telefone = $_POST["telefone"]; // Supondo que também haja um campo Telefone

    // Configurações de conexão com o banco de dados
    $servername = getenv('DB_HOST');
    $username = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');
    $dbname = getenv('DB_NAME');
    

    // Cria uma conexão com o banco de dados usando MySQLi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verifica a conexão
    if ($conn->connect_error) {
        die("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }

    // Gerar token numérico de 6 dígitos
    $token = gerarTokenNumerico();

    // Verifica se já existe um usuário com o mesmo email
    $sql_check_email = "SELECT * FROM usuarios WHERE email = ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $result_check_email = $stmt_check_email->get_result();

    if ($result_check_email->num_rows > 0) {
        // Email já cadastrado
        echo "Este email já está em uso. Por favor, escolha outro.";
        $stmt_check_email->close();
        $conn->close();
        exit;
    }

    //VERIFICAR SE JÁ EXISTE CPF CADASTRADO
    $sql_check_cpf = "SELECT * FROM usuarios WHERE cpf = ?";
    $stmt_check_cpf = $conn->prepare($sql_check_cpf);
    $stmt_check_cpf->bind_param("s", $cpf);
    $stmt_check_cpf->execute();
    $result_check_cpf = $stmt_check_cpf->get_result();

    if ($result_check_cpf->num_rows > 0) {
        // CPF JÁ CADASTRADO
        echo "Este CPF já está em uso. Recupere a senha se for necessário";
        $stmt_check_cpf->close();
        $conn->close();
        exit;
    }

    //VERIFICAR SE JÁ EXISTE ALGUÉM COM O MESMO NOME CADASTRADO
    $sql_check_name = "SELECT *FROM usuarios WHERE nomeCompleto = ?";
    $stmt_check_name = $conn->prepare($sql_check_name);
    $stmt_check_name->bind_param("s", $nomeCompleto);
    $stmt_check_name->execute();
    $result_check_name = $stmt_check_name->get_result();

    if ($result_check_name->num_rows > 0) {
        //NOME JÁ CADASTRADO
        echo "Este nome já está em uso. Utilize outro nome";
        $stmt_check_name->close();
        $conn->close();
        exit;
    }

    // Prepara a declaração SQL para inserir os dados na tabela de usuários
    $sql_insert_user = "INSERT INTO usuarios (nomeCompleto, email, senha, cpf, telefone, token) 
                        VALUES (?, ?, ?, ?, ?, ?)";

    // Prepara a declaração SQL usando uma instrução preparada para evitar SQL injection
    $stmt_insert_user = $conn->prepare($sql_insert_user);

    // Vincula os parâmetros da declaração SQL
    $stmt_insert_user->bind_param("ssssss", $nomeCompleto, $email, $senhaHash, $cpf, $telefone, $token);

    // Após o cadastro ser realizado com sucesso e o e-mail enviado com o token
    if ($stmt_insert_user->execute()) {
        // Redireciona para o envio do token
        header("Location: enviar_token.php");
        exit;
    } else {
        // Erro ao cadastrar usuário
        echo "Erro ao cadastrar o usuário: " . $stmt_insert_user->error;
    }

    // Fecha a declaração e a conexão com o banco de dados
    $stmt_insert_user->close();
    $conn->close();
}
?>
