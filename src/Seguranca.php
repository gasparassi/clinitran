<?php

declare(strict_types=1);

namespace Clinitran;

/**
 * Cabeçalhos HTTP de segurança.
 * A CSP (Content Security Policy) só libera os domínios que o site realmente usa.
 */
final class Seguranca
{
    public static function enviarCabecalhos(): void
    {
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' https://cdn.jsdelivr.net",
            "style-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com",
            "img-src 'self' data:",
            'frame-src https://www.google.com',
            "connect-src 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
        ]);

        header('Content-Security-Policy: '.$csp);
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }

    public static function ipDoVisitante(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    }
}
