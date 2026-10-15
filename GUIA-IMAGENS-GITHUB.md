# Imagens no GitHub

As imagens estáticas ficam em `public/assets/imagens/`, com nomes legíveis. `sql/imagens-estaticas.json` associa nomes antigos aos caminhos novos.

## Publicação

1. Publique esta versão no repositório configurado na Vercel.
2. Execute `sql/schema.sql` no Neon para adicionar `imagens.arquivo_url`.
3. Configure na Vercel `GITHUB_TOKEN` (fine-grained token com permissão Contents: Read and write), `GITHUB_REPOSITORY=ymarinhodev/Screen-Camara` e `GITHUB_BRANCH=main`. Nunca exponha o token ao navegador ou faça commit dele.
4. Os uploads do painel enviam os arquivos ao GitHub e registram **apenas a URL** na tabela imagens. A tela segue usando `img.php?id=...`, que agora redireciona à URL pública, sem consultar os bytes do banco.
5. O setup inicial usa os arquivos estáticos. Para dados já existentes no Neon, os registros antigos em Base64 continuam funcionando até migração específica; não são convertidos automaticamente.

**Atenção:** alterações via GitHub acionam novo deploy na Vercel caso a integração esteja ativa. Imagens externas da Câmara ainda podem ser usadas quando cadastradas como URLs. QR Codes gerados pela API externa permanecem dinâmicos. A funcionalidade de excluir uma imagem não exclui automaticamente o arquivo no GitHub.
