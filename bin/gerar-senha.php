<?php

/**
 * Gera o hash de uma senha para colocar em config/config.php.
 * Uso (no terminal):  php bin/gerar-senha.php "minha senha forte"
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit; // só pode ser executado pelo terminal
}

if (empty($argv[1]) || strlen($argv[1]) < 10) {
    fwrite(STDERR, "Informe uma senha com pelo menos 10 caracteres.\n");
    fwrite(STDERR, "Uso: php bin/gerar-senha.php \"minha senha forte\"\n");
    exit(1);
}

echo password_hash($argv[1], PASSWORD_DEFAULT).PHP_EOL;
