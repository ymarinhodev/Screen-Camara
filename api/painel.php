<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/auth.php';

try {
    $eu   = auth_exigir();
    $lado = ($_GET['lado'] ?? '') === 'esquerdo' ? 'esquerdo' : (($_GET['lado'] ?? '') === 'direito' ? 'direito' : 'ambos');
    $csrf = csrf_token($eu);
    $cfg  = config_todas();

    $vereadores = [
        'noticias' => vereadores_da_tela('noticias'),
        'qrcode'   => vereadores_da_tela('qrcode'),
    ];
    $noticias = noticias_ativas(false);
    $qrcodes  = qrcodes_ativos(false);
    $partidos = db()->query('SELECT * FROM partidos ORDER BY sigla')->fetchAll();
    $equipe   = db()->query('SELECT id, nome, email FROM usuarios WHERE ativo ORDER BY nome')->fetchAll();
} catch (Throwable $ex) {
    error_log('painel: ' . $ex->getMessage());
    http_response_code(500);
    exit('Não foi possível carregar o painel agora. Tente novamente em instantes.');
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

function campo_foto(string $nome, string $classe, ?string $url, string $rotulo, int $lado): void
{
    ?>
<label class="troca">
    <img class="miniatura <?= e($classe) ?>" src="<?= e($url ?? '') ?>" alt="">
    <input type="file" name="<?= e($nome) ?>" accept="image/jpeg,image/png,image/webp,image/gif" data-lado="<?= $lado ?>">
    <small><?= e($rotulo) ?></small>
</label>
<?php
}

function bloco_vereador(array $v, array $partidos): void
{
    ?>
<form class="cartao item-vereador" data-salvar="vereador_salvar" data-excluir="vereador_excluir" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
    <div class="linha-item">
        <?php campo_foto('foto', 'miniatura-foto', ($v['foto_id'] ? img_url((int)$v['foto_id']) : (!empty($v['foto_url']) ? $v['foto_url'] : '/assets/img/sem-foto.svg')), 'Trocar foto', 600); ?>
        <div class="campos">
            <label class="largo">Nome na TV
                <input type="text" name="nome" value="<?= e($v['nome']) ?>" maxlength="80" required>
            </label>
            <label>Gabinete
                <input type="text" name="gabinete" value="<?= e($v['gabinete']) ?>" maxlength="20" placeholder="GAB 10">
            </label>
            <label>Partido
                <select name="partido_id" class="seletor-partido">
                    <option value="">Sem partido</option>
<?php foreach ($partidos as $part): ?>
                    <option value="<?= (int)$part['id'] ?>"<?= (int)($v['partido_id'] ?? 0)===(int)$part['id'] ? ' selected' : '' ?>><?= e($part['sigla']) ?></option>
<?php endforeach; ?>
                </select>
            </label>
            <label>Aparece na
                <select name="tela">
<?php foreach (TELAS as $chave => $nome): ?>
                    <option value="<?= e($chave) ?>"<?= $chave === $v['tela'] ? ' selected' : '' ?>><?= e($nome) ?></option>
<?php endforeach; ?>
                </select>
            </label>
            <label>Posição
                <input type="number" name="ordem" value="<?= (int) $v['ordem'] ?>" min="0" step="1">
            </label>
        </div>
        <div class="miniatura-logo-caixa">
            <img class="miniatura miniatura-logo" src="<?= e(!empty($v['partido_logo_url']) ? $v['partido_logo_url'] : (!empty($v['partido_logo_id']) ? img_url((int)$v['partido_logo_id']) : (!empty($v['logo_id']) ? img_url((int)$v['logo_id']) : '/assets/img/sem-foto.svg'))) ?>" alt="Logo do partido">
            <small>Logotipo do partido selecionado</small>
        </div>
        <div class="acoes">
            <button type="submit" class="botao">Salvar</button>
            <button type="button" class="botao botao-perigo" data-botao-excluir>Excluir</button>
        </div>
    </div>
</form>
<?php
}

function bloco_noticia(array $n): void
{
    ?>
<form class="cartao item-noticia" data-salvar="noticia_salvar" data-excluir="noticia_excluir" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
    <div class="linha-item">
        <?php campo_foto('imagem', 'miniatura-noticia', (!empty($n['imagem_url']) ? $n['imagem_url'] : img_url($n['imagem_id'] ? (int) $n['imagem_id'] : null, '/assets/img/logo.png')), 'Trocar foto da notícia', 1280); ?>
        <div class="campos">
            <label class="largo">Título na TV
                <input type="text" name="titulo" value="<?= e($n['titulo']) ?>" maxlength="220" required>
            </label>
            <label class="largo">Link da matéria (opcional)
                <input type="url" name="link" value="<?= e($n['link']) ?>" maxlength="2000" placeholder="https://">
            </label>
            <div class="importacao-noticia largo">
                <button type="button" class="botao botao-claro" data-importar-noticia>Buscar fotos da matéria</button>
                <input type="hidden" name="imagem_url" value="<?= e($n['imagem_url'] ?? '') ?>">
                <div class="galeria-importada" aria-live="polite"></div>
                <small>Escolha uma foto da galeria oficial; upload manual continua disponível.</small>
            </div>
            <label>Posição
                <input type="number" name="ordem" value="<?= (int) $n['ordem'] ?>" min="0" step="1">
            </label>
            <label class="marca">
                <input type="checkbox" name="ativo" value="1"<?= $n['ativo'] ? ' checked' : '' ?>> Exibir na TV
            </label>
        </div>
        <div class="acoes">
            <button type="submit" class="botao">Salvar</button>
            <button type="button" class="botao botao-perigo" data-botao-excluir>Excluir</button>
        </div>
    </div>
</form>
<?php
}

function bloco_qr(array $q): void
{
    ?>
<form class="cartao item-qr" data-salvar="qr_salvar" data-excluir="qr_excluir">
    <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
    <div class="linha-item">
        <img class="qr-previa" src="<?= e(qr_url($q['link'], 240)) ?>" alt="Prévia do QR Code" width="120" height="120">
        <div class="campos">
            <label class="largo">Nome ao lado do QR Code
                <input type="text" name="titulo" value="<?= e($q['titulo']) ?>" maxlength="80" required>
            </label>
            <label class="largo">Link do QR Code
                <input type="url" name="link" value="<?= e($q['link']) ?>" maxlength="2000" placeholder="https://" required>
            </label>
            <label>Posição
                <input type="number" name="ordem" value="<?= (int) $q['ordem'] ?>" min="0" step="1">
            </label>
            <label class="marca">
                <input type="checkbox" name="ativo" value="1"<?= $q['ativo'] ? ' checked' : '' ?>> Exibir na TV
            </label>
        </div>
        <div class="acoes">
            <button type="submit" class="botao">Salvar</button>
            <button type="button" class="botao botao-perigo" data-botao-excluir>Excluir</button>
        </div>
    </div>
</form>
<?php
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf" content="<?= e($csrf) ?>">
    <title>Painel da Comunicação</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/painel.css" rel="stylesheet">
</head>
<body>
<header class="topo">
    <div class="topo-in">
        <img src="/assets/img/logo.png" alt="Câmara Municipal de Alvorada">
        <h1 class="topo-titulo">Painel da Comunicação — <?= e(ucfirst($lado)) ?></h1>
        <div class="topo-links">
            <a href="/tela-noticias" target="_blank" rel="noopener">Ver tela de notícias</a>
            <a href="/tela-qrcode" target="_blank" rel="noopener">Ver tela de QR Codes</a>
            <span>Olá, <?= e($eu['nome']) ?></span>
            <form method="post" action="/logout.php">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="botao botao-claro">Sair</button>
            </form>
        </div>
    </div>
</header>

<div style="text-align:center;padding:12px"><a href="/painel-direito">Gerenciar lado direito</a> · <a href="/painel-esquerdo">Gerenciar lado esquerdo</a> · <a href="/painel">Gerenciar ambos</a></div>
<nav class="abas" aria-label="Seções do painel">
    <div class="abas-in" role="tablist">
        <button class="aba" role="tab" data-aba="vereadores" aria-selected="true">Vereadores</button>
        <button class="aba" role="tab" data-aba="noticias" aria-selected="false">Notícias</button>
        <button class="aba" role="tab" data-aba="qrcodes" aria-selected="false">QR Codes</button>
        <button class="aba" role="tab" data-aba="textos" aria-selected="false">Textos das telas</button>
        <button class="aba" role="tab" data-aba="equipe" aria-selected="false">Equipe</button>
    </div>
</nav>

<main class="conteudo">

    <section class="painel" id="aba-vereadores">
        <h2>Vereadores</h2>
        <p class="ajuda">Troque nome, gabinete e fotos. Clique na foto ou no logo para enviar outra imagem. As TVs recarregam sozinhas.</p>
<?php foreach (TELAS as $chave => $titulo): if ($lado === 'direito' && $chave !== 'noticias') continue; if ($lado === 'esquerdo' && $chave !== 'qrcode') continue; ?>
        <div class="grupo">
            <h3><?= e($titulo) ?> (<?= count($vereadores[$chave]) ?>)</h3>
            <button type="button" class="botao botao-claro" data-novo="vereador_novo" data-tela="<?= e($chave) ?>">Adicionar vereador</button>
        </div>
<?php if (!$vereadores[$chave]): ?>
        <p class="vazio">Nenhum vereador nesta tela.</p>
<?php endif; ?>
<?php foreach ($vereadores[$chave] as $v): bloco_vereador($v, $partidos); endforeach; ?>
<?php endforeach; ?>
    </section>

    <section class="painel" id="aba-noticias" hidden>
        <div class="grupo" style="margin-top:0">
            <h2 style="margin:0">Notícias da Tela de Notícias</h2>
            <button type="button" class="botao botao-claro" data-novo="noticia_nova">Adicionar notícia</button>
        </div>
        <p class="ajuda">Cada notícia tem foto e título exibidos no carrossel da TV. Desmarque “Exibir na TV” para tirar sem apagar.</p>
<?php if (!$noticias): ?>
        <p class="vazio">Nenhuma notícia cadastrada.</p>
<?php endif; ?>
<?php foreach ($noticias as $n): bloco_noticia($n); endforeach; ?>
    </section>

    <section class="painel" id="aba-qrcodes" hidden>
        <div class="grupo" style="margin-top:0">
            <h2 style="margin:0">QR Codes da Tela de QR Codes</h2>
            <button type="button" class="botao botao-claro" data-novo="qr_novo">Adicionar QR Code</button>
        </div>
        <p class="ajuda">Informe só o nome e o link. O QR Code é gerado automaticamente pela API do qrserver.com.</p>
<?php if (!$qrcodes): ?>
        <p class="vazio">Nenhum QR Code cadastrado.</p>
<?php endif; ?>
<?php foreach ($qrcodes as $q): bloco_qr($q); endforeach; ?>
    </section>

    <section class="painel" id="aba-textos" hidden>
        <h2>Textos das telas</h2>
        <p class="ajuda">Títulos e o horário de funcionamento que aparecem nas duas TVs.</p>
        <form class="cartao" data-salvar="config_salvar">
            <div class="campos">
                <label>Título do topo
                    <input type="text" name="cabecalho_titulo" value="<?= e($cfg['cabecalho_titulo']) ?>" maxlength="120" required>
                </label>
                <label>Subtítulo do topo
                    <input type="text" name="cabecalho_subtitulo" value="<?= e($cfg['cabecalho_subtitulo']) ?>" maxlength="120" required>
                </label>
                <label>Título da seção de notícias
                    <input type="text" name="noticias_titulo" value="<?= e($cfg['noticias_titulo']) ?>" maxlength="120" required>
                </label>
                <label>Título da seção de QR Codes
                    <input type="text" name="qrcodes_titulo" value="<?= e($cfg['qrcodes_titulo']) ?>" maxlength="120" required>
                </label>
                <label class="largo">Horário de funcionamento (rodapé)
                    <input type="text" name="horario" value="<?= e($cfg['horario']) ?>" maxlength="120" required>
                </label>
                <label>Recarregar a TV a cada (minutos)
                    <input type="number" name="recarregar_minutos" value="<?= e($cfg['recarregar_minutos']) ?>" min="1" max="120" step="1" required>
                </label>
            </div>
            <div class="acoes" style="flex-direction:row;margin-top:14px">
                <button type="submit" class="botao">Salvar textos</button>
            </div>
        </form>
    </section>

    <section class="painel" id="aba-equipe" hidden>
        <h2>Equipe</h2>
        <p class="ajuda">Quem pode entrar neste painel.</p>
        <div class="dois">
            <div class="cartao">
                <h3>Usuários com acesso</h3>
<?php foreach ($equipe as $m): ?>
                <div class="usuario">
                    <span><?= e($m['nome']) ?><small><?= e($m['email']) ?></small></span>
<?php if ((int) $m['id'] !== $eu['uid']): ?>
                    <form data-excluir-usuario="<?= (int) $m['id'] ?>">
                        <button type="button" class="botao botao-perigo" data-botao-usuario>Remover</button>
                    </form>
<?php else: ?>
                    <small>você</small>
<?php endif; ?>
                </div>
<?php endforeach; ?>
            </div>
            <div class="cartao">
                <h3>Novo usuário</h3>
                <form data-salvar="usuario_novo" autocomplete="off">
                    <label>Nome <input type="text" name="nome" maxlength="60" required></label>
                    <label>E-mail <input type="email" name="email" maxlength="120" required></label>
                    <label>Senha (mínimo 8 caracteres) <input type="password" name="senha" minlength="8" autocomplete="new-password" required></label>
                    <button type="submit" class="botao">Criar usuário</button>
                </form>
            </div>
            <div class="cartao">
                <h3>Alterar minha senha</h3>
                <form data-salvar="senha_alterar">
                    <label>Senha atual <input type="password" name="senha_atual" autocomplete="current-password" required></label>
                    <label>Nova senha (mínimo 8 caracteres) <input type="password" name="senha_nova" minlength="8" autocomplete="new-password" required></label>
                    <button type="submit" class="botao">Alterar senha</button>
                </form>
            </div>
        </div>
    </section>

</main>

<div id="aviso" role="status" aria-live="polite" hidden></div>
<script src="/assets/js/painel.js"></script>
</body>
</html>
