<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInputs
{
    /**
     * Campos exceptuados de la eliminación de sintaxis HTML/código
     * (por ejemplo, envíos de código fuente de programación o markdown).
     *
     * @var array
     */
    protected $except = [
        'codigo',
        'solucion',
        'body_problema',
        'body_editorial',
        'entradas',
        'salidas',
    ];

    /**
     * Intercepta y sanitiza las entradas de la petición HTTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        if (!empty($input)) {
            array_walk_recursive($input, function (&$value, $key) {
                if (is_string($value)) {
                    // Remover caracteres Nulled (Null Byte injection)
                    $value = str_replace(["\0", "\x00"], '', $value);

                    // Si el campo no está en las excepciones, sanitizar contra vectores XSS
                    if (!in_array($key, $this->except, true)) {
                        $value = $this->sanitizeString($value);
                    }
                }
            });

            $request->merge($input);
        }

        return $next($request);
    }

    /**
     * Sanitiza una cadena de texto para mitigar ataques XSS.
     *
     * @param string $value
     * @return string
     */
    protected function sanitizeString(string $value): string
    {
        // Eliminar bloques de script y su contenido
        $value = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $value);

        // Eliminar etiquetas de incrustación de objetos/iframes
        $value = preg_replace('#<(iframe|embed|object|applet)(.*?)>(.*?)</\1>#is', '', $value);

        // Eliminar controladores de eventos inline (onload=, onerror=, onclick=, etc.)
        $value = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $value);
        $value = preg_replace('/on\w+\s*=\s*[^"\'\s>]+/i', '', $value);

        // Eliminar esquemas de URI javascript:
        $value = preg_replace('#javascript:[^\s]*#i', '', $value);

        return $value;
    }
}
