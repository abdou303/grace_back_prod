<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
  /*  public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', '*'); // Allow Angular
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');

        return $response;
    }
	
	
	public function handle(Request $request, Closure $next): Response
{
    $headers = [
        'Access-Control-Allow-Origin'      => '*', //  Use specific origin in production
        'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Content-Type, Authorization',
        'Access-Control-Expose-Headers'    => 'Content-Disposition',
    ];

    if ($request->getMethod() === 'OPTIONS') {
        return response()->noContent(204)->withHeaders($headers);
    }

    $response = $next($request);

    foreach ($headers as $key => $value) {
        $response->headers->set($key, $value);
    }

    return $response;
}*/
public function handle(Request $request, Closure $next): Response
{
    if ($request->getMethod() === "OPTIONS") {
        $response = response('', 204);
    } else {
        $response = $next($request);
    }

    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
    $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');

    return $response;
}
}
