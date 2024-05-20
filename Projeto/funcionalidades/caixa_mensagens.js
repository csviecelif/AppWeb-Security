document.addEventListener('DOMContentLoaded', function() {
    fetch("../cadastro/verificarsessao.php", {
        method: "GET",
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === false) {
            alert('Você deve estar logado para acessar esta página');
            location.href = "../login/index.html";
        } else {
            carregarMensagens();
        }
    })
    .catch(error => console.error('Erro ao verificar sessão:', error));

    function carregarMensagens() {
        const listaMensagens = document.getElementById('mensagens');

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
                        const mensagemDiv = document.createElement('div');
                        mensagemDiv.classList.add('message');
                        mensagemDiv.innerHTML = `
                            <h2>De: ${mensagem.remetenteNome}</h2>
                            <p>${mensagem.mensagem}</p>
                            <p><strong>Enviado em:</strong> ${new Date(mensagem.data_envio).toLocaleString()}</p>
                            <button class="contact-button" onclick="mostrarCaixaResposta(${mensagem.remetenteId}, '${mensagem.remetenteNome}', this)">Responder</button>
                        `;
                        listaMensagens.appendChild(mensagemDiv);
                    });
                } else {
                    listaMensagens.innerHTML = '<p>Nenhuma mensagem na caixa de entrada.</p>';
                }
            })
            .catch(error => console.error('Erro ao buscar mensagens:', error));
    }

    window.mostrarCaixaResposta = function(destinatarioId, nomeCompleto, botao) {
        const mensagemDiv = botao.parentElement;
        let replyBox = mensagemDiv.querySelector('.reply-box');
        
        if (!replyBox) {
            replyBox = document.createElement('div');
            replyBox.classList.add('reply-box');
            replyBox.innerHTML = `
                <textarea placeholder="Digite sua resposta aqui..."></textarea>
                <button class="send-button" onclick="enviarResposta(${destinatarioId}, this)">Enviar mensagem</button>
            `;
            mensagemDiv.appendChild(replyBox);
        } else {
            replyBox.style.display = 'block';
        }
    }

    window.enviarResposta = function(destinatarioId, botao) {
        const replyBox = botao.parentElement;
        const mensagem = replyBox.querySelector('textarea').value;

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
                replyBox.style.display = 'none';
            } else {
                alert('Erro ao enviar mensagem: ' + data.error);
            }
        })
        .catch(error => console.error('Erro ao enviar mensagem:', error));
    }
});
