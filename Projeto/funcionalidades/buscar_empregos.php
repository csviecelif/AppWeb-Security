<?php
require '../login/connection.php';

$query = "SELECT cargo, pais_empresa, setor FROM oferecer_emprego";
$result = $con->query($query);

$empregos = array();
while ($row = $result->fetch_assoc()) {
    $empregos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($empregos);
?>
