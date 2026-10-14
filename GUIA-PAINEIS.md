# Painéis da Câmara de Alvorada

- `/tela-noticias`: lado direito, 8 vereadores, seta e notícias.
- `/tela-qrcode`: lado esquerdo, 9 vereadores, seta e QR Codes.
- `/login-direito` e `/login-esquerdo`: entradas separadas, usando os mesmos usuários autenticados.
- `/painel-direito` e `/painel-esquerdo`: formulários dos vereadores filtrados por lado.
- `/painel`: administração geral, incluindo notícias, QR Codes, partidos e equipe.

**Banco novo:** execute `sql/schema.sql` e depois o procedimento de setup documentado no README original. O seed já contém os 17 nomes, gabinetes e partidos das imagens.

**Banco existente:** faça backup e execute `sql/ATUALIZAR-PAINEIS.sql` para atualizar os vereadores sem apagar notícias, QR Codes e imagens.

**Importante:** as entradas são separadas visualmente, mas não representam permissões independentes por usuário. Os acessos utilizam a autenticação existente. Fotos faltantes devem ser cadastradas no painel. Configure `.env` a partir de `.env.example`, sem publicar credenciais.
