<?php

namespace App\Services;

class OutputComparatorService
{
    /**
     * Evalúa si la salida obtenida por la solución del estudiante coincide
     * con la salida esperada considerando flexibilidades de formato.
     *
     * @param string|null $actual Salida producida por el programa (stdout)
     * @param string|null $expected Salida esperada definida en el caso de prueba
     * @return bool True si coincide exactamente o bajo criterios de flexibilidad
     */
    public function isMatch(?string $actual, ?string $expected): bool
    {
        $actual = $actual ?? '';
        $expected = $expected ?? '';

        // 1. Coincidencia exacta a nivel de bytes
        if ($actual === $expected) {
            return true;
        }

        // 2. Coincidencia previa con expresiones regulares (si la salida esperada especifica REGEX:)
        if (str_starts_with(trim($expected), 'REGEX:')) {
            $pattern = trim(substr(trim($expected), 6));
            if (@preg_match($pattern, $actual) === 1 || @preg_match($pattern, $this->normalizeOutput($actual)) === 1) {
                return true;
            }
        }

        // 3. Normalización de saltos de línea (\r\n -> \n), recortes de espacios finales e insensibilidad a mayúsculas/minúsculas
        $normActual = $this->normalizeOutput($actual);
        $normExpected = $this->normalizeOutput($expected);

        if ($normActual === $normExpected || mb_strtolower($normActual) === mb_strtolower($normExpected)) {
            return true;
        }

        // 4. Tokenización: Comparación de palabras y números ignorando diferencias de espacios, saltos de línea y mayúsculas/minúsculas
        $tokensActual = preg_split('/\s+/', trim($normActual), -1, PREG_SPLIT_NO_EMPTY);
        $tokensExpected = preg_split('/\s+/', trim($normExpected), -1, PREG_SPLIT_NO_EMPTY);

        if ($tokensActual === $tokensExpected) {
            return true;
        }

        $lowerTokensActual = array_map('mb_strtolower', $tokensActual);
        $lowerTokensExpected = array_map('mb_strtolower', $tokensExpected);

        if ($lowerTokensActual === $lowerTokensExpected) {
            return true;
        }

        return false;
    }

    /**
     * Normaliza saltos de línea y elimina espacios blancos redundantes al final de cada línea.
     *
     * @param string $str
     * @return string
     */
    public function normalizeOutput(string $str): string
    {
        // Convertir saltos de línea \r\n y \r a \n
        $str = str_replace(["\r\n", "\r"], "\n", $str);

        // Recortar espacios en blanco al final de cada línea
        $lines = explode("\n", $str);
        $trimmedLines = array_map('rtrim', $lines);
        $result = implode("\n", $trimmedLines);

        // Eliminar saltos de línea iniciales y finales
        return trim($result, "\n");
    }
}
