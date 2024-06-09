document.addEventListener('DOMContentLoaded', function() {
    fetch("../cadastro/verificarsessao.php", {
        method: "GET",
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === false) {
            //alert('Você deve estar logado para acessar esta página');
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

function enviarResposta(destinatarioId, botao) {
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
