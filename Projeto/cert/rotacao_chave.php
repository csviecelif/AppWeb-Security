<?php
// Gerar uma nova chave privada
$newPrivateKeyPath = 'private_new.key';
$newPrivateKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
openssl_pkey_export_to_file($newPrivateKey, $newPrivateKeyPath);

// Atualizar o sistema para usar a nova chave privada
rename($newPrivateKeyPath, 'private.key');

// Logar a rotação de chave
logSecurityEvent("Chave privada rotacionada com sucesso.");
?>
