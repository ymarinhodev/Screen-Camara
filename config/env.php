<?php
declare(strict_types=1);

/** Lê variável de ambiente (Vercel) ou do arquivo .env local. */
function env(string $chave, ?string $padrao = null): ?string
{
    $v = getenv($chave);
    if ($v === false || $v === '') {
        $v = $_ENV[$chave] ?? $_SERVER[$chave] ?? null;
    }
    return ($v === null || $v === '') ? $padrao : (string) $v;
}

(function (): void {
    $arquivo = dirname(__DIR__) . '/.env';
    if (!is_file($arquivo)) {
        return;
    }
    foreach (file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $linha) {
        $linha = trim($linha);
        if ($linha === '' || $linha[0] === '#' || strpos($linha, '=') === false) {
            continue;
        }
        [$k, $v] = explode('=', $linha, 2);
        $k = trim($k);
        $v = trim($v, " \t\"'");
        if (getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
})();
