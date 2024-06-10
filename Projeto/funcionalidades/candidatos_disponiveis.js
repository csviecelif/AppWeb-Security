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
        const response = await fetch('../cert/enviar_certificado.php');
        if (!response.ok) {
            throw new Error('Erro ao carregar o certificado');
        }
        const certText = await response.text();
        const publicKey = extractPublicKey(certText);

        const encrypt = new JSEncrypt();
        encrypt.setPublicKey(publicKey);
        const secretKey = generateSecretKey();
        console.log('Chave secreta gerada: ', secretKey);
        const encryptedSecretKey = encrypt.encrypt(secretKey);
        console.log('Chave secreta criptografada: ', encryptedSecretKey);

        // Geração do IV
        const iv = CryptoJS.lib.WordArray.random(16).toString(CryptoJS.enc.Hex);
        const encryptedMessage = CryptoJS.AES.encrypt(mensagem, CryptoJS.enc.Hex.parse(secretKey), { iv: CryptoJS.enc.Hex.parse(iv) }).toString();
        console.log('Mensagem criptografada: ', encryptedMessage);

        const encryptedData = {
            destinatarioId: destinatarioId,
            data: encryptedSecretKey,
            iv: iv,
            mensagem: encryptedMessage
        };

        const enviarResponse = await fetch('enviar_mensagem.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(encryptedData)
        });
        const result = await enviarResponse.json();

        if (result.success) {
            alert('Mensagem enviada com sucesso!');
            replyBox.style.display = 'none';
        } else {
            alert('Erro ao enviar mensagem: ' + result.error);
        }
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
    }
}

function extractPublicKey(cert) {
    const certificate = forge.pki.certificateFromPem(cert);
    const publicKey = forge.pki.publicKeyToPem(certificate.publicKey);
    return publicKey;
}

function generateSecretKey() {
    return CryptoJS.lib.WordArray.random(32).toString(CryptoJS.enc.Hex);
}
