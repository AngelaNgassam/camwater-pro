<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('web')->check() && auth('web')->user()->role === 'admin') {
            return $next($request);
        }

        abort(403, 'Accès réservé aux administrateurs.');
    }
}
