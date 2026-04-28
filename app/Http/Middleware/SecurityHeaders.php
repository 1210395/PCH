<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Appends security-related HTTP response headers to every outgoing response.
 *
 * Sets X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy,
 * Permissions-Policy, and (on HTTPS only) Strict-Transport-Security (HSTS).
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * Passes the request down the middleware pipeline, then attaches security
     * headers to the response before returning it to the client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        // X-XSS-Protection is deprecated by all modern browsers and can
        // introduce vulnerabilities in legacy IE/Edge. Send `0` per current
        // OWASP / Mozilla guidance; defense-in-depth lives in the CSP below.
        // (bugs.md M-43)
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content-Security-Policy in Report-Only mode so violations show up
        // in browser devtools / report-uri without breaking anything. Once
        // the report stream is clean, switch the header name to
        // `Content-Security-Policy` (no -Report-Only) to enforce.
        // (bugs.md H-29)
        //
        // Permissive on purpose: 'unsafe-inline' for script + style is
        // currently necessary because Alpine.js evaluates inline @click /
        // x-data expressions and Tailwind injects inline styles via the
        // @apply pattern. Tightening to nonces is a future task.
        $csp = "default-src 'self'; "
             . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
             . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; "
             . "img-src 'self' data: blob: https:; "
             . "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net; "
             . "connect-src 'self' https:; "
             . "media-src 'self' https:; "
             . "frame-ancestors 'self'; "
             . "form-action 'self'; "
             . "base-uri 'self'; "
             . "object-src 'none'";
        $response->headers->set('Content-Security-Policy-Report-Only', $csp);

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
