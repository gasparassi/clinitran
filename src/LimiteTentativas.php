<?php

declare(strict_types=1);

namespace Clinitran;

use PDO;

/**
 * Limite de tentativas por IP (ex.: 5 envios a cada 10 minutos).
 * Os registros ficam na tabela "tentativas" do SQLite.
 */
final class LimiteTentativas
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $acao,
        private readonly int $maximo,
        private readonly int $janelaSegundos,
    ) {}

    public function excedido(): bool
    {
        // Limpa registros com mais de 1 dia, para a tabela não crescer sem parar
        $this->pdo->prepare('DELETE FROM tentativas WHERE criado_em < :limite')
            ->execute([':limite' => time() - 86400]);

        $consulta = $this->pdo->prepare(
            'SELECT COUNT(*) FROM tentativas WHERE ip = :ip AND acao = :acao AND criado_em >= :inicio'
        );
        $consulta->execute([
            ':ip' => Seguranca::ipDoVisitante(),
            ':acao' => $this->acao,
            ':inicio' => time() - $this->janelaSegundos,
        ]);

        return (int) $consulta->fetchColumn() >= $this->maximo;
    }

    public function registrar(): void
    {
        $this->pdo->prepare('INSERT INTO tentativas (ip, acao, criado_em) VALUES (:ip, :acao, :agora)')
            ->execute([
                ':ip' => Seguranca::ipDoVisitante(),
                ':acao' => $this->acao,
                ':agora' => time(),
            ]);
    }
}
