<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/views/publico.php';
try {
    $cfg = config_todas();
    $vereadores = vereadores_da_tela('qrcode');
    $qrcodes = qrcodes_ativos();
} catch (Throwable $ex) {
    error_log('tela-qrcode: ' . $ex->getMessage());
    http_response_code(503);
    exit('Tela temporariamente indisponível.');
}
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
tela_inicio('Câmara Municipal de Alvorada — QR Codes', $cfg, 'qrcode');
tela_vereadores($vereadores, 9);
tela_seta(false);
tela_qrcodes($qrcodes,$cfg);
tela_fim($cfg);
