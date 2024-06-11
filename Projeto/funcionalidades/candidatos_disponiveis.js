document.addEventListener('DOMContentLoaded', function() {
    fetch("../cadastro/verificarsessao.php", {
        method: "GET",
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === false) {
            location.href = "../login/index.html";
        }
    })
    .catch(error => console.error('Erro ao verificar sessão:', error));

    const listaCandidatos = document.getElementById('candidatos');

    if (!listaCandidatos) {
        console.error('Elemento candidatos não encontrado');
        return;
    }

    fetch('buscar_candidatos.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na solicitação. Código de status: ' + response.status);
            }
            return response.json();
        })
        .then(candidatos => {
            if (candidatos.length > 0) {
                candidatos.forEach(candidato => {
                    const candidatoDiv = document.createElement('div');
                    candidatoDiv.classList.add('candidate');
                    candidatoDiv.innerHTML = `
                        <h2>${candidato.nomeCompleto}</h2>
                        <p><strong>Experiência Profissional:</strong> ${candidato.experiencia_profissional}</p>
                        <p><strong>Habilidades e Competências:</strong> ${candidato.habilidades_competencias}</p>
                        <p><strong>Formação Acadêmica:</strong> ${candidato.formacao_academica}</p>
                        <p><strong>Idiomas Falados:</strong> ${candidato.idiomas_falados}</p>
                        <p><strong>Área de Interesse:</strong> ${candidato.area_interesse}</p>
                        <p><strong>Expectativa Salarial:</strong> ${candidato.expectativa_salarial}</p>
                        <p><strong>País de Origem:</strong> ${candidato.pais_origem}</p>
                        <p><strong>Certificados:</strong> ${candidato.certificados}</p>
                        <p><strong>Data de Criação:</strong> ${new Date(candidato.criado_em).toLocaleDateString()}</p>
                        <button class="contact-button" onclick="mostrarCaixaResposta(${candidato.userId}, '${candidato.nomeCompleto}', this)">Contatar Candidato</button>
                    `;
                    listaCandidatos.appendChild(candidatoDiv);
                });
            } else {
                listaCandidatos.innerHTML = '<p>Nenhum candidato disponível no momento.</p>';
            }
        })
        .catch(error => console.error('Erro ao buscar candidatos:', error));
});

function mostrarCaixaResposta(destinatarioId, nomeCompleto, botao) {
    const candidatoDiv = botao.parentElement;
    let replyBox = candidatoDiv.querySelector('.reply-box');
    
    if (!replyBox) {
        replyBox = document.createElement('div');
        replyBox.classList.add('reply-box');
        replyBox.innerHTML = `
            <textarea placeholder="Envie sua mensagem ao candidato"></textarea>
            <button class="send-button" onclick="enviarResposta(${destinatarioId}, this)">Enviar mensagem</button>
        `;
        candidatoDiv.appendChild(replyBox);
    } else {
        replyBox.style.display = 'block';
    }
}

async function enviarResposta(destinatarioId, botao) {
    const replyBox = botao.parentElement;
    const mensagem = replyBox.querySelector('textarea').value;

    if (mensagem.trim() === '') {
        alert('A mensagem não pode estar vazia.');
        return;
    }

    try {
        const publicKey = await getPublicKeyFromServer();
        console.log('Chave Pública:', publicKey); // Adicionando log para verificar a chave pública

        const encryptedData = encryptMessage(mensagem, publicKey);
        console.log('Dados Criptografados:', encryptedData); // Adicionando log para verificar os dados criptografados

        const payload = {
            destinatarioId: destinatarioId,
            data: encryptedData.data,
            iv: encryptedData.iv,
            mensagem: encryptedData.mensagem
        };

        console.log('Payload Enviado:', payload); // Adicionando log para verificar o payload enviado

        const response = await fetch('enviar_mensagem.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        console.log('Resposta do Servidor:', data); // Adicionando log para verificar a resposta do servidor
        if (data.success) {
            alert('Mensagem enviada com sucesso!');
            replyBox.style.display = 'none';
        } else {
            alert('Erro ao enviar mensagem: ' + data.error);
        }
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
    }
}

function encryptMessage(message, publicKey) {
    const encrypt = new JSEncrypt();
    encrypt.setPublicKey(publicKey);
    
    const secretKey = forge.random.getBytesSync(32);
    const iv = forge.random.getBytesSync(16);

    const cipher = forge.cipher.createCipher('AES-CBC', secretKey);
    cipher.start({ iv: iv });
    cipher.update(forge.util.createBuffer(message));
    cipher.finish();
    const encryptedMessage = cipher.output.getBytes();

    const encryptedSecretKey = encrypt.encrypt(forge.util.encode64(secretKey));

    return {
        data: encryptedSecretKey,
        iv: forge.util.encode64(iv),
        mensagem: forge.util.encode64(encryptedMessage)
    };
}

async function getPublicKeyFromServer() {
    const response = await fetch('../cert/public.key');
    const publicKey = await response.text();
    return publicKey;
}




function extractPublicKey(cert) {
    const certificate = forge.pki.certificateFromPem(cert);
    const publicKey = forge.pki.publicKeyToPem(certificate.publicKey);
    return publicKey;
}

function generateSecretKey() {
    return CryptoJS.lib.WordArray.random(32).toString(CryptoJS.enc.Hex);
}
