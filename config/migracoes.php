<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

const MIGRACOES_VERSAO = 4;

/**
 * Executa os arquivos sql/migracao-imagens*.sql uma vez por versão do arquivo (hash em configuracoes).
 * Cada comando roda separado e o erro informa a etapa e o comando que falhou.
 * Nunca derruba as telas públicas: erros só vão para o log (a menos que $silencioso seja false).
 */
function executar_migracoes(bool $forcar = false, bool $silencioso = true): array
{
    $resultado = [];
    $etapa = 'conexão';
    try {
        $pdo = db();
        // Envio em texto simples: evita comandos preparados no servidor (problemáticos com o pooler do Neon).
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        foreach (glob(dirname(__DIR__) . '/sql/migracao-imagens*.sql') ?: [] as $arquivo) {
            $sql = (string) file_get_contents($arquivo);
            if (trim($sql) === '') {
                continue;
            }
            $nome  = basename($arquivo);
            $chave = 'migracao_' . basename($arquivo, '.sql');
            $hash  = md5($sql);

            $etapa = 'ler configuracoes';
            $atual = $pdo->query('SELECT valor FROM configuracoes WHERE chave = ' . $pdo->quote($chave))->fetchColumn();
            if (!$forcar && $atual === $hash) {
                $resultado[$nome] = 'já aplicada';
                continue;
            }

            $semComentarios = (string) preg_replace('/^\s*--.*$/m', '', $sql);
            $comandos = array_values(array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $semComentarios) ?: [])));

            $anterior = '(nenhum)';
            $etapa = 'iniciar transação';
            $pdo->beginTransaction();
            try {
                foreach ($comandos as $i => $comando) {
                    $etapa = 'comando ' . ($i + 1) . '/' . count($comandos) . ': ' . substr(preg_replace('/\s+/', ' ', $comando), 0, 140);
                    try {
                        $pdo->query($comando)->closeCursor();
                    } catch (Throwable $e) {
                        throw new RuntimeException($e->getMessage() . ' || comando anterior: ' . $anterior, 0, $e);
                    }
                    $anterior = substr(preg_replace('/\s+/', ' ', $comando), 0, 140);
                }
                $etapa = 'registrar migração';
                $pdo->query('INSERT INTO configuracoes (chave, valor) VALUES (' . $pdo->quote($chave) . ', ' . $pdo->quote($hash) . ')
                             ON CONFLICT (chave) DO UPDATE SET valor = EXCLUDED.valor')->closeCursor();
                $etapa = 'commit';
                $pdo->commit();
                $resultado[$nome] = 'executada com sucesso (' . count($comandos) . ' comandos)';
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }
        }
    } catch (Throwable $e) {
        error_log('migracoes [' . $etapa . ']: ' . $e->getMessage());
        $resultado['erro'] = $silencioso
            ? 'falhou (veja o log da Vercel)'
            : 'falhou na etapa [' . $etapa . '] -> ' . $e->getMessage();
    }
    return $resultado;
}

// Execução direta pelo terminal: php config/migracoes.php [--forcar]
if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    foreach (executar_migracoes(in_array('--forcar', $argv, true), false) as $arquivo => $status) {
        echo $arquivo . ': ' . $status . PHP_EOL;
    }
}
