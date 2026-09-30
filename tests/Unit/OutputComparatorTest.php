<?php

namespace Tests\Unit;

use App\Services\OutputComparatorService;
use PHPUnit\Framework\TestCase;

class OutputComparatorTest extends TestCase
{
    protected OutputComparatorService $comparator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->comparator = new OutputComparatorService();
    }

    public function test_exact_match_returns_true()
    {
        $this->assertTrue($this->comparator->isMatch("Hola Mundo\n123", "Hola Mundo\n123"));
    }

    public function test_line_endings_normalization_windows_vs_unix()
    {
        // Output actual con \r\n vs salida esperada con \n
        $actual = "Línea 1\r\nLínea 2\r\n";
        $expected = "Línea 1\nLínea 2\n";

        $this->assertTrue($this->comparator->isMatch($actual, $expected));
    }

    public function test_trailing_spaces_and_blank_lines_tolerance()
    {
        // Output del estudiante con espacios extra al final de línea y saltos de línea al final
        $actual = "Hola Mundo   \n100 200  \n\n";
        $expected = "Hola Mundo\n100 200";

        $this->assertTrue($this->comparator->isMatch($actual, $expected));
    }

    public function test_tokenized_matching_for_format_variations()
    {
        // Variaciones de espacio entre tokens
        $actual = "Respuesta:   42   puntos";
        $expected = "Respuesta: 42 puntos";

        $this->assertTrue($this->comparator->isMatch($actual, $expected));
    }

    public function test_regex_matching_when_expected_starts_with_regex_prefix()
    {
        $actual = "El resultado final es: 99.5%";
        $expected = "REGEX:/^El resultado final es: \d+(\.\d+)?%$/";

        $this->assertTrue($this->comparator->isMatch($actual, $expected));
    }

    public function test_case_insensitive_matching()
    {
        $this->assertTrue($this->comparator->isMatch("PaR\n", "Par\n"));
        $this->assertTrue($this->comparator->isMatch("ImpaR\n", "Impar\n"));
        $this->assertTrue($this->comparator->isMatch("PAR", "Par"));
    }

    public function test_different_outputs_return_false()
    {
        $actual = "Resultado: 50";
        $expected = "Resultado: 100";

        $this->assertFalse($this->comparator->isMatch($actual, $expected));
    }
}
