<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/views/publico.php';
try {
    $cfg = config_todas();
    $vereadores = vereadores_da_tela('noticias');
    $noticias = noticias_ativas();
} catch (Throwable $ex) {
    error_log('tela-noticias: ' . $ex->getMessage());
    http_response_code(503);
    exit('Tela temporariamente indisponível.');
}
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
tela_inicio('Câmara Municipal de Alvorada — Notícias', $cfg, 'noticias');
tela_vereadores($vereadores, 8);
tela_seta(true);
tela_noticias($noticias, $cfg);
tela_fim($cfg);
