<?php
declare(strict_types=1);
/** Importa apenas HTML do portal oficial; imagens retornadas são URLs HTTPS. */
function importar_noticia_oficial(string $url): array {
    $parts=parse_url($url);
    $hosts=['www.camara-alvorada.rs.gov.br','camara-alvorada.rs.gov.br'];
    if (!$parts || ($parts['scheme']??'')!=='https' || !in_array(strtolower($parts['host']??''),$hosts,true) || !preg_match('~^/noticia/view/[0-9]+/?$~',$parts['path']??'')) throw new RuntimeException('Informe o link HTTPS de uma notícia oficial da Câmara.');
    if (!function_exists('curl_init') || !class_exists('DOMDocument')) throw new RuntimeException('Extensões PHP cURL/DOM indisponíveis no servidor.');
    // Consulte apenas os dois endereços oficiais autorizados. Alguns ambientes
    // respondem de forma diferente ao domínio com ou sem www.
    $hostOriginal = strtolower((string) $parts['host']);
    $hostAlternativo = $hostOriginal === 'www.camara-alvorada.rs.gov.br'
        ? 'camara-alvorada.rs.gov.br'
        : 'www.camara-alvorada.rs.gov.br';

    $urls = [$url];
    $alternativa = 'https://' . $hostAlternativo . ($parts['path'] ?? '/');
    if (isset($parts['query'])) {
        $alternativa .= '?' . $parts['query'];
    }
    $urls[] = $alternativa;

    $html = '';
    $ultimoStatus = 0;

    foreach ($urls as $urlConsulta) {
        $atual = $urlConsulta;

        // Redirecionamentos são verificados individualmente, para não permitir
        // consultas a domínios externos ou URLs não HTTPS.
        for ($i = 0; $i < 4; $i++) {
            $ch = curl_init($atual);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 7,
                CURLOPT_TIMEOUT => 18,
                CURLOPT_USERAGENT => 'ScreenCamara/2.1 (consulta de noticias publicas)',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml',
                    'Accept-Language: pt-BR,pt;q=0.9',
                ],
                CURLOPT_ENCODING => '',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
                CURLOPT_MAXFILESIZE => 4000000,
            ]);

            $resposta = curl_exec($ch);
            $erroCurl = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $tipo = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $redirecionamento = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
            curl_close($ch);

            $ultimoStatus = $status;
            if ($resposta === false) {
                error_log('importar_noticia: falha de rede: ' . $erroCurl);
                break;
            }

            if (in_array($status, [301, 302, 303, 307, 308], true)) {
                $proxima = parse_url($redirecionamento);
                if (!$proxima || ($proxima['scheme'] ?? '') !== 'https'
                    || !in_array(strtolower($proxima['host'] ?? ''), $hosts, true)
                    || !preg_match('~^/noticia/view/[0-9]+/?$~', $proxima['path'] ?? '')) {
                    throw new RuntimeException('O portal redirecionou para um endereço não autorizado.');
                }
                $atual = $redirecionamento;
                continue;
            }

            if ($status === 200 && stripos($tipo, 'text/html') !== false
                && strlen($resposta) <= 4000000) {
                $html = $resposta;
            } else {
                error_log('importar_noticia: HTTP ' . $status . ' content-type=' . $tipo);
            }
            break;
        }

        if ($html !== '') {
            break;
        }
    }

    if ($html === '') {
        if ($ultimoStatus === 403) {
            throw new RuntimeException(
                'O portal oficial recusou a consulta automática (HTTP 403), inclusive no endereço alternativo. ' .
                'Use “Trocar foto da notícia” para enviar a imagem manualmente ou solicite acesso autorizado ao portal.'
            );
        }
        throw new RuntimeException(
            'Não foi possível consultar a matéria oficial (HTTP ' . $ultimoStatus . '). ' .
            'Verifique o link ou envie a imagem manualmente.'
        );
    }

    $doc=new DOMDocument();$old=libxml_use_internal_errors(true);$doc->loadHTML('<?xml encoding="UTF-8">'.$html);libxml_clear_errors();libxml_use_internal_errors($old);
    $xp=new DOMXPath($doc);$imagens=[];
    $consultas=["//div[contains(concat(' ',normalize-space(@class),' '),' carousel-inner ')]//img","//article//img","//main//img","//meta[@property='og:image']"];
    foreach($consultas as $consulta){
        foreach($xp->query($consulta) as $el){
            $src='';foreach(['data-src','data-original','src','content','data-lazy-src'] as $attr){if($el->getAttribute($attr)!==''){$src=$el->getAttribute($attr);break;}}
            if ($src==='' || str_starts_with($src,'data:')) continue;
            if (str_starts_with($src,'//')) $src='https:'.$src;
            elseif (str_starts_with($src,'/')) $src='https://www.camara-alvorada.rs.gov.br'.$src;
            elseif (!preg_match('~^https?://~i',$src)) $src='https://www.camara-alvorada.rs.gov.br/'.ltrim($src,'/');
            $u=parse_url($src);if(!$u || ($u['scheme']??'')!=='https' || !in_array(strtolower($u['host']??''),$hosts,true))continue;
            if(!preg_match('~\.(jpe?g|png|webp)(?:$|[?#])~i',$src))continue;
            if(!in_array($src,$imagens,true))$imagens[]=$src;
        }
        if($imagens)break;
    }
    $titulo=trim($xp->evaluate('string(//meta[@property="og:title"]/@content)'));
    if(!$titulo)$titulo=trim($xp->evaluate('string(//h1[1])'));
    return ['titulo'=>mb_substr($titulo,0,220),'imagens'=>array_slice($imagens,0,30)];
}
