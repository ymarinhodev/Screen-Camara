<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/auth.php';

$erro = '';
$usuarioDigitado = '';

try {
    if (auth_usuario()) {
        redirect('/painel');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuarioDigitado = trim((string) ($_POST['usuario'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');
        $ip = ip_cliente();
        $pdo = db();

        $st = $pdo->prepare("SELECT COUNT(*) FROM tentativas_login WHERE ip = :ip AND criado_em > now() - interval '15 minutes'");
        $st->execute([':ip' => $ip]);

        if ((int) $st->fetchColumn() >= 8) {
            http_response_code(429);
            $erro = 'Muitas tentativas. Aguarde 15 minutos e tente de novo.';
        } else {
            $st = $pdo->prepare('SELECT id, nome, senha_hash FROM usuarios WHERE ativo AND (lower(email) = lower(:u) OR lower(nome) = lower(:u)) LIMIT 1');
            $st->execute([':u' => $usuarioDigitado]);
            $u = $st->fetch();

            // Sempre confere um hash, mesmo sem usuário, para não revelar quais nomes existem.
            $hash = $u['senha_hash'] ?? '$2y$10$usesomesillystringforsaltuQ3m6Qm0Y0b4n1o0uK3Vd8q2W5eHsC';
            if (password_verify($senha, $hash) && $u) {
                $pdo->prepare('DELETE FROM tentativas_login WHERE ip = :ip')->execute([':ip' => $ip]);
                auth_entrar($u);
                redirect('/painel');
            }

            $pdo->prepare('INSERT INTO tentativas_login (ip) VALUES (:ip)')->execute([':ip' => $ip]);
            $erro = 'Usuário ou senha incorretos.';
        }
    }
} catch (Throwable $ex) {
    error_log('login: ' . $ex->getMessage());
    http_response_code(500);
    $erro = 'Não foi possível entrar agora. Tente novamente em instantes.';
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Login da Equipe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/painel.css" rel="stylesheet">
</head>
<body class="pagina-login">
    <main class="login">
        <img class="login-logo" src="/assets/img/logo.png" alt="Câmara Municipal de Alvorada">
        <section class="login-cartao">
            <h1>Login da Equipe</h1>
            <p class="login-texto">Acesso privado para atualizar vereadores, notícias e QR Codes das telas.</p>
<?php if ($erro !== ''): ?>
            <p class="aviso aviso-erro" role="alert"><?= e($erro) ?></p>
<?php endif; ?>
            <form method="post" action="/login" autocomplete="on">
                <label for="usuario">Nome ou e-mail</label>
                <input id="usuario" name="usuario" type="text" value="<?= e($usuarioDigitado) ?>" autocomplete="username" required autofocus>
                <label for="senha">Senha</label>
                <input id="senha" name="senha" type="password" autocomplete="current-password" required>
                <button type="submit" class="botao botao-ouro botao-cheio">Entrar no painel</button>
            </form>
            <p class="login-rodape"><a href="/tela-noticias">Voltar à tela de notícias</a></p>
        </section>
    </main>
</body>
</html>
