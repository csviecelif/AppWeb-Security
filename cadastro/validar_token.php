<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validação de Cadastro</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 50px;
        }
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <?php
            if (isset($_GET['email'])) {
                //Função para evitar XSS
                $email = htmlspecialchars($_GET['email']);
                echo "<h2>Validação de Cadastro</h2>";
                echo "<p>Um e-mail de confirmação foi enviado para <strong>{$email}</strong>. Insira o token recebido:</p>";
                echo "<form action='processar_token.php' method='post'>";
                echo "<input type='hidden' name='email' value='{$email}'>";
                echo "<div class='form-group'>";
                echo "<label for='token'>Token:</label>";
                echo "<input type='text' id='token' name='token' class='form-control' required>";
                echo "</div>";
                echo "<button type='submit' class='btn btn-primary'>Validar</button>";
                echo "</form>";
            } else {
                echo "<h2>Erro</h2>";
                echo "<div class='alert alert-danger' role='alert'>";
                echo "<p>Ocorreu um erro ao processar a solicitação. Por favor, tente novamente mais tarde.</p>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
