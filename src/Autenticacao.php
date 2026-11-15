<?php

declare(strict_types=1);

namespace Clinitran;

/**
 * Login da área administrativa: sessão PHP, senha com hash e token CSRF.
 */
final class Autenticacao
{
    private const TEMPO_MAXIMO_INATIVO = 1800; // 30 minutos

    private const CAMINHO_COOKIE = '/admin';

    public static function iniciarSessao(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name('clinitran_admin');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => self::CAMINHO_COOKIE,
            'httponly' => true,                      // JavaScript não lê o cookie
            'samesite' => 'Strict',                  // cookie não vai em requisições de outros sites
            'secure' => ! empty($_SERVER['HTTPS']), // só por HTTPS quando o site estiver em HTTPS
        ]);
        session_start();
    }

    // Retorna ['usuario' => ..., 'senha_hash' => ...] ou null se o admin não foi configurado
    public static function configuracao(): ?array
    {
        $admin = Configuracao::secao('admin');

        if (empty($admin['usuario']) || empty($admin['senha_hash'])) {
            return null;
        }

        return $admin;
    }

    public static function credenciaisValidas(string $usuario, string $senha): bool
    {
        $admin = self::configuracao();
        if ($admin === null) {
            return false;
        }

        // hash_equals evita ataques de tempo na comparação do usuário
        return hash_equals((string) $admin['usuario'], $usuario)
            && password_verify($senha, (string) $admin['senha_hash']);
    }

    public static function entrar(): void
    {
        session_regenerate_id(true); // novo ID de sessão após o login
        $_SESSION['admin_logado'] = true;
        $_SESSION['ultimo_acesso'] = time();
    }

    public static function sair(): void
    {
        $_SESSION = [];
        session_destroy();
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => self::CAMINHO_COOKIE]);
    }

    public static function logado(): bool
    {
        if (empty($_SESSION['admin_logado'])) {
            return false;
        }

        // Encerra a sessão depois de muito tempo sem uso
        if (time() - ($_SESSION['ultimo_acesso'] ?? 0) > self::TEMPO_MAXIMO_INATIVO) {
            self::sair();

            return false;
        }

        $_SESSION['ultimo_acesso'] = time();

        return true;
    }

    // Usada no topo das páginas protegidas
    public static function exigirLogin(): void
    {
        self::iniciarSessao();
        if (! self::logado()) {
            header('Location: login.php');
            exit;
        }
    }

    // Token CSRF: garante que o formulário enviado saiu do nosso próprio site
    public static function tokenCsrf(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    public static function csrfValido(mixed $token): bool
    {
        return is_string($token)
            && ! empty($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $token);
    }
}
