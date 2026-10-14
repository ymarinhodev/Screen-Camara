<?php
declare(strict_types=1);

/**
 * Telas verticais oficiais - arte 720 x 1280 (9:16).
 * O conteúdo é dimensionado proporcionalmente no navegador, sem distorções.
 * Dados vêm exclusivamente do painel/Neon; imagens são URLs ou Base64 no banco.
 */
function tv_foto(array $v): string {
    if (!empty($v['foto_id'])) return img_url((int)$v['foto_id']);
    if (!empty($v['foto_url']) && link_valido((string)$v['foto_url'])) return (string)$v['foto_url'];
    return '/assets/img/sem-foto.svg';
}
function tv_logo_partido(array $v): string {
    $url = (string)($v['partido_logo_url'] ?? '');
    if ($url !== '' && link_valido($url)) return $url;
    if (!empty($v['partido_logo_id'])) return img_url((int)$v['partido_logo_id']);
    if (!empty($v['logo_id'])) return img_url((int)$v['logo_id']);
    return '';
}
function tv_noticia_foto(array $n): string {
    $url = (string)($n['imagem_url'] ?? '');
    if ($url !== '' && link_valido($url)) return $url;
    return img_url(!empty($n['imagem_id']) ? (int)$n['imagem_id'] : null, '/assets/img/logo.png');
}
function tela_inicio(string $titulo, array $cfg, string $tipo = 'noticias'): void {
    ?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#051e43">
    <title><?= e($titulo) ?></title>
    <link rel="stylesheet" href="/assets/css/tv-design.css?v=20261014-1">
</head>
<body class="tv-body tv-body--<?= e($tipo) ?>">
<div class="tv-stage" id="tv-stage">
    <div class="tv-page">
        <header class="tv-header" aria-label="Câmara Municipal de Alvorada">
            <img src="/assets/img/logo.png" alt="Câmara Municipal de Alvorada" class="tv-header-logo">
        </header>
        <div class="tv-heading">
            <div class="tv-heading-title"><?= e($cfg['cabecalho_titulo']) ?></div>
            <div class="tv-heading-subtitle"><?= e($cfg['cabecalho_subtitulo']) ?></div>
        </div>
        <main class="tv-main" id="conteudo">
<?php
}
function tela_vereadores(array $vereadores, int $porPagina): void {
    $paginas = array_chunk($vereadores, $porPagina);
    if (!$paginas) $paginas = [[]];
    ?>
<section class="tv-people tv-people--<?= $porPagina === 9 ? 'qr' : 'news' ?>" aria-label="Gabinetes dos vereadores" data-tv-people>
<?php foreach ($paginas as $indice => $pagina): ?>
    <div class="tv-people-page<?= $indice === 0 ? ' is-active' : '' ?>" data-tv-people-page<?= $indice !== 0 ? ' hidden' : '' ?> aria-hidden="<?= $indice === 0 ? 'false' : 'true' ?>">
        <div class="tv-people-grid tv-people-grid--<?= $porPagina ?>">
<?php foreach ($pagina as $v):
    $nome=(string)$v['nome'];
    $partido=(string)($v['partido_sigla'] ?? $v['partido'] ?? '');
    $logo=tv_logo_partido($v);
    $gab=(string)($v['gabinete'] ?? '');
?>
            <article class="tv-person" title="<?= e($nome) ?> — <?= e($gab) ?>">
                <div class="tv-person-bg"></div>
                <div class="tv-person-blue"></div>
                <img class="tv-person-photo" src="<?= e(tv_foto($v)) ?>" alt="Foto de <?= e($nome) ?>" onerror="this.onerror=null;this.src='/assets/img/sem-foto.svg'">
                <div class="tv-person-name<?= strlen($nome) > 18 ? ' tv-person-name--long' : '' ?>"><?= e($nome) ?></div>
                <div class="tv-person-party">
<?php if ($logo !== ''): ?>
                    <img src="<?= e($logo) ?>" alt="<?= e($partido) ?>" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                    <span class="tv-party-text" hidden><?= e($partido) ?></span>
<?php elseif ($partido !== ''): ?>
                    <span class="tv-party-text"><?= e($partido) ?></span>
<?php endif; ?>
                </div>
                <div class="tv-person-room"><?= e($gab) ?></div>
            </article>
<?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
</section>
<?php
}
function tela_seta(bool $invertida = false): void {
    ?>
<div class="tv-direction <?= $invertida ? 'tv-direction--right' : 'tv-direction--left' ?>" aria-hidden="true">
    <img src="/assets/img/seta-design.svg" alt="">
</div>
<?php
}
function tela_noticias(array $noticias, array $cfg): void {
    $paginas=array_chunk($noticias, 5);
    if (!$paginas) $paginas=[[]];
    ?>
<section class="tv-news" aria-label="<?= e($cfg['noticias_titulo']) ?>">
    <div class="tv-news-toolbar"><span class="tv-news-tag"><?= e($cfg['noticias_titulo']) ?></span><span class="tv-news-all">Todas</span></div>
    <div class="tv-news-pages" data-tv-news>
<?php foreach ($paginas as $i=>$pagina): ?>
        <div class="tv-news-grid tv-news-grid--count-<?= count($pagina) ?><?= $i===0 ? ' is-active' : '' ?>" data-tv-news-page<?= $i===0 ? '' : ' hidden' ?> aria-hidden="<?= $i===0 ? 'false' : 'true' ?>">
<?php foreach ($pagina as $n): ?>
            <article class="tv-news-item">
                <?php if (!empty($n['link']) && link_valido((string)$n['link'])): ?><a href="<?= e($n['link']) ?>" tabindex="-1" aria-hidden="true"><?php endif; ?>
                    <img src="<?= e(tv_noticia_foto($n)) ?>" alt="" loading="eager" onerror="this.onerror=null;this.src='/assets/img/logo.png'">
                    <div class="tv-news-caption"><?= e($n['titulo']) ?></div>
                <?php if (!empty($n['link']) && link_valido((string)$n['link'])): ?></a><?php endif; ?>
            </article>
<?php endforeach; ?>
<?php if (!$pagina): ?>
            <div class="tv-news-empty">Notícias em atualização</div>
<?php endif; ?>
        </div>
<?php endforeach; ?>
    </div>
</section>
<?php
}
function tela_qrcodes(array $qrcodes, array $cfg): void {
    $paginas=array_chunk($qrcodes,6);
    if (!$paginas) $paginas=[[]];
    ?>
<section class="tv-qr-panel" aria-label="<?= e($cfg['qrcodes_titulo']) ?>">
<?php foreach ($paginas as $i=>$pagina): ?>
    <div class="tv-qr-grid<?= $i===0 ? ' is-active' : '' ?>" data-tv-qr-page<?= $i===0 ? '' : ' hidden' ?> aria-hidden="<?= $i===0 ? 'false' : 'true' ?>">
<?php foreach ($pagina as $q): ?>
        <div class="tv-qr-item">
            <img src="<?= e(qr_url((string)$q['link'],320)) ?>" alt="QR Code para <?= e($q['titulo']) ?>" loading="eager">
            <div class="tv-qr-label"><?= e($q['titulo']) ?></div>
        </div>
<?php endforeach; ?>
    </div>
<?php endforeach; ?>
</section>
<?php
}
function tela_fim(array $cfg): void {
    $minutes=max(1,min(120,(int)$cfg['recarregar_minutos']));
    $horario=(string)$cfg['horario'];
    $partes=explode(':',$horario,2);
    ?>
        </main>
        <footer class="tv-footer">
            <div class="tv-footer-brand">
                <span class="tv-footer-crest"><img src="/assets/img/logo.png" alt=""></span>
                <span class="tv-footer-brand-text">CÂMARA<br>MUNICIPAL</span>
            </div>
            <div class="tv-footer-hours">
                <?= e(trim($partes[0])) ?><?= count($partes)>1 ? ':' : '' ?>
                <?php if(count($partes)>1): ?><br><span><?= e(trim($partes[1])) ?></span><?php endif; ?>
            </div>
        </footer>
    </div>
</div>
<script src="/assets/js/tv-design.js?v=20261014-1"></script>
<script>window.TV_REFRESH_MS = <?= $minutes * 60000 ?>;</script>
</body></html>
<?php
}
