CREATE DATABASE normal;
USE normal;
CREATE TABLE usuarios (
    userId INT AUTO_INCREMENT PRIMARY KEY,
    nomeCompleto VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_validado TINYINT(1) DEFAULT 0,
    senha VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    token VARCHAR(10) NOT NULL,
    twoef VARCHAR(255) NOT NULL,
    flag2fa INT(1) DEFAULT 0,
    bio TEXT,
    foto VARCHAR(255),
    cv VARCHAR(255),
    certificados TEXT
);

CREATE TABLE buscar_emprego (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    experiencia_profissional TEXT NOT NULL,
    habilidades_competencias TEXT NOT NULL,
    formacao_academica VARCHAR(255) NOT NULL,
    idiomas_falados VARCHAR(255) NOT NULL,
    data_nascimento DATE,
    area_interesse VARCHAR(255) NOT NULL,
    expectativa_salarial DECIMAL(10, 2) NOT NULL,
    pais_origem VARCHAR(255) NOT NULL,
    cv VARCHAR(255),
    certificados TEXT,
    bio TEXT NOT NULL,
    foto VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo_emprego_desejado VARCHAR(255),
    disponibilidade_inicio VARCHAR(255),
    FOREIGN KEY (userId) REFERENCES usuarios(userId)
);


CREATE TABLE oferecer_emprego (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    nome_empresa VARCHAR(255) NOT NULL,
    cargo VARCHAR(255) NOT NULL,
    pais_empresa VARCHAR(255) NOT NULL,
    setor VARCHAR(255) NOT NULL,
    descricao_vaga TEXT NOT NULL,
    requisitos_vaga TEXT NOT NULL,
    salario DECIMAL(10, 2) NOT NULL,
    beneficios TEXT NOT NULL,
    endereco_empresa VARCHAR(255) NOT NULL,
    website_empresa VARCHAR(255),
    redes_sociais_empresa VARCHAR(255),
    documento_identidade VARCHAR(255) NOT NULL,
    bio TEXT NOT NULL,
    foto VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES usuarios(userId)
);

CREATE TABLE mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    remetenteId INT NOT NULL,
    destinatarioId INT NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (remetenteId) REFERENCES usuarios(userId),
    FOREIGN KEY (destinatarioId) REFERENCES usuarios(userId)
);

SELECT * FROM mensagens;
SELECT * FROM usuarios;
SELECT * FROM buscar_emprego;
SELECT * FROM oferecer_emprego;