<?php
header('Content-Type: application/json');
include 'connection.php';
session_start();

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['userId']) && isset($_SESSION['userId']) && $data['userId'] == $_SESSION['userId']) {
    $userId = $data['userId'];

    $response = [
        'oferecerEmprego' => false,
        'buscarEmprego' => false
    ];

    error_log("User ID: $userId");

    $query1 = $con->prepare("SELECT * FROM oferecer_emprego WHERE userId = ?");
    $query1->bind_param("i", $userId);
    $query1->execute();
    $result1 = $query1->get_result();

    if ($result1->num_rows > 0) {
        $response['oferecerEmprego'] = true;
    }

    $query2 = $con->prepare("SELECT * FROM buscar_emprego WHERE userId = ?");
    $query2->bind_param("i", $userId);
    $query2->execute();
    $result2 = $query2->get_result();

    if ($result2->num_rows > 0) {
        $response['buscarEmprego'] = true;
    }

    echo json_encode($response);

    $query1->close();
    $query2->close();
} else {
    echo json_encode([
        'error' => 'User ID not provided or does not match session'
    ]);
}

$con->close();
?>
