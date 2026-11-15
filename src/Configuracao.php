<?php

declare(strict_types=1);

namespace Clinitran;

/**
 * Lê o arquivo config/config.php (se existir).
 * Sem o arquivo, o site funciona, mas a área admin e o aviso por WhatsApp ficam desativados.
 */
final class Configuracao
{
    private static ?array $dados = null;

    public static function todas(): array
    {
        if (self::$dados === null) {
            $arquivo = RAIZ_PROJETO.'/config/config.php';
            $conteudo = is_file($arquivo) ? require $arquivo : [];
            self::$dados = is_array($conteudo) ? $conteudo : [];
        }

        return self::$dados;
    }

    // Ex.: Configuracao::secao('admin') retorna o array 'admin' ou []
    public static function secao(string $nome): array
    {
        $secao = self::todas()[$nome] ?? [];

        return is_array($secao) ? $secao : [];
    }
}
