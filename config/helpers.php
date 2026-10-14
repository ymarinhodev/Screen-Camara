<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

const TELAS = ['noticias' => 'Tela de Notícias', 'qrcode' => 'Tela de QR Codes'];

/** Textos editáveis das telas e seus valores padrão. */
const CONFIG_PADRAO = [
    'cabecalho_titulo'    => 'GABINETES',
    'cabecalho_subtitulo' => 'Localize seu Vereador',
    'noticias_titulo'     => 'Notícias',
    'qrcodes_titulo'      => 'QR CODES',
    'horario'             => 'HORÁRIO DE FUNCIONAMENTO: das 07h às 19h',
    'recarregar_minutos'  => '5',
];

function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_out(array $dados, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function redirect(string $url): never
{
    header('Location: ' . $url, true, 302);
    exit;
}

function img_url(?int $id, string $padrao = '/assets/img/sem-foto.svg'): string
{
    return $id ? '/img.php?id=' . $id : $padrao;
}

/** URL da imagem do QR Code gerada pela API do qrserver.com (a mesma do Gerador-QRCode). */
function qr_url(string $link, int $tamanho = 500): string
{
    return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $tamanho . 'x' . $tamanho
        . '&margin=10&format=png&data=' . rawurlencode($link);
}

function link_valido(string $link): bool
{
    if ($link === '' || strlen($link) > 2000) {
        return false;
    }
    $partes = parse_url($link);
    return $partes !== false
        && in_array(strtolower($partes['scheme'] ?? ''), ['http', 'https'], true)
        && !empty($partes['host']);
}

/** Lê o conteúdo de uma coluna BYTEA (o pdo_pgsql devolve um stream). */
function ler_bytea($valor): string
{
    return is_resource($valor) ? (string) stream_get_contents($valor) : (string) $valor;
}

/** Grava um arquivo enviado (ou um caminho local) na tabela imagens e devolve o id. */
function salvar_imagem_arquivo(string $caminho, int $limite = 3500000): int
{
    $tamanho = (int) @filesize($caminho);
    if ($tamanho <= 0 || $tamanho > $limite) {
        throw new RuntimeException('A imagem deve ter até 3,5 MB.');
    }
    $info = @getimagesize($caminho);
    $mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!$info || !in_array($info['mime'], $mimes, true)) {
        throw new RuntimeException('Formato inválido. Envie JPG, PNG, WEBP ou GIF.');
    }
    $bytes=file_get_contents($caminho);
    if ($bytes===false) throw new RuntimeException('Falha ao ler imagem.');
    $st=db()->prepare('INSERT INTO imagens (mime, base64_data, tamanho) VALUES (:m,:d,:t) RETURNING id');
    $st->execute([':m'=>$info['mime'],':d'=>base64_encode($bytes),':t'=>$tamanho]);
    return (int)$st->fetchColumn();
}

function salvar_imagem_upload(array $arquivo): int
{
    if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Não foi possível receber a imagem. Tente de novo.');
    }
    return salvar_imagem_arquivo($arquivo['tmp_name']);
}

/** Remove a imagem se nada mais usa ela (logos de partido podem ser compartilhados). */
function limpar_imagem_orfa(?int $id): void
{
    if (!$id) {
        return;
    }
    $st = db()->prepare(
        'DELETE FROM imagens WHERE id = :a
           AND NOT EXISTS (SELECT 1 FROM vereadores WHERE foto_id = :b OR logo_id = :c)
           AND NOT EXISTS (SELECT 1 FROM noticias WHERE imagem_id = :d)
           AND NOT EXISTS (SELECT 1 FROM partidos WHERE logo_id = :e)'
    );
    $st->execute([':a' => $id, ':b' => $id, ':c' => $id, ':d' => $id, ':e' => $id]);
}

function config_todas(): array
{
    $cfg = CONFIG_PADRAO;
    foreach (db()->query('SELECT chave, valor FROM configuracoes') as $linha) {
        if (array_key_exists($linha['chave'], $cfg)) {
            $cfg[$linha['chave']] = $linha['valor'];
        }
    }
    return $cfg;
}

function vereadores_da_tela(string $tela): array
{
    $st = db()->prepare('SELECT v.*, p.sigla AS partido_sigla, p.logo_url AS partido_logo_url, p.logo_id AS partido_logo_id FROM vereadores v LEFT JOIN partidos p ON p.id=v.partido_id WHERE v.tela = :t ORDER BY v.ordem, v.id');
    $st->execute([':t' => $tela]);
    return $st->fetchAll();
}

function noticias_ativas(bool $somenteAtivas = true): array
{
    $sql = 'SELECT * FROM noticias' . ($somenteAtivas ? ' WHERE ativo' : '') . ' ORDER BY ordem, id';
    return db()->query($sql)->fetchAll();
}

function qrcodes_ativos(bool $somenteAtivos = true): array
{
    $sql = 'SELECT * FROM qrcodes' . ($somenteAtivos ? ' WHERE ativo' : '') . ' ORDER BY ordem, id';
    return db()->query($sql)->fetchAll();
}
