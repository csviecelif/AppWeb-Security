document.addEventListener('DOMContentLoaded', function() {
    fetch("../cadastro/verificarsessao.php", {
        method: "GET",
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === false) {
            location.href = "../login/index.html";
        } else {
            carregarMensagens();
        }
    })
    .catch(error => console.error('Erro ao verificar sessão:', error));

    function carregarMensagens() {
        const listaMensagens = document.getElementById('mensagens-lista');

        if (!listaMensagens) {
            console.error('Elemento mensagens não encontrado');
            return;
        }

        fetch('buscar_mensagens.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na solicitação. Código de status: ' + response.status);
                }
                return response.json();
            })
            .then(mensagens => {
                if (mensagens.length > 0) {
                    mensagens.forEach(mensagem => {
                        const mensagemItem = document.createElement('div');
                        mensagemItem.classList.add('message-item');
                        mensagemItem.innerHTML = `
                            <h2>${mensagem.remetenteNome || 'Remetente não identificado'}</h2>
                            <p>${mensagem.mensagem ? mensagem.mensagem.slice(0, 50) : 'Erro ao descriptografar a mensagem'}...</p>
                        `;
                        mensagemItem.addEventListener('click', () => abrirMensagemDetalhe(mensagem));
                        listaMensagens.appendChild(mensagemItem);
                    });
                } else {
                    listaMensagens.innerHTML = '<p>Nenhuma mensagem na caixa de entrada.</p>';
                }
            })
            .catch(error => console.error('Erro ao buscar mensagens:', error));
    }

    function abrirMensagemDetalhe(mensagem) {
        const mensagemDetalhe = document.getElementById('mensagem-detalhe');
        mensagemDetalhe.innerHTML = `
            <h2>De: ${mensagem.remetenteNome || 'Remetente não identificado'}</h2>
            <p>${mensagem.mensagem ? mensagem.mensagem : 'Erro ao descriptografar a mensagem'}</p>
            <p><strong>Enviado em:</strong> ${new Date(mensagem.data_envio).toLocaleString()}</p>
            <button class="contact-button" onclick="mostrarCaixaResposta(${mensagem.remetenteId}, '${mensagem.remetenteNome}')">Responder</button>
            <div id="resposta-container"></div>
        `;
    }

    window.mostrarCaixaResposta = function(destinatarioId, nomeCompleto) {
        const respostaContainer = document.getElementById('resposta-container');
        respostaContainer.innerHTML = `
            <textarea class="resposta-textarea" placeholder="Digite sua resposta aqui..."></textarea>
            <button class="send-button" onclick="enviarResposta(${destinatarioId}, this)">Enviar mensagem</button>
        `;
    }

    window.enviarResposta = async function(destinatarioId, botao) {
        const respostaContainer = botao.parentElement;
        const mensagem = respostaContainer.querySelector('textarea').value;

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
                respostaContainer.innerHTML = '';
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
        formData.append('secretKey', btoa(encryptedSecretKey));
        formData.append('mensagem', btoa(encryptedMessage));

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
