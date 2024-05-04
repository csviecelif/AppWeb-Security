<?php
if (isset($_GET['email'])) {
    $email = $_GET['email'];
    // Exibir o formulário para validar o token
    echo "<h2>Validação de Cadastro</h2>";
    echo "<p>Um e-mail de confirmação foi enviado para <strong>{$email}</strong>. Insira o token recebido:</p>";
    echo "<form action='processar_token.php' method='post'>";
    echo "<input type='hidden' name='email' value='{$email}'>";
    echo "<label for='token'>Token:</label>";
    echo "<input type='text' id='token' name='token' required>";
    echo "<input type='submit' value='Validar'>";
    echo "</form>";
} else {
    // Se o parâmetro 'email' não estiver presente na URL, exibir uma mensagem de erro simples
    echo "<h2>Erro</h2>";
    echo "<p>Ocorreu um erro ao processar a solicitação. Por favor, tente novamente mais tarde.</p>";
}
?>
