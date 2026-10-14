<?php
declare(strict_types=1);

/*
 * Primeira configuração: cria as tabelas no Neon, o primeiro usuário da equipe
 * e importa os dados das telas originais (vereadores, notícias e QR Codes).
 * Acesse /setup.php?token=SEU_SETUP_TOKEN. Pode ser executado de novo sem duplicar dados.
 * Depois do primeiro uso, REMOVA a variável SETUP_TOKEN na Vercel.
 */

require_once dirname(__DIR__) . '/config/helpers.php';

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$esperado = env('SETUP_TOKEN');
if (!$esperado) {
    http_response_code(403);
    exit("Setup desativado: a variável SETUP_TOKEN não está configurada.\n");
}
if (!hash_equals($esperado, (string) ($_GET['token'] ?? ''))) {
    http_response_code(403);
    exit("Token inválido.\n");
}

try {
    $pdo = db();
    $raiz = dirname(__DIR__);

    $pdo->exec((string) file_get_contents($raiz . '/sql/schema.sql'));
    echo "OK  tabelas criadas/verificadas\n";

    // ---- usuário da equipe ----
    $total = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    $nome  = env('ADMIN_NOME');
    $email = strtolower((string) env('ADMIN_EMAIL'));
    $senha = env('ADMIN_SENHA');
    if (!empty($_GET['reset_admin']) && $nome && $senha) {
        $st = $pdo->prepare('UPDATE usuarios SET senha_hash = :h, ativo = TRUE WHERE lower(nome) = lower(:n)');
        $st->execute([':h' => password_hash($senha, PASSWORD_DEFAULT), ':n' => $nome]);
        echo "OK  senha do usuário '{$nome}' redefinida (linhas: {$st->rowCount()})\n";
    } elseif ($total === 0) {
        if (!$nome || !$email || !$senha) {
            exit("ERRO defina ADMIN_NOME, ADMIN_EMAIL e ADMIN_SENHA para criar o primeiro usuário.\n");
        }
        if (strlen($senha) < 8) {
            exit("ERRO ADMIN_SENHA precisa ter pelo menos 8 caracteres.\n");
        }
        $st = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (:n, :e, :h)');
        $st->execute([':n' => $nome, ':e' => $email, ':h' => password_hash($senha, PASSWORD_DEFAULT)]);
        echo "OK  usuário '{$nome}' criado\n";
    } else {
        echo "--  já existem usuários; nada a criar\n";
    }

    // ---- dados iniciais ----
    $seed = json_decode((string) file_get_contents($raiz . '/sql/seed.json'), true);
    $cache = [];
    $imagem = function (?string $arquivo) use (&$cache, $raiz): ?int {
        if (!$arquivo) {
            return null;
        }
        if (!isset($cache[$arquivo])) {
            $cache[$arquivo] = salvar_imagem_arquivo($raiz . '/sql/seed-images/' . $arquivo);
        }
        return $cache[$arquivo];
    };

    if ((int) $pdo->query('SELECT COUNT(*) FROM vereadores')->fetchColumn() === 0) {
        $st = $pdo->prepare('INSERT INTO vereadores (tela, ordem, nome, gabinete, partido, foto_id, logo_id, foto_url) VALUES (:t, :o, :n, :g, :p, :f, :l, :url)');
        foreach ($seed['vereadores'] as $v) {
            $st->bindValue(':t', $v['tela']);
            $st->bindValue(':o', (int) $v['ordem'], PDO::PARAM_INT);
            $st->bindValue(':n', $v['nome']);
            $st->bindValue(':g', $v['gabinete']);
            $st->bindValue(':p', $v['partido']);
            $st->bindValue(':f', $imagem($v['foto']), PDO::PARAM_INT);
            $st->bindValue(':l', $imagem($v['logo']), PDO::PARAM_INT);
            $base = rtrim((string) env('FOTOS_OFICIAIS_BASE', ''), '/');
            $st->bindValue(':url', $base !== '' ? $base . ($v['foto_caminho_oficial'] ?? '') : '');
            $st->execute();
        }
        echo 'OK  ' . count($seed['vereadores']) . " vereadores importados\n";
    } else {
        echo "--  vereadores já cadastrados\n";
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM noticias')->fetchColumn() === 0) {
        $st = $pdo->prepare('INSERT INTO noticias (ordem, titulo, link, imagem_id) VALUES (:o, :t, :l, :i)');
        foreach ($seed['noticias'] as $n) {
            $st->bindValue(':o', (int) $n['ordem'], PDO::PARAM_INT);
            $st->bindValue(':t', $n['titulo']);
            $st->bindValue(':l', $n['link']);
            $st->bindValue(':i', $imagem($n['imagem']), PDO::PARAM_INT);
            $st->execute();
        }
        echo 'OK  ' . count($seed['noticias']) . " notícias importadas\n";
    } else {
        echo "--  notícias já cadastradas\n";
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM qrcodes')->fetchColumn() === 0) {
        $st = $pdo->prepare('INSERT INTO qrcodes (ordem, titulo, link) VALUES (:o, :t, :l)');
        foreach ($seed['qrcodes'] as $q) {
            $st->execute([':o' => (int) $q['ordem'], ':t' => $q['titulo'], ':l' => $q['link']]);
        }
        echo 'OK  ' . count($seed['qrcodes']) . " QR Codes importados\n";
    } else {
        echo "--  QR Codes já cadastrados\n";
    }

    echo "\nPronto. Acesse /login. Remova SETUP_TOKEN da Vercel agora.\n";
} catch (Throwable $ex) {
    error_log('setup: ' . $ex->getMessage());
    http_response_code(500);
    echo 'ERRO ' . $ex->getMessage() . "\n";
}
