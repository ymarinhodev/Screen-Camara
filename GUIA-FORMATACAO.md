# Organização do código-fonte

Os arquivos CSS autorais foram reformatados com seletores, declarações e blocos em linhas separadas e indentação de quatro espaços. Os arquivos PHP, JavaScript, SQL e JSON foram preservados estruturalmente, com limpeza de espaços finais e padronização de final de arquivo quando aplicável. Bibliotecas de terceiros não foram reformatadas para evitar regressões.

## Observações

- Esta entrega é de formatação, não de correção funcional.
- Não é necessário executar SQL nem recriar o banco Neon.
- O CSS `public/assets/css/tv-design.css` concentra o desenho dos cartões e gabinetes.
- O PHP passou por verificação de sintaxe.
- Recomenda-se testar as telas e o painel após o deploy.
- Não houve reestruturação lógica de PHP e JS, para evitar mudanças de comportamento.
