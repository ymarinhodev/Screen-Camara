<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Executa automaticamente, uma única vez por versão, os arquivos de sql/migracao-imagens*.sql.
 * Roda na primeira visita às telas depois de cada deploy na Vercel. O resultado fica registrado em
 * configuracoes (chave "migracao_<arquivo>") com o hash do arquivo: se o SQL mudar, roda de novo.
 * Nunca derruba a tela: qualquer erro só é gravado no log.
 */
function executar_migracoes(): void
{
    try {
        $pdo = db();
        $arquivos = glob(dirname(__DIR__) . '/sql/migracao-imagens*.sql') ?: [];
        foreach ($arquivos as $arquivo) {
            $sql = (string) file_get_contents($arquivo);
            if (trim($sql) === '') {
                continue;
            }
            $chave = 'migracao_' . basename($arquivo, '.sql');
            $hash  = md5($sql);

            $st = $pdo->prepare('SELECT valor FROM configuracoes WHERE chave = :c');
            $st->execute([':c' => $chave]);
            if ($st->fetchColumn() === $hash) {
                continue; // já aplicada
            }

            $pdo->beginTransaction();
            try {
                // Trava para que duas requisições simultâneas não rodem a migração juntas.
                $pdo->query('SELECT pg_advisory_xact_lock(7428193)');
                $st->execute([':c' => $chave]);
                if ($st->fetchColumn() !== $hash) {
                    $pdo->exec($sql);
                    $pdo->prepare('INSERT INTO configuracoes (chave, valor) VALUES (:c, :v)
                                   ON CONFLICT (chave) DO UPDATE SET valor = EXCLUDED.valor')
                        ->execute([':c' => $chave, ':v' => $hash]);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }
        }
    } catch (Throwable $e) {
        error_log('migracoes: ' . $e->getMessage());
    }
}
