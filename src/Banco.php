<?php

declare(strict_types=1);

namespace Clinitran;

use PDO;

/**
 * Conexão com o banco SQLite (database/clinitran.sqlite).
 * Na primeira execução o arquivo do banco e as tabelas são criados automaticamente.
 */
final class Banco
{
    private static ?PDO $conexao = null;

    public static function conexao(): PDO
    {
        if (self::$conexao === null) {
            $pastaBanco = RAIZ_PROJETO.'/database';

            $pdo = new PDO('sqlite:'.$pastaBanco.'/clinitran.sqlite');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Cria as tabelas caso ainda não existam
            $pdo->exec((string) file_get_contents($pastaBanco.'/schema.sql'));

            self::$conexao = $pdo;
        }

        return self::$conexao;
    }
}
