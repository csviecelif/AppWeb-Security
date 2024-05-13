<?php
    session_start();

    if (isset($_SESSION['userId'])) {
        echo json_encode("True");
    } else {
        echo json_encode("False");
    }
?>