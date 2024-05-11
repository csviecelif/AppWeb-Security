<?php
// Inclui o autoload do Composer para carregar as dependências
require __DIR__ . '/../vendor/autoload.php';

//a
// Função para gerar um token numérico
function gerarTokenNumerico($tamanho = 6) {
    return mt_rand(pow(10, $tamanho-1), pow(10, $tamanho)-1);
}

// Verifica se o formulário foi submetido via método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtém o email do formulário
    $email = $_POST["email"];

    // Gera um token numérico de 6 dígitos
    $token = gerarTokenNumerico();

    // Configurações do servidor SMTP para envio de e-mail
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Username   = 'globaloportuna';
    $mail->Password   = 'GlobalOportuna1';
    $mail->SMTPDebug = 0;
    $mail->CharSet = 'UTF-8';
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    // Configurações do e-mail a ser enviado
    $mail->setFrom('globaloportuna@gmail.com', 'Global Oportuna'); // Altere o e-mail remetente conforme necessário
    $mail->addAddress($email, $recipient_name);
    $mail->isHTML(true);
    $mail->Subject = 'Token de Confirmação de Cadastro';
    $mail->Body = 'Seu token de confirmação é: ' . $token;
    $mail->send();


    // Enviar o e-mail
    if (!$mail->send()) {
        echo 'Erro ao enviar o e-mail: ' . $mail->ErrorInfo;
    } else {
        echo 'E-mail enviado com sucesso! Verifique sua caixa de entrada.';
        
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

        // Prepara a declaração SQL para inserir/atualizar o token na tabela de usuários
        $sql_insert_token = "UPDATE usuarios SET token = ? WHERE email = '$email'";
        $stmt_insert_token = $conn->prepare($sql_insert_token);
        $stmt_insert_token->bind_param("ss", $token, $email);

        // Executa a declaração SQL para inserir/atualizar o token
        if ($stmt_insert_token->execute()) {
            // Cadastro/atualização do token realizado com sucesso no DB
            echo "Token cadastrado/atualizado com sucesso!";
            
            // Redireciona para a página de validação do token após enviar o e-mail
            header("Location: validar_token.php?email=" . urlencode($email));
            exit;
        } else {
            // Erro ao cadastrar/atualizar o token
            echo "Erro ao cadastrar/atualizar o token: " . $stmt_insert_token->error;
        }

        // Fecha a declaração e a conexão com o banco de dados
        $stmt_insert_token->close();
        $conn->close();
    }
}
?>
