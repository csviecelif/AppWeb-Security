<?php
header('Content-Type: application/json');
include '../login/connection.php';

$query = "SELECT userId, nome_empresa, cargo, pais_empresa, setor, descricao_vaga, requisitos_vaga, salario, beneficios, endereco_empresa, website_empresa, redes_sociais_empresa, foto, criado_em FROM oferecer_emprego";
$result = $con->query($query);

$empregos = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $empregos[] = $row;
    }
}

echo json_encode($empregos);

$con->close();
?>
