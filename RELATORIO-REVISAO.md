# Revisão da versão recebida (15/10/2026)

## Comparação
A versão enviada mantém os arquivos de código da versão GitHub anterior, com uma imagem adicional `Rosseto.png`. A principal regressão foi a ausência de 29 imagens em `public/assets/imagens/arquivo-legado`; elas foram restauradas.

## Correções
- Restauradas 29 imagens legadas para evitar referências estáticas quebradas.
- Upload GitHub passou a gerar sufixo aleatório além do hash, evitando erro HTTP 422 ao enviar o mesmo arquivo novamente.
- Redirecionamento `img.php` restringido a caminhos estáticos locais ou HTTPS.
- Arquivos `.env` e diretório `.git` removidos do pacote distribuído, para não compartilhar segredos nem histórico local.
- Sintaxe PHP verificada com `php -l`.

## Limitações e ações necessárias
- A aplicação ainda consulta a tabela `imagens` para resolver IDs, mas não armazena os bytes de novos uploads nela. Para remover completamente essa consulta, é necessária uma migração de referências do banco e alteração dos modelos.
- As imagens legadas ainda em Base64 no Neon não foram migradas automaticamente.
- QR Codes gerados dinamicamente usam o serviço externo atual.
- Configurar `GITHUB_TOKEN`, `GITHUB_REPOSITORY` e `GITHUB_BRANCH` na Vercel; executar migração de `arquivo_url` no Neon se ainda não aplicada.
- Não foram realizados testes end-to-end com credenciais reais ou deploy.
