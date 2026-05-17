<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeadersMiddleware
 *
 * Agrega cabeceras HTTP de seguridad para proteger contra los principales
 * vectores de ataque del OWASP Top 10:
 *   - A03: Injection (CSP)
 *   - A05: Security Misconfiguration (X-Frame-Options, X-Content-Type)
 *   - A07: Identification & Authentication Failures (HSTS)
 *   - A09: Security Logging & Monitoring
 *
 * Activar en bootstrap/app.php o en las rutas de API.
 */
class SecurityHeadersMiddleware
{
    /**
     * Cabeceras a eliminar (evitar revelar información del servidor)
     */
    private const HEADERS_TO_REMOVE = [
        'X-Powered-By',
        'Server',
        'X-AspNet-Version',
        'X-AspNetMvc-Version',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // ── 1. Eliminar cabeceras que revelan tecnología del servidor ─────────
        foreach (self::HEADERS_TO_REMOVE as $header) {
            $response->headers->remove($header);
        }

        // ── 2. HTTP Strict Transport Security (HSTS) ─────────────────────────
        // Obliga al navegador a usar siempre HTTPS durante 2 años
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=63072000; includeSubDomains; preload'
        );

        // ── 3. Prevenir que la página sea embebida en iframes (Clickjacking) ──
        $response->headers->set('X-Frame-Options', 'DENY');

        // ── 4. Evitar MIME sniffing (Content-Type injection) ──────────────────
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ── 5. XSS Protection (navegadores heredados) ─────────────────────────
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // ── 6. Referrer Policy (no filtrar info en el header Referer) ─────────
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ── 7. Permissions Policy (deshabilitar APIs peligrosas) ──────────────
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(self), payment=(), usb=(), fullscreen=(self)'
        );

        // ── 8. Content Security Policy (CSP) ─────────────────────────────────
        // Ajusta los dominios según los CDNs/servicios que uses
        $cspDirectives = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",      // Vue + librerías
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",   // CSS + Google Fonts
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https: blob:",                               // Imágenes + avatares
            "connect-src 'self' wss: https://livekit.cloud https://api.resend.com", // API + WebSocket
            "media-src 'self' blob:",                                          // Videollamadas
            "object-src 'none'",                                               // Sin Flash/Java
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",                                          // Anti-Clickjacking
            "upgrade-insecure-requests",                                       // Forzar HTTPS
        ]);

        $response->headers->set('Content-Security-Policy', $cspDirectives);

        // ── 9. Cross-Origin Resource Policy ───────────────────────────────────
        $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin');

        // ── 10. Cross-Origin Opener Policy ────────────────────────────────────
        $response->headers->set('Cross-Origin-Opener-Policy', 'cross-origin');

        return $response;
    }
}
