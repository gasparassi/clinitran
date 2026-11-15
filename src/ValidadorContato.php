<?php

declare(strict_types=1);

namespace Clinitran;

use DateTimeImmutable;

/**
 * Valida os dados do formulário de contato no servidor.
 * O JavaScript valida antes, mas nunca confiamos só no navegador.
 */
final class ValidadorContato
{
    public const ASSUNTOS = ['Renovação', 'Primeira habilitação', 'Adição ou mudança de categoria', 'Dúvidas', 'Outro'];

    private ?string $erro = null;

    private array $dados = [];

    public function validar(array $entrada): bool
    {
        $nome = self::campoTexto($entrada, 'nome', 100);
        $email = self::campoTexto($entrada, 'email', 254);
        $telefone = self::campoTexto($entrada, 'telefone', 20);
        $assunto = self::campoTexto($entrada, 'assunto', 50);
        $dataPreferida = self::campoTexto($entrada, 'data_preferida', 10);
        $mensagem = self::campoTexto($entrada, 'mensagem', 500);

        if ($nome === null || mb_strlen($nome) < 3) {
            return $this->falhar('Nome inválido.');
        }
        if ($email === null || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->falhar('E-mail inválido.');
        }
        if ($telefone === null || ! self::telefoneValido($telefone)) {
            return $this->falhar('Telefone inválido.');
        }
        if ($assunto === null || ! in_array($assunto, self::ASSUNTOS, true)) {
            return $this->falhar('Assunto inválido.');
        }
        if ($dataPreferida === null || ($dataPreferida !== '' && ! self::dataValida($dataPreferida))) {
            return $this->falhar('Data inválida. Escolha um dia útil a partir de hoje.');
        }
        if ($mensagem === null || mb_strlen($mensagem) < 10) {
            return $this->falhar('A mensagem deve ter entre 10 e 500 caracteres.');
        }

        $this->dados = [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'assunto' => $assunto,
            'data_preferida' => $dataPreferida,
            'mensagem' => $mensagem,
        ];

        return true;
    }

    public function erro(): ?string
    {
        return $this->erro;
    }

    // Dados já limpos (sem espaços extras), disponíveis depois de validar()
    public function dados(): array
    {
        return $this->dados;
    }

    private function falhar(string $mensagem): bool
    {
        $this->erro = $mensagem;

        return false;
    }

    /**
     * Lê um campo de texto do JSON.
     * Retorna null se o campo não for texto ou passar do tamanho máximo.
     */
    private static function campoTexto(array $entrada, string $campo, int $tamanhoMaximo): ?string
    {
        $valor = $entrada[$campo] ?? '';
        if (! is_string($valor)) {
            return null;
        }

        $valor = trim($valor);

        return mb_strlen($valor) > $tamanhoMaximo ? null : $valor;
    }

    private static function telefoneValido(string $telefone): bool
    {
        if (! preg_match('/^[\d\s()+-]+$/', $telefone)) {
            return false;
        }

        $digitos = strlen((string) preg_replace('/\D/', '', $telefone));

        return $digitos >= 10 && $digitos <= 13;
    }

    // Data no formato AAAA-MM-DD, existente, não passada e em dia útil
    private static function dataValida(string $data): bool
    {
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $data, $partes)) {
            return false;
        }
        if (! checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
            return false;
        }

        $escolhida = new DateTimeImmutable($data);
        $hoje = new DateTimeImmutable('today');
        $diaDaSemana = (int) $escolhida->format('N'); // 1 = segunda ... 7 = domingo

        return $escolhida >= $hoje && $diaDaSemana <= 5;
    }
}
