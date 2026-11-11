<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/migracoes.php';

/**
 * Executa as migrações pela URL:
 *   https://SEU-SITE.vercel.app/migrar?token=SEU_TOKEN          (roda se ainda não foi aplicada)
 *   https://SEU-SITE.vercel.app/migrar?token=SEU_TOKEN&forcar=1 (roda de novo mesmo se já aplicada)
 * Exige a variável de ambiente MIGRACAO_TOKEN configurada na Vercel.
 */
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$esperado = (string) env('MIGRACAO_TOKEN', '');
$enviado  = (string) ($_GET['token'] ?? '');
if ($esperado === '' || !hash_equals($esperado, $enviado)) {
    http_response_code(403);
    exit("Acesso negado. Configure MIGRACAO_TOKEN na Vercel e use ?token=...\n");
}

$res = executar_migracoes(!empty($_GET['forcar']), false);
foreach ($res as $arquivo => $status) {
    echo $arquivo . ': ' . $status . "\n";
}
if (!$res) {
    echo "Nenhum arquivo de migração encontrado.\n";
}
