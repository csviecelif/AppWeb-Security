<?php
header('Content-Type: application/x-x509-ca-cert');
echo file_get_contents('certificate.crt');
?>
