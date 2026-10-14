<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/views/publico.php';

try {
    $cfg        = config_todas();
    $vereadores = vereadores_da_tela('noticias');
    $noticias   = noticias_ativas();
} catch (Throwable $ex) {
    http_response_code(503);
    echo 'Tela temporariamente indisponível.';
    error_log('tela-noticias: ' . $ex->getMessage());
    exit;
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache');

tela_inicio('Câmara Municipal - Alvorada/RS - TV1', $cfg);
tela_vereadores($vereadores);
tela_seta(true);
?>
<section class="container-fluid margin80T bg-secondary padding80T padding80B">
    <div class="row">
        <div class="col-12">
            <div class="titulo"><h2><?= e($cfg['noticias_titulo']) ?></h2><hr></div>
        </div>
    </div>
    <div class="row nomargin">
        <div class="col-12">
            <div id="owlc0" class="owl-carousel">
<?php foreach ($noticias as $n): ?>
                <div class="tv-noticia">
                    <img src="<?= e(img_url($n['imagem_id'] ? (int) $n['imagem_id'] : null, '/assets/img/logo.png')) ?>" alt="<?= e($n['titulo']) ?>">
                    <h2 class="nomargin"><?= e($n['titulo']) ?></h2>
                </div>
<?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php
tela_fim($cfg, true, count($noticias));
