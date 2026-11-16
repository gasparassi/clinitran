<?php

declare(strict_types=1);

namespace Clinitran;

/**
 * Configuração lida das variáveis de ambiente (arquivo .env ou painel da hospedagem).
 * Sem as variáveis, o site funciona, mas a área admin e o aviso por WhatsApp ficam desativados.
 *
 * As seções retornadas ('admin' e 'whatsapp') têm sempre o mesmo formato,
 * então as classes que usam a configuração não dependem de onde ela vem.
 */
final class Configuracao
{
    public static function todas(): array
    {
        return [
            'admin' => [
                'usuario' => self::texto('ADMIN_USUARIO'),
                'senha_hash' => self::texto('ADMIN_SENHA_HASH'),
            ],
            'whatsapp' => [
                'ativo' => self::booleano('WHATSAPP_ATIVO'),
                'token' => self::texto('WHATSAPP_TOKEN'),
                'phone_number_id' => self::texto('WHATSAPP_PHONE_NUMBER_ID'),
                'versao_api' => self::texto('WHATSAPP_VERSAO_API', 'v21.0'),
                'destino' => self::texto('WHATSAPP_DESTINO'),
                'modelo' => self::texto('WHATSAPP_MODELO', 'novo_contato_site'),
                'idioma' => self::texto('WHATSAPP_IDIOMA', 'pt_BR'),
            ],
        ];
    }

    // Ex.: Configuracao::secao('admin') retorna o array 'admin' ou []
    public static function secao(string $nome): array
    {
        return self::todas()[$nome] ?? [];
    }

    /**
     * Lê uma variável de ambiente, nesta ordem de prioridade:
     * 1. ambiente real do servidor (getenv): painel da hospedagem, Docker etc.;
     * 2. arquivo .env ($_ENV e $_SERVER, preenchidos pelo phpdotenv).
     */
    private static function texto(string $nome, string $padrao = ''): string
    {
        foreach ([getenv($nome), $_ENV[$nome] ?? null, $_SERVER[$nome] ?? null] as $valor) {
            if (is_string($valor) && $valor !== '') {
                return $valor;
            }
        }

        return $padrao;
    }

    private static function booleano(string $nome): bool
    {
        return filter_var(self::texto($nome, 'false'), FILTER_VALIDATE_BOOL);
    }
}
