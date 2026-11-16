<?php

declare(strict_types=1);

namespace Clinitran;

/**
 * Aviso automático por WhatsApp (API oficial - Meta Cloud API).
 * Envia para o celular da clínica um resumo de cada contato recebido pelo site.
 *
 * Fica desligado até WHATSAPP_ATIVO=true no arquivo .env
 * (veja .env.example).
 */
final class NotificadorWhatsApp
{
    /**
     * Retorna true se o aviso foi enviado, false se está desligado ou se deu erro.
     * Nunca lança exceção: uma falha aqui não pode impedir o contato de ser salvo.
     */
    public static function notificar(int $protocolo, array $contato): bool
    {
        $config = Configuracao::secao('whatsapp');
        if (empty($config['ativo'])) {
            return false;
        }

        $dataPreferida = '-';
        if (! empty($contato['data_preferida'])) {
            [$ano, $mes, $dia] = explode('-', $contato['data_preferida']);
            $dataPreferida = "$dia/$mes/$ano";
        }

        $parametros = [
            (string) $protocolo,
            $contato['nome'],
            $contato['telefone'],
            $contato['assunto'],
            $dataPreferida,
        ];

        $corpo = [
            'messaging_product' => 'whatsapp',
            'to' => $config['destino'],
            'type' => 'template',
            'template' => [
                'name' => $config['modelo'],
                'language' => ['code' => $config['idioma']],
                'components' => [[
                    'type' => 'body',
                    'parameters' => array_map(
                        fn (string $valor): array => ['type' => 'text', 'text' => self::limparParametro($valor)],
                        $parametros
                    ),
                ]],
            ],
        ];

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            $config['versao_api'],
            $config['phone_number_id']
        );

        $requisicao = curl_init($url);
        curl_setopt_array($requisicao, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$config['token'],
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($corpo, JSON_UNESCAPED_UNICODE),
        ]);

        $resposta = curl_exec($requisicao);
        $status = curl_getinfo($requisicao, CURLINFO_HTTP_CODE);
        $erroRede = curl_error($requisicao);
        curl_close($requisicao);

        if ($status >= 200 && $status < 300) {
            return true;
        }

        // Registra no log do PHP para facilitar a investigação, sem expor nada ao visitante
        error_log("Falha ao enviar WhatsApp (HTTP $status): ".($erroRede ?: (string) $resposta));

        return false;
    }

    // A Meta não aceita quebras de linha nem muitos espaços seguidos nas variáveis do modelo
    private static function limparParametro(string $texto): string
    {
        $texto = (string) preg_replace('/\s+/', ' ', trim($texto));

        return $texto === '' ? '-' : mb_substr($texto, 0, 200);
    }
}
