# Screen-Camara — telas das TVs da Câmara de Alvorada

PHP + PostgreSQL (Neon) + Vercel, no mesmo padrão do `Spotify-ValentinesDay`.
A equipe de comunicação entra em `/login` e altera nomes, fotos, notícias e QR Codes direto no site.

| Endereço | O que é |
|---|---|
| `/tela-noticias` (e `/`) | TV 1: vereadores + carrossel de notícias |
| `/tela-qrcode` | TV 2: vereadores + QR Codes |
| `/login` | Login da equipe (mesma ideia do `login-casal.php`) |
| `/painel` | Painel de edição |
| `/setup.php?token=...` | Primeira configuração (cria tabelas, usuário e importa os dados) |

## Como publicar

1. **Neon**: crie um projeto e copie a *connection string* (`postgresql://...?sslmode=require`).
2. **Vercel**: importe o repositório e cadastre as variáveis de `.env.example`
   (`DATABASE_URL`, `SESSION_SECRET`, `ADMIN_NOME`, `ADMIN_EMAIL`, `ADMIN_SENHA`, `SETUP_TOKEN`). Faça o deploy.
3. Abra `https://SEU-SITE.vercel.app/setup.php?token=SEU_SETUP_TOKEN`.
   Ele cria as tabelas, o primeiro usuário e importa os 18 vereadores, 9 notícias e 6 QR Codes das telas originais.
4. **Apague `SETUP_TOKEN` na Vercel** (e faça um novo deploy) e entre em `/login`.
5. Abra `/tela-noticias` e `/tela-qrcode` nas TVs. Elas recarregam sozinhas (padrão: 5 min, editável em *Textos das telas*).

Esqueceu a senha? Defina `SETUP_TOKEN` de novo, ajuste `ADMIN_SENHA` e abra `/setup.php?token=...&reset_admin=1`.

## O que a equipe edita no painel

- **Vereadores**: nome, gabinete, partido, foto, logo do partido, em qual tela aparece e a posição. Adicionar e excluir.
- **Notícias**: foto, título, link opcional, posição e “Exibir na TV”.
- **QR Codes**: nome ao lado e link. A imagem é gerada na hora pela API do `qrserver.com`
  (a mesma do `Gerador-QRCode`); nada de PNG armazenado.
- **Textos das telas**: título do topo, subtítulos, horário do rodapé e intervalo de recarga.
- **Equipe**: criar/remover usuários e trocar a própria senha.

## Decisões técnicas

- **Fotos no banco** (tabela `imagens`, `BYTEA`): o disco da Vercel é somente leitura. Cada troca grava uma imagem nova
  (outro id), então `/img.php?id=` usa cache longo. O navegador reduz as fotos antes do envio (limite de 4,5 MB da Vercel).
- **Login por cookie assinado (HMAC)**, não por `$_SESSION`: em serverless a sessão nativa do PHP se perde entre execuções.
  Cookie `HttpOnly`, `Secure`, `SameSite=Lax`, validade de 12 h, token CSRF em todas as ações e bloqueio após 8 tentativas
  erradas em 15 min por IP.
- Senhas com `password_hash` (bcrypt); consultas sempre com parâmetros (PDO).
- `.env` fica fora do Git (`.gitignore`). **Não suba o `.env` ao GitHub**: ele tem a senha do banco.

## Estrutura

```
api/        funções PHP da Vercel (telas, login, painel, ações, setup, img)
config/     env, conexão Neon (PDO), helpers, autenticação
views/      HTML compartilhado das telas públicas
sql/        schema.sql, seed.json e seed-images/ (dados das telas originais)
public/     CSS/JS/imagens estáticas (/assets/...)
vercel.json runtime vercel-php + rotas
```

## Correções e pendências de publicação

- O arquivo `Veradores-Fotos.txt` contém **17 nomes e caminhos relativos de fotos**, não URLs completas. Foram cadastrados os 17 nomes em **cada tela**, com a correspondência original entre nome e caminho da imagem. Para exibir as fotos oficiais, configure `FOTOS_OFICIAIS_BASE` com o domínio HTTPS que hospeda os caminhos `/uploads/vereador/...` **antes do primeiro setup**. Sem isso, aparece uma imagem neutra em vez de uma foto incorreta.
- Gabinete e partido ficaram vazios quando não constavam no arquivo fornecido; podem ser preenchidos no painel.
- A importação inicial não substitui cadastros já existentes. Em instalações anteriores, migre/atualize os vereadores manualmente ou em banco, preservando as edições da equipe.
- Layout retrato 9:16 para 2160×3840; valide no navegador integrado da TV real.
- QR Code depende do serviço externo `api.qrserver.com`, tal como o projeto de referência.
- Não há conexão Neon nem deploy Vercel neste pacote; validar runtime PHP, `pdo_pgsql`, HTTPS, fotos e login no ambiente de produção.
