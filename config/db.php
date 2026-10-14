<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

/** Conexão PDO com o PostgreSQL do Neon (DATABASE_URL). */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $url = env('DATABASE_URL');
    if (!$url) {
        throw new RuntimeException('DATABASE_URL não configurada.');
    }
    $p = parse_url($url);
    if ($p === false || empty($p['host']) || empty($p['user'])) {
        throw new RuntimeException('DATABASE_URL inválida.');
    }

    parse_str($p['query'] ?? '', $q);
    $host    = $p['host'];
    $porta   = (int) ($p['port'] ?? 5432);
    $banco   = ltrim($p['path'] ?? '/neondb', '/');
    $usuario = urldecode($p['user']);
    $senha   = urldecode($p['pass'] ?? '');
    $ssl     = $q['sslmode'] ?? 'require';

    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 10,
    ];
    $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s;sslmode=%s', $host, $porta, $banco, $ssl);

    try {
        $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
    } catch (PDOException $e) {
        // libpq antigo sem SNI: o Neon pede o endpoint no parâmetro "options" (não vale para host -pooler).
        $semPooler = strpos($host, '-pooler') === false;
        if ($semPooler && stripos($e->getMessage(), 'endpoint') !== false) {
            $endpoint = explode('.', $host)[0];
            $pdo = new PDO($dsn . ";options='endpoint=" . $endpoint . "'", $usuario, $senha, $opcoes);
        } else {
            throw $e;
        }
    }
    return $pdo;
}
