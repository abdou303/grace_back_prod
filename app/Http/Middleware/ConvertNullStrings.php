<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConvertNullStrings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        // On parcourt tous les champs de la requête
        array_walk_recursive($input, function (&$item) {
            // Si la valeur est la chaîne "null", on la transforme en vrai null
            if ($item === 'null') {
                $item = null;
            }
        });

        // On remplace les données de la requête par les données nettoyées
        $request->merge($input);

        return $next($request);
    }
}
