<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/views/publico.php';

try {
    $cfg        = config_todas();
    $vereadores = vereadores_da_tela('qrcode');
    $qrcodes    = qrcodes_ativos();
} catch (Throwable $ex) {
    http_response_code(503);
    echo 'Tela temporariamente indisponível.';
    error_log('tela-qrcode: ' . $ex->getMessage());
    exit;
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache');

tela_inicio('Câmara Municipal - Alvorada/RS - TV2', $cfg);
tela_vereadores($vereadores);
tela_seta();
?>
<section class="container-fluid margin80T bg-secondary padding80T padding80B">
    <div class="row">
        <div class="col-12">
            <div class="titulo"><h2><?= e($cfg['qrcodes_titulo']) ?></h2><hr></div>
        </div>
    </div>
    <div class="row nomargin">
        <div class="col-12">
            <div class="qrcodes-grid">
                <fieldset class="fieldset-qrcodes">
<?php foreach ($qrcodes as $q): ?>
                    <div class="qrcode-item">
                        <img src="<?= e(qr_url($q['link'])) ?>" width="250" height="250" alt="QR Code: <?= e($q['titulo']) ?>">
                        <h2><?= e($q['titulo']) ?></h2>
                    </div>
<?php endforeach; ?>
                </fieldset>
            </div>
        </div>
    </div>
</section>
<?php
tela_fim($cfg);
