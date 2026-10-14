<?php
declare(strict_types=1);
/** Importa apenas HTML do portal oficial; imagens retornadas são URLs HTTPS. */
function importar_noticia_oficial(string $url): array {
    $parts=parse_url($url);
    $hosts=['www.camara-alvorada.rs.gov.br','camara-alvorada.rs.gov.br'];
    if (!$parts || ($parts['scheme']??'')!=='https' || !in_array(strtolower($parts['host']??''),$hosts,true) || !preg_match('~^/noticia/view/[0-9]+/?$~',$parts['path']??'')) throw new RuntimeException('Informe o link HTTPS de uma notícia oficial da Câmara.');
    if (!function_exists('curl_init') || !class_exists('DOMDocument')) throw new RuntimeException('Extensões PHP cURL/DOM indisponíveis no servidor.');
    $atual=$url; $html='';
    for($i=0;$i<4;$i++) {
        $ch=curl_init($atual);
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>7,CURLOPT_TIMEOUT=>18,CURLOPT_USERAGENT=>'Mozilla/5.0 (compatible; ScreenCamara/2.0)',CURLOPT_HTTPHEADER=>['Accept: text/html,application/xhtml+xml','Accept-Language: pt-BR,pt;q=0.9'],CURLOPT_ENCODING=>'',CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_MAXFILESIZE=>4000000]);
        $res=curl_exec($ch); $erro=curl_error($ch); $status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $tipo=(string)curl_getinfo($ch,CURLINFO_CONTENT_TYPE); $redirect=(string)curl_getinfo($ch,CURLINFO_REDIRECT_URL); curl_close($ch);
        if ($res===false) {error_log('importar_noticia curl: '.$erro);throw new RuntimeException('O portal oficial não respondeu à consulta do servidor. Use o upload manual ou tente novamente.');}
        if (in_array($status,[301,302,303,307,308],true)) {
            $next=parse_url($redirect);
            if (!$next || ($next['scheme']??'')!=='https' || !in_array(strtolower($next['host']??''),$hosts,true)) throw new RuntimeException('O portal redirecionou para um endereço não autorizado.');
            $atual=$redirect;continue;
        }
        if ($status!==200 || stripos($tipo,'text/html')===false || strlen($res)>4000000) {error_log('importar_noticia http='.$status.' type='.$tipo);throw new RuntimeException('Não foi possível consultar a notícia oficial (HTTP '.$status.'). Tente novamente ou envie a imagem manualmente.');}
        $html=$res;break;
    }
    if ($html==='') throw new RuntimeException('Redirecionamentos excessivos no portal oficial.');
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
