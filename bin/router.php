<?php

/**
 * Roteador para o servidor embutido do PHP (apenas desenvolvimento):
 *   composer serve      (ou: php -S localhost:8000 bin/router.php)
 *
 * A pasta principal do projeto é a raiz do site, então este roteador funciona
 * como uma lista de permissões:
 * - entrega somente o index.html e o conteúdo das pastas públicas (css, js, imagens...);
 * - executa somente os scripts PHP de "api/" e "paginas/";
 * - responde 404 para todo o resto (src, config, database, vendor, .git...).
 */

declare(strict_types=1);

require __DIR__.'/../bootstrap.php';

use Clinitran\Seguranca;

// Pastas que podem ser acessadas pelo navegador
const PASTAS_PUBLICAS = ['css', 'js', 'imagens', 'videos', 'paginas', 'api'];

// Pastas onde arquivos .php podem ser executados
const PASTAS_PHP = ['api', 'paginas'];

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
    'gif' => 'image/gif',
    'ico' => 'image/x-icon',
    'mp4' => 'video/mp4',
    'webm' => 'video/webm',
    'woff2' => 'font/woff2',
    'pdf' => 'application/pdf',
];

function naoEncontrado(): bool
{
    Seguranca::enviarCabecalhos();
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Página não encontrada.';

    return true;
}

$raiz = (string) realpath(__DIR__.'/..');
$caminho = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($caminho === '/') {
    $caminho = '/index.html';
}

$arquivo = realpath($raiz.$caminho);

// Arquivo inexistente ou tentativa de sair da pasta do projeto (ex.: /../)
if ($arquivo === false || ! is_file($arquivo) || ! str_starts_with($arquivo, $raiz.DIRECTORY_SEPARATOR)) {
    return naoEncontrado();
}

// Caminho relativo à raiz, ex.: "css/style.css" ou "index.html"
$relativo = str_replace(DIRECTORY_SEPARATOR, '/', substr($arquivo, strlen($raiz) + 1));
$primeiraPasta = str_contains($relativo, '/') ? strstr($relativo, '/', true) : '';
$extensao = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));

$permitido = $relativo === 'index.html' || in_array($primeiraPasta, PASTAS_PUBLICAS, true);

if (! $permitido) {
    return naoEncontrado();
}

if ($extensao === 'php') {
    if (! in_array($primeiraPasta, PASTAS_PHP, true)) {
        return naoEncontrado();
    }

    return false; // o servidor executa o script PHP (que envia os próprios cabeçalhos)
}

if (! isset(TIPOS_ARQUIVO[$extensao])) {
    return naoEncontrado();
}

Seguranca::enviarCabecalhos();
header('Content-Type: '.TIPOS_ARQUIVO[$extensao]);
header('Content-Length: '.filesize($arquivo));
readfile($arquivo);

return true;
