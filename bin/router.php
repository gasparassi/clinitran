<?php

/**
 * Roteador para o servidor embutido do PHP (apenas desenvolvimento):
 *   composer serve      (ou: php -S localhost:8000 -t public bin/router.php)
 *
 * - Arquivos .php: executados normalmente (eles enviam os próprios cabeçalhos de segurança).
 * - Arquivos estáticos: entregues aqui, já com os cabeçalhos de segurança.
 * - Caminhos inexistentes: 404 (o servidor embutido devolveria o index.html).
 */

declare(strict_types=1);

require __DIR__.'/../bootstrap.php';

use Clinitran\Seguranca;

const TIPOS_ARQUIVO = [
    'html' => 'text/html; charset=UTF-8',
    'css' => 'text/css; charset=UTF-8',
    'js' => 'text/javascript; charset=UTF-8',
    'json' => 'application/json',
    'svg' => 'image/svg+xml',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'webp' => 'image/webp',
    'ico' => 'image/x-icon',
    'woff2' => 'font/woff2',
    'txt' => 'text/plain; charset=UTF-8',
];

$pastaPublica = realpath(__DIR__.'/../public');
$caminho = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (str_ends_with($caminho, '/')) {
    $caminho .= 'index.html';
}

$arquivo = realpath($pastaPublica.$caminho);

// Bloqueia arquivos inexistentes e tentativas de sair da pasta public (ex.: /../config)
if ($arquivo === false || ! str_starts_with($arquivo, $pastaPublica.DIRECTORY_SEPARATOR) || ! is_file($arquivo)) {
    Seguranca::enviarCabecalhos();
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Página não encontrada.';

    return true;
}

$extensao = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));

if ($extensao === 'php') {
    return false; // o servidor executa o script PHP
}

if (! isset(TIPOS_ARQUIVO[$extensao])) {
    Seguranca::enviarCabecalhos();
    http_response_code(403);
    echo 'Acesso negado.';

    return true;
}

Seguranca::enviarCabecalhos();
header('Content-Type: '.TIPOS_ARQUIVO[$extensao]);
header('Content-Length: '.filesize($arquivo));
readfile($arquivo);

return true;
