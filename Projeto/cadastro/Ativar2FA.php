<?php


    use OTPHP\TOTP; //import da biblioteca
    require '..\vendor\autoload.php';
    require_once '../login/connection.php'; // conexao com o banco de dados
    session_start();

    $response = array();

    if (isset($_SESSION['userId'])) {
        $userId = $_SESSION['userId'];
        
        // Verificar se o OTP inserido pelo usuário está correto
        if (isset($_POST['OTP'])) {
            $userInput = $_POST['OTP'];
            // Consultar o 2FACode do usuário
            $query = "SELECT twoef FROM usuarios WHERE userId = $userId";
            $result = mysqli_query($con, $query);
            
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                $secret = $row['twoef'];
                
                // Comparar o OTP inserido pelo usuário com o OTP gerado a partir do 2FA
                $otp = TOTP::createFromSecret($secret);
                if ($otp->verify($userInput)) {
                    // OTP válido, atualizar Flag2FA para 1
                    $updateQuery = "UPDATE usuarios SET flag2fa = 1 WHERE userId = $userId";
                    $updateResult = mysqli_query($con, $updateQuery);
                    if ($updateResult) {
                        $response['success'] = true;
                        $_SESSION["flag2fa"] = 1;
                    } else {
                        $response['success'] = false;
                        $_SESSION["flag2fa"] = 0;
                        $response['error'] = 'Erro ao atualizar flag2fa no banco de dados.';
                    }
                } else {
                    // OTP inválido
                    $response['success'] = false;
                    $_SESSION["flag2fa"] = 0;
                    $response['error'] = 'OTP inválido.';
                }
            } else {
                $response['success'] = false;
                $_SESSION["flag2fa"] = 0;
                $response['error'] = 'Erro ao consultar o flag2fa no banco de dados.';
            }
        } else {
            $response['success'] = false;
            $_SESSION["flag2fa"] = 0;
            $response['error'] = 'Nenhum OTP fornecido.';
        }
    } else {
        $response['success'] = false;
        $_SESSION["flag2fa"] = 0;
        $response['error'] = 'Nenhuma sessão ativa.';
    }

    // Saída do JSON
    echo json_encode($response);
?>