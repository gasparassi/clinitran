<?php

/**
 * Ponto de partida de todas as páginas PHP.
 * Carrega o autoload do Composer (classes em src/ e pacotes instalados em vendor/)
 * e as variáveis de ambiente do arquivo .env.
 */

declare(strict_types=1);

// Nunca mostra erros do PHP ao visitante (eles vão apenas para o log)
ini_set('display_errors', '0');

const RAIZ_PROJETO = __DIR__;

$autoload = RAIZ_PROJETO.'/vendor/autoload.php';

if (! is_file($autoload)) {
    http_response_code(500);
    error_log('Dependências não instaladas: execute "composer install" na raiz do projeto.');
    exit('Site em manutenção.');
}

require $autoload;

// Lê o .env (se existir). Variáveis já definidas no servidor têm prioridade e não são sobrescritas.
// safeLoad: sem o arquivo .env o site continua funcionando (admin e WhatsApp ficam desativados).
Dotenv\Dotenv::createImmutable(RAIZ_PROJETO)->safeLoad();
