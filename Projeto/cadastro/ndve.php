<?php 

session_start();

if (isset($_SESSION['userId'])) {
    echo "1";



}
else { echo "n";}

?>