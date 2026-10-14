<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/auth.php';

try {
    $u = auth_usuario();
    if ($u && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido($u, (string) ($_POST['csrf'] ?? ''))) {
        auth_sair();
        redirect('/login');
    }
} catch (Throwable $ex) {
    error_log('logout: ' . $ex->getMessage());
}
redirect('/painel');
