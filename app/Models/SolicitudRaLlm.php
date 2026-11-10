<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\EnvioSolucionProblema;
use App\Observers\LlmObserver;

#[ObservedBy([LlmObserver::class])]
class SolicitudRaLlm extends Model
{
    use HasFactory;

    public static function promptError($error_compilacion=null, $lenguaje, $resultado=null){
        if(isset($error_compilacion)){
            return "Eres un asistente de programación, el usuario te enviará un código escrito en ".$lenguaje." en que el resultado entrego errores de compilación, debes analizar el código línea por línea e indicar los errores que se encuentren en el código. No debes entregar la solución al error, ni tampoco dar sugerencias de mejorar el código y también ignora los comentarios del código, solo debes indicar los errores que ha cometido el usuario. Para mayor contexto, el error de compilación que le generó el usuario de su código es el siguiente: \n ".$error_compilacion."Básate en el error de compilación para indicar los errores en el código del usuario.";
        }else{
            $queMejorar = "memoria";
            if(str_contains($resultado, 'Time')){
                $queMejorar = "tiempo de ejecucción";
            }
            return "Eres un asistente de programación, el usuario te enviará un código escrito en ".$lenguaje." en que el resultado entrego el siguiente error '".$resultado."'. Debes analizar el código línea por línea y dar sugerencias de cómo podría mejorar el código en cuanto a ".$queMejorar.". No entregues el código mejorado, solo indica las posibles mejoras.";
        }
    }

    public static function promptErrorRespuestaErronea($entradas, $salidas_esperadas, $salidas_usuario, $lenguaje, $enunciado_resumido = null){
        $prompt = 'Eres un asistente de programación, el usuario te enviará un código escrito en '.$lenguaje." en que no entrega las salidas que se esperan, debes analizar el código línea por línea e indicar los errores que se encuentren en el código que impiden la entrega de la salida esperada. No entregues la solución al error ni tampoco dar sugerencias para mejorar el código, ignora los comentarios del código, solo indicar los errores que ha cometido el usuario. Para mayor contexto, la entradas que debe recibir el código es el siguiente:\n".$entradas."\ny el código debe entregar las siguientes salidas:\n".$salidas_esperadas."\npero el código del usuario entrego las siguientes salidas: \n".$salidas_usuario."\n";
        if(isset($enunciado_resumido)){
            $prompt = $prompt."El problema que debe resolver el código es el siguiente: \n '".$enunciado_resumido."'\nNo entregues la solución al problema. Básate en el problema, entradas, salidas esperadas y las salidas generadas por el código para indicar los errores en el código del usuario.";
        }else{
            $prompt = $prompt."Básate en las entradas, salidas esperadas y las salidas generadas por el código para indicar los errores en el código del usuario.";
        }
        return $prompt;
    }

    public static function promptVerificarRestricciones($restricciones, $lenguaje, $enunciado_resumido = null)
    {
        $prompt = "Eres un evaluador automático de restricciones de código en una plataforma académica. Tu función es verificar de forma estricta y EXCLUSIVA si el código enviado por el estudiante en " . $lenguaje . " CUMPLE O NO CUMPLE con las siguientes restricciones impuestas por el profesor:\n\n";
        $prompt .= "RESTRICCIONES DEL PROFESOR:\n\"" . $restricciones . "\"\n\n";
        $prompt .= "INSTRUCCIONES Y REGLAS FUNDAMENTALES:\n";
        $prompt .= "1. EVALÚA ÚNICAMENTE las restricciones técnicas y metodológicas impuestas por el profesor (por ejemplo: funciones/librerías prohibidas o estructuras de control requeridas).\n";
        $prompt .= "2. NO evalúes la lógica, corrección algorítmica ni si el programa resuelve el problema: los errores de lógica o cálculos son evaluados exclusivamente por los casos de prueba del juez, NO por ti. Aunque el algoritmo del estudiante tenga un bug o error lógico (por ejemplo asignar una variable equivocada), si no violó las restricciones del profesor, DEBES responder que 'Cumple'.\n";

        $esSql = str_contains(strtolower($lenguaje), 'sql');
        if ($esSql) {
            $prompt .= "3. REGLAS ESPECÍFICAS PARA CONSULTAS SQL:\n";
            $prompt .= "   - En SQLite / bases de datos, el operador LIKE es nativamente insensible a mayúsculas y minúsculas (case-insensitive) para caracteres ASCII.\n";
            $prompt .= "   - Si la restricción solicita considerar mayúsculas y minúsculas (por ejemplo buscar 'RPG' o 'rpg'), el uso de LIKE, ILIKE, LOWER() o UPPER() para buscar el término CUMPLE plenamente con la restricción.\n";
            $prompt .= "   - Filtros de datos o condiciones en WHERE/HAVING no son prohibiciones metodológicas: si no se utilizan funciones expresamente prohibidas por el profesor, DEBES responder 'Cumple'.\n";
        }

        $prompt .= "4. La PRIMERA LÍNEA de tu respuesta DEBE ser estrictamente 'Cumple' o 'No cumple' (sin comillas, sin tildes ni texto adicional en esa primera línea).\n";
        $prompt .= "5. Si 'Cumple': Añade a partir de la segunda línea una breve confirmación de que se respetaron las restricciones requeridas.\n";
        $prompt .= "6. Si 'No cumple':\n";
        $prompt .= "   - Indica a partir de la segunda línea y de forma general cuál restricción del profesor no se cumplió (por ejemplo: 'El código utiliza la función prohibida max()').\n";
        $prompt .= "   - PROHIBICIÓN ESTRICTA: JAMÁS digas cómo corregir el código, JAMÁS señales en qué línea o qué variable está el error, JAMÁS des pistas de la solución ni muestres cómo debería ser el código.\n";
        return $prompt;
    }



    
    public function envio(): BelongsTo
    {
        return $this->belongsTo(EnvioSolucionProblema::class,'id_envio');
    }
}
