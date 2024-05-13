<?php
    session_start();

    if ($_SESSION['flag2fa'] == 1){
        echo json_encode("True");
    } else {
        echo json_encode("False");
    }
?>