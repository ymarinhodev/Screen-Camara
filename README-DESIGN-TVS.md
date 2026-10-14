# Telas de TV — design de referência

Esta versão mantém o painel administrativo, o banco Neon, os endpoints e o fluxo de imagens (Base64 em `imagens.base64_data` ou URLs existentes). Foram alteradas somente as apresentações públicas das duas TVs e adicionados CSS, JS e SVG da seta.

- `/tela-noticias`: 8 vereadores por página (2 colunas x 4 linhas), seta para a direita, mosaico de até 5 notícias e rodapé.
- `/tela-qrcode`: 9 vereadores por página (2 colunas, com o último à direita), seta para a esquerda, 6 QR Codes e rodapé.
- A arte base mede 720 × 1280 (9:16) e é escalada proporcionalmente, inclusive em 2160 × 3840.
- As fotos, partidos, gabinetes, notícias, QR Codes e textos são carregados do Neon. O design não insere fotografias nem logos fictícios no banco.
- Quando houver mais vereadores, notícias ou QR Codes que caibam em uma página, o conteúdo é alternado automaticamente. A página é atualizada conforme o tempo configurado no painel.
- O logo partidário é obtido do cadastro `partidos.logo_url` ou `partidos.logo_id`. Cadastre as URLs ou Base64 no painel/banco para aparecerem nas TVs.

## Publicação

Publique o conteúdo da pasta `Screen-Camara` na raiz do repositório Vercel (onde está `vercel.json`). Mantenha as variáveis de ambiente atuais. **Não é necessário apagar nem recriar o banco.**

## Limites de validação

Sintaxe PHP e JavaScript foram verificadas localmente. O resultado visual depende dos dados reais e da disponibilidade das URLs de imagens. O deploy na Vercel e a conexão ao Neon não foram testados neste ambiente.
