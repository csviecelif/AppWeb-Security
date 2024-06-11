document.addEventListener('DOMContentLoaded', function() {
    fetch("../cadastro/verificarsessao.php", {
        method: "GET",
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === false) {
            location.href = "../login/index.html";
        } else {
            carregarCandidatos();
        }
    })
    .catch(error => console.error('Erro ao verificar sessão:', error));

    function carregarCandidatos() {
        const listaCandidatos = document.getElementById('candidatos');

        if (!listaCandidatos) {
            console.error('Elemento candidateList não encontrado');
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
                            <p><strong>Habilidades:</strong> ${candidato.habilidades_competencias}</p>
                            <p><strong>Formação:</strong> ${candidato.formacao_academica}</p>
                            <p><strong>Idiomas:</strong> ${candidato.idiomas_falados}</p>
                            <p><strong>Data de Nascimento:</strong> ${new Date(candidato.data_nascimento).toLocaleDateString()}</p>
                            <p><strong>Área de Interesse:</strong> ${candidato.area_interesse}</p>
                            <p><strong>Expectativa Salarial:</strong> ${candidato.expectativa_salarial}</p>
                            <p><strong>País de Origem:</strong> ${candidato.pais_origem}</p>
                            <p><strong>Biografia:</strong> ${candidato.bio}</p>
                            <img src="${candidato.foto}" alt="Foto do Candidato" class="candidate-photo">
                            <button class="send-button" onclick="mostrarCaixaResposta(${candidato.userId}, '${candidato.nomeCompleto}', this)">Enviar Mensagem</button>
                        `;
                        listaCandidatos.appendChild(candidatoDiv);
                    });
                } else {
                    listaCandidatos.innerHTML = '<p>Nenhum candidato disponível no momento.</p>';
                }
            })
            .catch(error => console.error('Erro ao buscar candidatos:', error));
    }

    window.mostrarCaixaResposta = function(destinatarioId, nomeCompleto, botao) {
        const candidatoDiv = botao.parentElement;
        let replyBox = candidatoDiv.querySelector('.reply-box');
        
        if (!replyBox) {
            replyBox = document.createElement('div');
            replyBox.classList.add('reply-box');
            replyBox.innerHTML = `
                <textarea placeholder="Digite sua resposta aqui..."></textarea>
                <button class="send-button" onclick="enviarResposta(${destinatarioId}, this)">Enviar mensagem</button>
            `;
            candidatoDiv.appendChild(replyBox);
        } else {
            replyBox.style.display = 'block';
        }
    }

    window.enviarResposta = async function(destinatarioId, botao) {
        const replyBox = botao.parentElement;
        const mensagem = replyBox.querySelector('textarea').value;

        if (mensagem.trim() === '') {
            alert('A mensagem não pode estar vazia.');
            return;
        }

        const payload = await prepararDadosCriptografados({ destinatarioId, mensagem });

        fetch('enviar_mensagem.php', {
            method: 'POST',
            body: payload
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Mensagem enviada com sucesso!');
                replyBox.style.display = 'none';
            } else {
                alert('Erro ao enviar mensagem: ' + data.error);
            }
        })
        .catch(error => console.error('Erro ao enviar mensagem:', error));
    }

    async function prepararDadosCriptografados(data) {
        const jsonObject = JSON.stringify(data);
        
        // Obtendo a chave pública do servidor
        const publicKey = await getPublicKeyFromServer();

        // Gerando chave secreta e IV
        const secretKey = CryptoJS.lib.WordArray.random(32).toString(CryptoJS.enc.Hex);
        const iv = CryptoJS.lib.WordArray.random(16).toString(CryptoJS.enc.Hex);

        // Criptografando a mensagem
        const encryptedMessage = CryptoJS.AES.encrypt(jsonObject, CryptoJS.enc.Hex.parse(secretKey), { iv: CryptoJS.enc.Hex.parse(iv) }).toString();

        // Criptografando a chave secreta com a chave pública
        const encryptedSecretKey = encryptSecretKey(secretKey, publicKey);

        const formData = new FormData();
        formData.append('iv', iv);
        formData.append('secretKey', encryptedSecretKey);
        formData.append('mensagem', encryptedMessage);

        return formData;
    }

    async function getPublicKeyFromServer() {
        const response = await fetch('../cert/public.key');
        const publicKey = await response.text();
        return publicKey;
    }

    function encryptSecretKey(secretKey, publicKey) {
        const encrypt = new JSEncrypt();
        encrypt.setPublicKey(publicKey);
        return encrypt.encrypt(secretKey);
    }
});
