<?php
header('Content-Type: application/json');
include '../login/connection.php';

function logSecurityEvent($message) {
    $logFile = 'security.log';
    $date = new DateTime();
    file_put_contents($logFile, $date->format('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

try {
    $query = "SELECT userId, experiencia_profissional, habilidades_competencias, formacao_academica, idiomas_falados, data_nascimento, area_interesse, expectativa_salarial, pais_origem, cv, certificados, bio, foto FROM buscar_emprego";
    $result = $con->query($query);

    $candidatos = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $candidatos[] = $row;
        }
    }

    echo json_encode($candidatos);
} catch (mysqli_sql_exception $e) {
    logSecurityEvent("Erro ao buscar candidatos: " . $e->getMessage());
    echo json_encode(['error' => 'Erro ao buscar candidatos']);
}

$con->close();
?>
