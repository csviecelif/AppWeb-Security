GlobalOpportuna

Bem-vindo ao GlobalOpportuna, um projeto dedicado a facilitar a busca de oportunidades de trabalho para estrangeiros que chegam a um novo país. Nossa missão é ajudar migrantes a se integrarem rapidamente ao mercado de trabalho local, proporcionando uma plataforma intuitiva e segura para encontrar empregos adequados às suas qualificações e necessidades.

Visão Geral do Projeto
O GlobalOpportuna é uma aplicação web que permite aos usuários cadastrarem-se, autenticarem-se e buscarem oportunidades de emprego. A plataforma também oferece ferramentas para empregadores publicarem vagas e se conectarem com candidatos qualificados. Este README fornece uma visão geral das funcionalidades, requisitos do sistema e instruções de configuração e uso do projeto.

Funcionalidades
Cadastro de Usuário

Página de Cadastro: Permite que novos usuários criem uma conta preenchendo informações pessoais como nome completo, e-mail, senha, CPF/CNPJ e telefone.
Validação de Dados: Utiliza expressões regulares para validar a formatação dos campos de e-mail, CPF/CNPJ e telefone.
Confirmação de E-mail: Após o cadastro, os usuários recebem um e-mail de confirmação para ativar suas contas.
Autenticação em Dois Fatores (2FA): Para aumentar a segurança, os usuários podem ativar a autenticação em dois fatores.

Autenticação de Usuário
Página de Login: Permite que os usuários entrem em suas contas utilizando e-mail e senha.
Recuperação de Senha: Opção para recuperação de senha via e-mail.
Autenticação 2FA: Implementação de autenticação em dois fatores para uma camada extra de segurança.

Gerenciamento de Sessão
Expiração de Sessão: As sessões dos usuários expiram após um determinado período de inatividade.
Controle de Acesso: URLs protegidas só podem ser acessadas por usuários autenticados.

Busca e Publicação de Vagas
Busca de Vagas: Os usuários podem procurar por oportunidades de emprego de acordo com suas qualificações e interesses.
Publicação de Vagas: Empregadores podem publicar vagas de emprego detalhadas e gerenciar candidaturas.
