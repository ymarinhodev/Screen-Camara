<?php
declare(strict_types=1);
/** Busca exclusivamente matérias oficiais; não armazena a fotografia no banco. */
function importar_noticia_oficial(string $url): array {
    $parts = parse_url($url);
    if (!$parts || strtolower($parts['scheme'] ?? '') !== 'https' || strtolower($parts['host'] ?? '') !== 'www.camara-alvorada.rs.gov.br' || !preg_match('~^/noticia/view/[0-9]+/?$~', $parts['path'] ?? '')) {
        throw new RuntimeException('Use um link HTTPS de notícia do site oficial da Câmara.');
    }
    if (!function_exists('curl_init') || !class_exists('DOMDocument')) {
        throw new RuntimeException('Servidor sem extensões cURL/DOM necessárias à importação.');
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>false, CURLOPT_CONNECTTIMEOUT=>5, CURLOPT_TIMEOUT=>12, CURLOPT_MAXREDIRS=>0, CURLOPT_USERAGENT=>'ScreenCamara/1.0 (+painel)', CURLOPT_HTTPHEADER=>['Accept: text/html'], CURLOPT_ENCODING=>'', CURLOPT_SSL_VERIFYPEER=>true]);
    $html = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); $tipo = curl_getinfo($ch, CURLINFO_CONTENT_TYPE); curl_close($ch);
    if (!is_string($html) || $status !== 200 || stripos((string)$tipo, 'text/html') === false || strlen($html)>3000000) {
        throw new RuntimeException('Não foi possível consultar a notícia oficial.');
    }
    $doc = new DOMDocument();
    $ant = libxml_use_internal_errors(true); $doc->loadHTML('<?xml encoding="UTF-8">'.$html); libxml_clear_errors(); libxml_use_internal_errors($ant);
    $xp = new DOMXPath($doc); $imagens=[];
    // Prioriza o carrossel da matéria, não os logotipos do cabeçalho e rodapé.
    $consultas = ["//div[contains(concat(' ',normalize-space(@class),' '),' carousel-inner ')]//img", "//meta[@property='og:image']", "//article//img", "//main//img"];
    foreach ($consultas as $consulta) {
        foreach ($xp->query($consulta) as $el) {
            $src = $el->getAttribute('src') ?: $el->getAttribute('content');
            if (!$src) { $src = $el->getAttribute('data-src'); }
            if (str_starts_with($src,'//')) $src='https:'.$src;
            elseif (str_starts_with($src,'/')) $src='https://www.camara-alvorada.rs.gov.br'.$src;
            elseif (!preg_match('~^https?://~i',$src)) $src='https://www.camara-alvorada.rs.gov.br/'.ltrim($src,'/');
            $u=parse_url($src); $host=strtolower($u['host']??'');
            if (!in_array($host,['www.camara-alvorada.rs.gov.br','camara-alvorada.rs.gov.br'],true) || ($u['scheme']??'')!=='https') continue;
            if (!preg_match('~\.(jpe?g|png|webp)(\?|$)~i', $u['path']??'')) continue;
            if (!in_array($src,$imagens,true)) $imagens[]=$src;
        }
        if ($imagens) break;
    }
    $titulo = trim($xp->evaluate('string(//meta[@property="og:title"]/@content)'));
    if (!$titulo) $titulo=trim($xp->evaluate('string(//h1[1])'));
    return ['titulo'=>mb_substr($titulo,0,220), 'imagens'=>array_slice($imagens,0,24)];
}
