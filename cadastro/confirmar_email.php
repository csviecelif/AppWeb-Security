<?php
// Inclui o autoload do Composer para carregar as dependências
session_start();
use OTPHP\TOTP;
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


define('SESSION_EXPIRATION_TIME', 900);

function isSessionExpired() {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_EXPIRATION_TIME) {
            return true;
        }
    }
    return false;
}

if (isSessionExpired()) {
    session_unset(); // Remove todas as variáveis de sessão
    session_destroy(); // Destroi a sessão
    header("Location: ../login/index.html"); // Redireciona para a página de login
    exit;
} else {
    // Atualiza o timestamp da sessão
    $_SESSION['login_time'] = time();
}




// Função para gerar um token numérico de 6 dígitos
function gerarTokenNumerico($tamanho = 6) {
    return mt_rand(pow(10, $tamanho - 1), pow(10, $tamanho) - 1);
}

// Verifica se o formulário foi submetido via método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtém os dados do formulário
    $nomeCompleto = $_POST["nomeCompleto"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $senhaHash = hash('sha256', $senha);
    $cpf = $_POST["cpf"]; // Supondo que também haja um campo CPF
    $telefone = $_POST["telefone"]; // Supondo que também haja um campo Telefone

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

    $otp = TOTP::generate();
    $twoef = $otp->getSecret();



    // Prepara a declaração SQL para inserir os dados na tabela de usuários
    $sql_insert_user = "INSERT INTO usuarios (nomeCompleto, email, senha, cpf, telefone, token, twoef) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Prepara a declaração SQL usando uma instrução preparada para evitar SQL injection
    $stmt_insert_user = $conn->prepare($sql_insert_user);

    // Vincula os parâmetros da declaração SQL
    $stmt_insert_user->bind_param("sssssss", $nomeCompleto, $email, $senhaHash, $cpf, $telefone, $token, $twoef);

    $query = "SELECT * FROM usuarios WHERE email='$email'";

    // Após o cadastro ser realizado com sucesso e o e-mail enviado com o token
    if ($stmt_insert_user->execute()) {
        try {

            $userId = $conn->insert_id;
            // Armazenar o ID do usuário na sessão
            $_SESSION['userId'] = $userId;
            
            // Configurações do servidor SMTP para envio de e-mail
            $mail = new PHPMailer(true);

            // Configurações do servidor SMTP para envio de e-mail
            $mail->isSMTP();
            $mail->SMTPDebug = 2;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 465;
            $mail->Username = 'globaloportuna@gmail.com';
            $mail->Password = 'qgvm brod pjrl wflz';
            $mail->setFrom('globaloportuna@gmail.com', 'Global Oportuna');
            $mail->addAddress($email, ''); // Define o destinatário
            $mail->isHTML(true);
            $mail->Subject = 'Token de Confirmação de Cadastro';
            $mail->Body = 'Seu token de confirmação é: ' . $token;

            // Envia o e-mail
            $mail->send();
            echo 'E-mail enviado com sucesso! Verifique sua caixa de entrada.';

            // Redireciona para a página de validação do token após enviar o e-mail
            header("Location: validar_token.php?email=" . urlencode($email));
            exit;
        } catch (Exception $e) {
            echo "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
        }
    } else {
        // Erro ao cadastrar usuário
        echo "Erro ao cadastrar o usuário: " . $stmt_insert_user->error;
    }

    // Fecha a declaração e a conexão com o banco de dados
    $stmt_insert_user->close();
    $conn->close();

}
?>
