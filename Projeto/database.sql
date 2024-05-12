CREATE DATABASE normal;

USE normal;
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomeCompleto VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    token VARCHAR(10) NOT NULL,
    twoef VARCHAR(255) NOT NULL,
    flag2fa INT(1) DEFAULT 0    
);

SELECT * FROM usuarios;

DROP TABLE usuarios;
DELETE FROM usuarios where id = 1;
FROM usuarios WHERE id = 15;