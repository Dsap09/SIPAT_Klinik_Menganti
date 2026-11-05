<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMode
{
    public function handle(Request $request, Closure $next, string $mode): Response
    {
        $aktif = (string) config('sipat.mode', 'full');

        if ($aktif !== 'full' && $aktif !== $mode) {
            abort(404);
        }

        return $next($request);
    }
}
