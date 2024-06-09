document.addEventListener('DOMContentLoaded', function() {
    fetch("../cadastro/verificarsessao.php", {
        method: "GET",
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === false) {
            //alert('Você deve estar logado para acessar esta página');
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
                            <h2>${mensagem.remetenteNome}</h2>
                            <p>${mensagem.mensagem.slice(0, 50)}...</p>
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
            <h2>De: ${mensagem.remetenteNome}</h2>
            <p>${mensagem.mensagem}</p>
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

    window.enviarResposta = function(destinatarioId, botao) {
        const respostaContainer = botao.parentElement;
        const mensagem = respostaContainer.querySelector('textarea').value;

        if (mensagem.trim() === '') {
            alert('A mensagem não pode estar vazia.');
            return;
        }

        fetch('enviar_mensagem.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ destinatarioId: destinatarioId, mensagem: mensagem })
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
});
