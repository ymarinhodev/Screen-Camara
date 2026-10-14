<?php
declare(strict_types=1);

/** Peças compartilhadas pelas duas telas das TVs. Layout e classes vêm das telas originais. */

function tela_inicio(string $titulo, array $cfg): void
{
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($titulo) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Montserrat:wght@300;400;500;700&family=Roboto+Condensed:wght@700&display=swap" rel="stylesheet">
    <link href="/assets/css/bootstrap.css" rel="stylesheet">
    <link href="/assets/css/owl.carousel.min.css" rel="stylesheet">
    <link href="/assets/css/owl.theme.default.min.css" rel="stylesheet">
    <link href="/assets/css/animacao.css" rel="stylesheet">
    <link href="/assets/css/estilos.css" rel="stylesheet">
    <link href="/assets/css/responsivo.css" rel="stylesheet">
    <link href="/assets/css/telas.css" rel="stylesheet">
    <link href="/assets/css/tv-portrait.css" rel="stylesheet">
</head>
<body class="tv-portrait">
    <header class="dontPrint">
        <section class="bg-primary">
            <div class="container-fluid">
                <div id="brasao" class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 text-center">
                        <div class="padding-logo">
                            <img src="/assets/img/logo.png" alt="Câmara de Alvorada">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </header>
    <header class="dontPrint">
        <section class="bg-golden">
            <div class="container-fluid">
                <div id="brasao" class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 text-center">
                        <p style="color:#001b45; font-weight: bold; font-size:50px; padding-top: 10px;"><?= e($cfg['cabecalho_titulo']) ?></p>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 text-center">
                        <p style="color:#001b45; font-weight: light; font-size:40px; margin-top: -40px;"><?= e($cfg['cabecalho_subtitulo']) ?></p>
                    </div>
                </div>
            </div>
        </section>
    </header>
    <div id="conteudo">
        <div class="row">
            <div class="content col-12">
<?php
}

/** Vereadores em blocos de 8 (4 linhas x 2 colunas), como nas telas originais. */
function tela_vereadores(array $vereadores): void
{
    ?>
<section style="margin: 50px;">
    <div class="row nomargin">
        <div class="col-12">
<?php foreach (array_chunk($vereadores, 8) as $grupo): ?>
            <div class="item">
                <div class="vereadores-grid">
<?php foreach ($grupo as $v): ?>
                    <div class="vereador-grid-item">
                        <div class="card-vereador">
                            <fieldset class="fieldset-vereador">
                                <img class="foto-vereador" src="<?= e(($v['foto_id'] ? img_url((int) $v['foto_id']) : (!empty($v['foto_url']) ? $v['foto_url'] : '/assets/img/sem-foto.svg'))) ?>" alt="<?= e($v['nome']) ?>">
                                <p class="nome-vereador"><?= e($v['nome']) ?></p>
                                <div id="canto-branco"></div>
                                <div id="canto-azul"></div>
<?php if ($v['logo_id']): ?>
                                <img class="partido_vereador" src="<?= e(img_url((int) $v['logo_id'])) ?>" alt="<?= e($v['partido']) ?>">
<?php endif; ?>
                                <p class="gabinete-vereador"><?= e($v['gabinete']) ?></p>
                            </fieldset>
                        </div>
                    </div>
<?php endforeach; ?>
                </div>
            </div>
<?php endforeach; ?>
        </div>
    </div>
</section>
<?php
}

function tela_seta(bool $invertida = false): void
{
    ?>
<div>
    <img src="/assets/img/seta.png" width="250" alt=""<?= $invertida ? ' style="transform: rotate(180deg)"' : '' ?>>
</div>
<?php
}

function tela_fim(array $cfg, bool $comCarrossel = false, int $totalNoticias = 0): void
{
    $min = max(1, (int) $cfg['recarregar_minutos']);
    ?>
            </div>
        </div>
    </div>
    <footer class="container-fluid dontPrint" style="border: none;">
        <div class="row margin30T">
            <div class="col-12 col-lg-12">
                <div class="row">
                    <div class="col-6 text-left">
                        <div id="brasao" class="row nomargin">
                            <div class="col-12 nopadding text-center">
                                <div class="padding-logo">
                                    <img src="/assets/img/logo.png" alt="Câmara de Alvorada">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 text-right">
                        <div id="brasao" class="row nomargin">
                            <div class="col-12 nopadding text-center">
                                <p style="font-size: 24px; font-weight: bold; margin-top: 70px;"><?= e($cfg['horario']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
<?php if ($comCarrossel): ?>
    <script src="/assets/js/jquery.js"></script>
    <script src="/assets/js/owl.carousel.min.js"></script>
    <script>
        jQuery(function ($) {
            $("#owlc0").owlCarousel({
                items: <?= $totalNoticias >= 2 ? 2 : 1 ?>,
                dots: true,
                smartSpeed: 1000,
                autoplay: <?= $totalNoticias > 1 ? 'true' : 'false' ?>,
                autoplayTimeout: 2000,
                autoplayHoverPause: true,
                loop: <?= $totalNoticias > 2 ? 'true' : 'false' ?>,
                margin: 0
            });
        });
    </script>
<?php endif; ?>
    <script>
        // A TV recarrega sozinha para mostrar o que a equipe alterou no painel.
        setTimeout(function () { location.reload(); }, <?= $min * 60000 ?>);
    </script>
</body>
</html>
<?php
}
