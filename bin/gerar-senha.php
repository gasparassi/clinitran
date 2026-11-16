<?php

/**
 * Gera o hash de uma senha para a área administrativa.
 * Uso (no terminal):  composer gerar-senha -- "minha senha forte"
 *
 * Mostra a linha pronta para colar no arquivo .env, já com aspas simples
 * (o hash contém "$", que ferramentas como Docker e o shell interpretariam como variável).
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit; // só pode ser executado pelo terminal
}

if (empty($argv[1]) || strlen($argv[1]) < 10) {
    fwrite(STDERR, "Informe uma senha com pelo menos 10 caracteres.\n");
    fwrite(STDERR, "Uso: composer gerar-senha -- \"minha senha forte\"\n");
    exit(1);
}

$hash = password_hash($argv[1], PASSWORD_DEFAULT);

echo "Copie a linha abaixo para o arquivo .env:\n\n";
echo "ADMIN_SENHA_HASH='{$hash}'\n";
