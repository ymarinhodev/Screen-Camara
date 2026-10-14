<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/auth.php';
require_once dirname(__DIR__) . '/config/importar_noticia.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['ok' => false, 'msg' => 'Método não permitido.'], 405);
}

try {
    $u = auth_exigir(true);
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? (string) ($_POST['csrf'] ?? '');
    if (!csrf_valido($u, $token)) {
        json_out(['ok' => false, 'msg' => 'Sessão inválida. Recarregue a página.'], 403);
    }
    $pdo = db();
    $acao = (string) ($_POST['acao'] ?? '');

    switch ($acao) {
        case 'vereador_salvar':   json_out(vereador_salvar($pdo));
        case 'vereador_novo':     json_out(vereador_novo($pdo));
        case 'vereador_excluir':  json_out(vereador_excluir($pdo));
        case 'noticia_importar': json_out(noticia_importar());
        case 'noticia_salvar':    json_out(noticia_salvar($pdo));
        case 'noticia_nova':      json_out(noticia_nova($pdo));
        case 'noticia_excluir':   json_out(noticia_excluir($pdo));
        case 'qr_salvar':         json_out(qr_salvar($pdo));
        case 'qr_novo':           json_out(qr_novo($pdo));
        case 'qr_excluir':        json_out(qr_excluir($pdo));
        case 'config_salvar':     json_out(config_salvar($pdo));
        case 'usuario_novo':      json_out(usuario_novo($pdo));
        case 'usuario_excluir':   json_out(usuario_excluir($pdo, $u));
        case 'senha_alterar':     json_out(senha_alterar($pdo, $u));
        default:                  json_out(['ok' => false, 'msg' => 'Ação desconhecida.'], 400);
    }
} catch (RuntimeException $ex) {
    json_out(['ok' => false, 'msg' => $ex->getMessage()], 422);
} catch (Throwable $ex) {
    error_log('acoes: ' . get_class($ex) . ' ' . $ex->getMessage() . ' at ' . basename($ex->getFile()) . ':' . $ex->getLine());
    json_out(['ok' => false, 'msg' => 'Erro interno. Tente de novo em instantes.'], 500);
}

/* ---------- utilitários ---------- */

function texto(string $campo, int $max, bool $obrigatorio = true, string $rotulo = ''): string
{
    $v = trim((string) ($_POST[$campo] ?? ''));
    $rotulo = $rotulo !== '' ? $rotulo : $campo;
    if ($obrigatorio && $v === '') {
        throw new RuntimeException("Preencha o campo {$rotulo}.");
    }
    if (mb_strlen($v) > $max) {
        throw new RuntimeException("O campo {$rotulo} aceita até {$max} caracteres.");
    }
    return $v;
}

function inteiro(string $campo, int $padrao = 0): int
{
    return isset($_POST[$campo]) && is_numeric($_POST[$campo]) ? (int) $_POST[$campo] : $padrao;
}

function marcado(string $campo): bool
{
    return !empty($_POST[$campo]) && $_POST[$campo] !== '0';
}

function arquivo_enviado(string $campo): ?array
{
    $f = $_FILES[$campo] ?? null;
    return ($f && ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) ? $f : null;
}

function sql(PDO $pdo, string $consulta, array $params = []): PDOStatement
{
    $st = $pdo->prepare($consulta);
    foreach ($params as $nome => $valor) {
        $tipo = is_bool($valor) ? PDO::PARAM_BOOL : (is_int($valor) ? PDO::PARAM_INT : (is_null($valor) ? PDO::PARAM_NULL : PDO::PARAM_STR));
        $st->bindValue(':' . $nome, $valor, $tipo);
    }
    $st->execute();
    return $st;
}

function ou_404(PDO $pdo, string $tabela, int $id): array
{
    $linha = sql($pdo, "SELECT * FROM {$tabela} WHERE id = :id", ['id' => $id])->fetch();
    if (!$linha) {
        throw new RuntimeException('Registro não encontrado. Recarregue a página.');
    }
    return $linha;
}

function tela_valida(): string
{
    $t = (string) ($_POST['tela'] ?? '');
    if (!array_key_exists($t, TELAS)) {
        throw new RuntimeException('Tela inválida.');
    }
    return $t;
}

function link_ou_erro(string $link, bool $obrigatorio): string
{
    if ($link === '' && !$obrigatorio) {
        return '';
    }
    if (!link_valido($link)) {
        throw new RuntimeException('Informe um link completo, começando com http:// ou https://');
    }
    return $link;
}

/* ---------- vereadores ---------- */

function vereador_salvar(PDO $pdo): array
{
    $v = ou_404($pdo, 'vereadores', inteiro('id'));
    $nome = texto('nome', 80, true, 'Nome');
    $gab = texto('gabinete', 20, false, 'Gabinete');
    $partidoId = inteiro('partido_id');
    $partido = '';
    if ($partidoId) {
        $partido = (string)sql($pdo, 'SELECT sigla FROM partidos WHERE id=:id', ['id'=>$partidoId])->fetchColumn();
        if ($partido === '') throw new RuntimeException('Selecione um partido válido.');
    }
    $tela = tela_valida();
    $ordem = inteiro('ordem');

    $fotoId = $v['foto_id'] ? (int) $v['foto_id'] : null;
    $logoId = $v['logo_id'] ? (int) $v['logo_id'] : null;
    $antigas = [];
    if ($f = arquivo_enviado('foto')) {
        $antigas[] = $fotoId;
        $fotoId = salvar_imagem_upload($f);
    }
    if ($f = arquivo_enviado('logo')) {
        $antigas[] = $logoId;
        $logoId = salvar_imagem_upload($f);
    }

    sql($pdo, 'UPDATE vereadores SET nome=:n, gabinete=:g, partido=:p, partido_id=:pid, tela=:t, ordem=:o, foto_id=:f, logo_id=:l, atualizado_em=now() WHERE id=:id',
        ['n' => $nome, 'g' => $gab, 'p' => $partido, 'pid' => $partidoId ?: null, 't' => $tela, 'o' => $ordem, 'f' => $fotoId, 'l' => $logoId, 'id' => (int) $v['id']]);
    foreach ($antigas as $a) {
        limpar_imagem_orfa($a);
    }

    return ['ok' => true, 'msg' => 'Vereador salvo.', 'foto' => img_url($fotoId), 'logo' => $logoId ? img_url($logoId) : '', 'recarregar' => $tela !== $v['tela']];
}

function vereador_novo(PDO $pdo): array
{
    $tela = tela_valida();
    $ordem = (int) sql($pdo, 'SELECT COALESCE(MAX(ordem),0)+1 FROM vereadores WHERE tela = :t', ['t' => $tela])->fetchColumn();
    sql($pdo, "INSERT INTO vereadores (tela, ordem, nome) VALUES (:t, :o, 'Novo vereador')", ['t' => $tela, 'o' => $ordem]);
    return ['ok' => true, 'msg' => 'Vereador adicionado.', 'recarregar' => true];
}

function vereador_excluir(PDO $pdo): array
{
    $v = ou_404($pdo, 'vereadores', inteiro('id'));
    sql($pdo, 'DELETE FROM vereadores WHERE id = :id', ['id' => (int) $v['id']]);
    limpar_imagem_orfa($v['foto_id'] ? (int) $v['foto_id'] : null);
    limpar_imagem_orfa($v['logo_id'] ? (int) $v['logo_id'] : null);
    return ['ok' => true, 'msg' => 'Vereador removido.', 'recarregar' => true];
}

/* ---------- notícias ---------- */

function noticia_importar(): array {
    $link = link_ou_erro(texto('link', 2000, true, 'Link'), true);
    return ['ok'=>true, 'msg'=>'Imagens encontradas. Selecione uma fotografia e salve.', 'dados'=>importar_noticia_oficial($link)];
}

function noticia_salvar(PDO $pdo): array
{
    $n = ou_404($pdo, 'noticias', inteiro('id'));
    $titulo = texto('titulo', 220, true, 'Título');
    $link = link_ou_erro(texto('link', 2000, false, 'Link'), false);
    $imagemUrl = texto('imagem_url', 2000, false, 'URL da imagem');
    if ($imagemUrl !== '') {
        $u = parse_url($imagemUrl);
        if (!$u || ($u['scheme'] ?? '') !== 'https' || !in_array(strtolower($u['host'] ?? ''), ['www.camara-alvorada.rs.gov.br','camara-alvorada.rs.gov.br'],true)) {
            throw new RuntimeException('A fotografia externa deve pertencer ao site oficial da Câmara.');
        }
    }
    $imagemId = $n['imagem_id'] ? (int)$n['imagem_id'] : null;
    $antiga = null;
    if ($f = arquivo_enviado('imagem')) {
        $antiga = $imagemId;
        $imagemId = salvar_imagem_upload($f);
        $imagemUrl = ''; // Upload manual tem prioridade quando enviado.
    }
    sql($pdo, 'UPDATE noticias SET titulo=:t, link=:l, ordem=:o, ativo=:a, imagem_id=:i, imagem_url=:url, atualizado_em=now() WHERE id=:id',
        ['t'=>$titulo, 'l'=>$link, 'o'=>inteiro('ordem'), 'a'=>marcado('ativo'), 'i'=>$imagemId, 'url'=>$imagemUrl, 'id'=>(int)$n['id']]);
    limpar_imagem_orfa($antiga);
    return ['ok'=>true, 'msg'=>'Notícia salva.', 'foto'=>$imagemUrl ?: img_url($imagemId, '/assets/img/logo.png')];
}

function noticia_nova(PDO $pdo): array
{
    $ordem = (int) sql($pdo, 'SELECT COALESCE(MAX(ordem),0)+1 FROM noticias')->fetchColumn();
    sql($pdo, "INSERT INTO noticias (ordem, titulo, ativo) VALUES (:o, 'Nova notícia', FALSE)", ['o' => $ordem]);
    return ['ok' => true, 'msg' => 'Notícia criada. Envie a foto e ative para exibir na TV.', 'recarregar' => true];
}

function noticia_excluir(PDO $pdo): array
{
    $n = ou_404($pdo, 'noticias', inteiro('id'));
    sql($pdo, 'DELETE FROM noticias WHERE id = :id', ['id' => (int) $n['id']]);
    limpar_imagem_orfa($n['imagem_id'] ? (int) $n['imagem_id'] : null);
    return ['ok' => true, 'msg' => 'Notícia removida.', 'recarregar' => true];
}

/* ---------- QR Codes ---------- */

function qr_salvar(PDO $pdo): array
{
    $q = ou_404($pdo, 'qrcodes', inteiro('id'));
    $titulo = texto('titulo', 80, true, 'Nome ao lado do QR Code');
    $link = link_ou_erro(texto('link', 2000, true, 'Link'), true);
    sql($pdo, 'UPDATE qrcodes SET titulo=:t, link=:l, ordem=:o, ativo=:a, atualizado_em=now() WHERE id=:id',
        ['t' => $titulo, 'l' => $link, 'o' => inteiro('ordem'), 'a' => marcado('ativo'), 'id' => (int) $q['id']]);
    return ['ok' => true, 'msg' => 'QR Code salvo.'];
}

function qr_novo(PDO $pdo): array
{
    $ordem = (int) sql($pdo, 'SELECT COALESCE(MAX(ordem),0)+1 FROM qrcodes')->fetchColumn();
    sql($pdo, "INSERT INTO qrcodes (ordem, titulo, link, ativo) VALUES (:o, 'Novo QR Code', 'https://www.camara-alvorada.rs.gov.br/', FALSE)", ['o' => $ordem]);
    return ['ok' => true, 'msg' => 'QR Code criado. Ajuste o nome e o link e ative para exibir.', 'recarregar' => true];
}

function qr_excluir(PDO $pdo): array
{
    $q = ou_404($pdo, 'qrcodes', inteiro('id'));
    sql($pdo, 'DELETE FROM qrcodes WHERE id = :id', ['id' => (int) $q['id']]);
    return ['ok' => true, 'msg' => 'QR Code removido.', 'recarregar' => true];
}

/* ---------- textos das telas ---------- */

function config_salvar(PDO $pdo): array
{
    foreach (array_keys(CONFIG_PADRAO) as $chave) {
        if ($chave === 'recarregar_minutos') {
            $valor = (string) max(1, min(120, inteiro($chave, 5)));
        } else {
            $valor = texto($chave, 120, true, $chave);
        }
        sql($pdo, 'INSERT INTO configuracoes (chave, valor) VALUES (:c, :v) ON CONFLICT (chave) DO UPDATE SET valor = EXCLUDED.valor', ['c' => $chave, 'v' => $valor]);
    }
    return ['ok' => true, 'msg' => 'Textos salvos. As TVs atualizam no próximo recarregamento.'];
}

/* ---------- equipe ---------- */

function usuario_novo(PDO $pdo): array
{
    $nome = texto('nome', 60, true, 'Nome');
    $email = strtolower(texto('email', 120, true, 'E-mail'));
    $senha = (string) ($_POST['senha'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Informe um e-mail válido.');
    }
    if (strlen($senha) < 8) {
        throw new RuntimeException('A senha precisa ter pelo menos 8 caracteres.');
    }
    $existe = sql($pdo, 'SELECT 1 FROM usuarios WHERE lower(email) = :e OR lower(nome) = lower(:n)', ['e' => $email, 'n' => $nome])->fetchColumn();
    if ($existe) {
        throw new RuntimeException('Já existe um usuário com esse nome ou e-mail.');
    }
    sql($pdo, 'INSERT INTO usuarios (nome, email, senha_hash) VALUES (:n, :e, :h)', ['n' => $nome, 'e' => $email, 'h' => password_hash($senha, PASSWORD_DEFAULT)]);
    return ['ok' => true, 'msg' => 'Usuário criado.', 'recarregar' => true];
}

function usuario_excluir(PDO $pdo, array $eu): array
{
    $id = inteiro('id');
    if ($id === $eu['uid']) {
        throw new RuntimeException('Você não pode remover o próprio acesso.');
    }
    sql($pdo, 'DELETE FROM usuarios WHERE id = :id', ['id' => $id]);
    return ['ok' => true, 'msg' => 'Usuário removido.', 'recarregar' => true];
}

function senha_alterar(PDO $pdo, array $eu): array
{
    $atual = (string) ($_POST['senha_atual'] ?? '');
    $nova = (string) ($_POST['senha_nova'] ?? '');
    if (strlen($nova) < 8) {
        throw new RuntimeException('A nova senha precisa ter pelo menos 8 caracteres.');
    }
    $hash = sql($pdo, 'SELECT senha_hash FROM usuarios WHERE id = :id', ['id' => $eu['uid']])->fetchColumn();
    if (!$hash || !password_verify($atual, (string) $hash)) {
        throw new RuntimeException('A senha atual não confere.');
    }
    sql($pdo, 'UPDATE usuarios SET senha_hash = :h WHERE id = :id', ['h' => password_hash($nova, PASSWORD_DEFAULT), 'id' => $eu['uid']]);
    return ['ok' => true, 'msg' => 'Senha alterada.'];
}
