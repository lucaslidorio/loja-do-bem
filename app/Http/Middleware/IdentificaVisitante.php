<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dá a cada visitante um identificador anônimo (cookie) para registrar
 * os "tenho interesse" sem pedir login nem dado pessoal.
 */
class IdentificaVisitante
{
    public function handle(Request $request, Closure $next): Response
    {
        $nome = config('loja.cookie_visitante');
        $id = $request->cookie($nome);

        if (! is_string($id) || ! Str::isUuid($id)) {
            $id = (string) Str::uuid();
            Cookie::queue($nome, $id, 60 * 24 * 365 * 2); // 2 anos
        }

        $request->attributes->set('visitante', hash_hmac('sha256', $id, (string) config('app.key')));

        return $next($request);
    }
}
