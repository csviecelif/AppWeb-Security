<?php
header('Content-Type: application/json');
include '../login/connection.php';

$query = "
    SELECT 
        b.userId, 
        u.nomeCompleto, 
        b.experiencia_profissional, 
        b.habilidades_competencias, 
        b.formacao_academica, 
        b.idiomas_falados, 
        b.area_interesse, 
        b.expectativa_salarial, 
        b.pais_origem, 
        b.cv, 
        b.certificados, 
        b.foto, 
        b.criado_em 
    FROM 
        buscar_emprego b
    JOIN 
        usuarios u ON b.userId = u.userId
";
$result = $con->query($query);

$candidatos = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $candidatos[] = $row;
    }
}

echo json_encode($candidatos);

$con->close();
?>