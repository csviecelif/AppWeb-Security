<?php
require '../login/connection.php';

$query = "SELECT email, telefone, bio FROM buscar_emprego";
$result = $con->query($query);

$candidatos = array();
while ($row = $result->fetch_assoc()) {
    $candidatos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($candidatos);
?>
