<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FormatRut
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('rut') && !empty($request->input('rut'))) {
            $rut = $request->input('rut');
            
            // 1. Limpiar todo lo que no sea número o la letra k
            $rutLimpio = preg_replace('/[^0-9kK]/', '', $rut);
            
            if (strlen($rutLimpio) >= 2) {
                // 2. Extraer dígito verificador y el resto de los números
                $dv = strtoupper(substr($rutLimpio, -1));
                $numeros = substr($rutLimpio, 0, -1);
                
                // 3. Formatear con puntos y guion
                $numerosFormateados = number_format((int)$numeros, 0, '', '.');
                $rutFinal = $numerosFormateados . '-' . $dv;
                
                // 4. Reemplazar el input original en el request
                $request->merge(['rut' => $rutFinal]);
            }
        }

        return $next($request);
    }
}
