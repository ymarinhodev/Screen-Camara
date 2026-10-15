<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit;
}

try {
    $st = db()->prepare('SELECT mime, dados, base64_data, arquivo_url FROM imagens WHERE id = :id');
    $st->execute([':id' => $id]);
    $linha = $st->fetch();
} catch (Throwable $ex) {
    http_response_code(503);
    error_log('img: ' . $ex->getMessage());
    exit;
}

if (!$linha) {
    http_response_code(404);
    exit;
}

if (!empty($linha['arquivo_url'])) {
    $destino = (string) $linha['arquivo_url'];
    if (!(str_starts_with($destino, '/assets/imagens/') || (filter_var($destino, FILTER_VALIDATE_URL) && strtolower((string) parse_url($destino, PHP_URL_SCHEME)) === 'https'))) {
        http_response_code(404);
        exit;
    }
    header('Cache-Control: public, max-age=300');
    header('Location: ' . $destino, true, 302);
    exit;
}

$dados = !empty($linha['base64_data']) ? base64_decode($linha['base64_data'],true) : ler_bytea($linha['dados']);
if (!is_string($dados) || $dados==='') {http_response_code(404);exit;}
$etag  = '"' . md5($dados) . '"';

header('Content-Type: ' . $linha['mime']);
header('X-Content-Type-Options: nosniff');
// Ao trocar uma foto, o painel grava uma imagem nova (outro id), então o cache pode ser longo.
header('Cache-Control: public, max-age=31536000, immutable');
header('ETag: ' . $etag);

if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
    http_response_code(304);
    exit;
}
header('Content-Length: ' . strlen($dados));
echo $dados;
