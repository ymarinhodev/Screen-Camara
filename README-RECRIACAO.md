# Instalação limpa — Screen-Camara

## Atenção: a recriação destrói os dados

1. Faça backup ou crie uma branch no Neon.
2. No SQL Editor, execute `sql/RECRIAR-BANCO.sql` **somente se desejar apagar tudo**.
3. Em seguida execute `sql/schema.sql` no mesmo banco.
4. Na Vercel configure `DATABASE_URL`, `SESSION_SECRET` (32+ caracteres), `ADMIN_NOME`, `ADMIN_EMAIL`, `ADMIN_SENHA` (8+ caracteres) e `SETUP_TOKEN`.
5. Faça deploy com `vercel.json` na raiz e acesse `/setup.php?token=SEU_SETUP_TOKEN` **uma vez**.
6. Verifique `/login`, `/painel`, `/tela-noticias` e `/tela-qrcode`.
7. **Remova SETUP_TOKEN, ADMIN_SENHA e outras variáveis de inicialização** da Vercel depois da instalação.

## Imagens

Uploads JPG/PNG/WEBP/GIF são redimensionados no navegador, codificados em Base64 e gravados em `imagens.base64_data`. A rota `/img.php?id=N` decodifica Base64 e entrega a imagem. Fotos oficiais de notícias também podem ser mantidas como URLs HTTPS em `noticias.imagem_url`. A importação depende da acessibilidade do portal oficial a partir da Vercel; caso haja bloqueio de rede, use upload manual.

## Importante

Não inclua `.env` ou credenciais no GitHub. O código não altera o Neon até você executar os comandos de instalação.
