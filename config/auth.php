<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/*
 * Login por cookie assinado (HMAC). A Vercel é serverless: sessões nativas do PHP
 * ficam em disco temporário e se perdem entre execuções, então o estado vai no cookie.
 */
const AUTH_COOKIE = 'equipe_sess';
const AUTH_DURACAO = 43200; // 12 horas

function auth_segredo(): string
{
    $s = env('SESSION_SECRET');
    if (!$s || strlen($s) < 32) {
        throw new RuntimeException('SESSION_SECRET ausente ou com menos de 32 caracteres.');
    }
    return $s;
}

function b64u_enc(string $s): string
{
    return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
}

function b64u_dec(string $s): string
{
    return (string) base64_decode(strtr($s, '-_', '+/'));
}

function https_ativo(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

function auth_entrar(array $usuario): void
{
    $exp = time() + AUTH_DURACAO;
    $payload = b64u_enc(json_encode(['uid' => (int) $usuario['id'], 'nome' => $usuario['nome'], 'exp' => $exp]));
    $assinatura = hash_hmac('sha256', $payload, auth_segredo());
    setcookie(AUTH_COOKIE, $payload . '.' . $assinatura, [
        'expires'  => $exp,
        'path'     => '/',
        'secure'   => https_ativo(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function auth_sair(): void
{
    setcookie(AUTH_COOKIE, '', ['expires' => time() - 3600, 'path' => '/', 'secure' => https_ativo(), 'httponly' => true, 'samesite' => 'Lax']);
}

/** Devolve ['uid','nome','exp'] se o cookie é válido e o usuário ainda está ativo. */
function auth_usuario(): ?array
{
    $cookie = $_COOKIE[AUTH_COOKIE] ?? '';
    if (substr_count($cookie, '.') !== 1) {
        return null;
    }
    [$payload, $assinatura] = explode('.', $cookie);
    if (!hash_equals(hash_hmac('sha256', $payload, auth_segredo()), $assinatura)) {
        return null;
    }
    $dados = json_decode(b64u_dec($payload), true);
    if (!is_array($dados) || ($dados['exp'] ?? 0) < time()) {
        return null;
    }
    $st = db()->prepare('SELECT id, nome FROM usuarios WHERE id = :id AND ativo');
    $st->execute([':id' => (int) $dados['uid']]);
    $u = $st->fetch();
    if (!$u) {
        return null;
    }
    return ['uid' => (int) $u['id'], 'nome' => $u['nome'], 'exp' => (int) $dados['exp']];
}

function auth_exigir(bool $json = false): array
{
    $u = auth_usuario();
    if ($u) {
        return $u;
    }
    if ($json) {
        json_out(['ok' => false, 'msg' => 'Sessão expirada. Entre de novo.'], 401);
    }
    redirect('/login');
}

function csrf_token(array $u): string
{
    return hash_hmac('sha256', 'csrf|' . $u['uid'] . '|' . $u['exp'], auth_segredo());
}

function csrf_valido(array $u, string $token): bool
{
    return $token !== '' && hash_equals(csrf_token($u), $token);
}

function ip_cliente(): string
{
    $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    $ip = $xff !== '' ? trim(explode(',', $xff)[0]) : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    return substr($ip, 0, 64);
}
