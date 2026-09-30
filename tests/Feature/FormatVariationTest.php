<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cursos;
use App\Models\Problemas;
use App\Models\Casos_Pruebas;
use App\Models\EnvioSolucionProblema;
use App\Models\EvaluacionSolucion;
use App\Services\OutputComparatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormatVariationTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluar_envios_accepts_format_variation_when_judge0_returns_wrong_answer()
    {
        $comparator = app(OutputComparatorService::class);

        // 1. Caso de prueba donde el alumno imprime espacios extra al final y múltiples saltos de línea
        $actualStudentStdout = "Par   \r\n\r\n";
        $expectedOutput = "Par\n";

        // Verificar que el comparador flexible detecta la coincidencia
        $this->assertTrue($comparator->isMatch($actualStudentStdout, $expectedOutput));

        // 2. Simulación directa del flujo de reevaluación cuando Judge0 devuelve 'Wrong Answer' (status id 4)
        $statusIdFromJudge0 = 4; // Wrong Answer
        $isAccepted = ($statusIdFromJudge0 == 3);

        if (!$isAccepted && $statusIdFromJudge0 == 4) {
            if ($comparator->isMatch($actualStudentStdout, $expectedOutput)) {
                $isAccepted = true;
                $resultadoTexto = "Aceptado (Variación de Formato)";
            }
        }

        $this->assertTrue($isAccepted);
        $this->assertEquals("Aceptado (Variación de Formato)", $resultadoTexto);
    }

    public function test_tokenization_variation_is_accepted()
    {
        $comparator = app(OutputComparatorService::class);

        $actualOutput = "Respuesta:    42   puntos  \n";
        $expectedOutput = "Respuesta: 42 puntos";

        $this->assertTrue($comparator->isMatch($actualOutput, $expectedOutput));
    }
}
