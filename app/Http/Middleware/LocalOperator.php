<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalOperator
{
    public function handle(Request $request, Closure $next): Response
    {
        $environment = app()->environment(['local', 'testing']);
        $address = in_array($request->ip(), ['127.0.0.1', '::1'], true);
        $host = in_array($request->getHost(), ['localhost', '127.0.0.1', '[::1]', '::1'], true);
        abort_unless($environment && $address && $host, 403);

        return $next($request);
    }
}
