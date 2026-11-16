<?php

/**
 * Encerra a sessão da área administrativa (somente via POST com token CSRF).
 */

declare(strict_types=1);

require __DIR__.'/../../bootstrap.php';

use Clinitran\Autenticacao;
use Clinitran\Seguranca;

Seguranca::enviarCabecalhos();
Autenticacao::iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Autenticacao::csrfValido($_POST['csrf'] ?? null)) {
    Autenticacao::sair();
}

header('Location: login.php');
exit;
