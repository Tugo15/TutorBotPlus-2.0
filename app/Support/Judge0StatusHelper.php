<?php

namespace App\Support;

class Judge0StatusHelper
{
    /**
     * Traduce los estados y mensajes de error de Judge0 del inglés al español.
     */
    public static function traducir(?string $status): string
    {
        $valor = trim((string) $status);
        if ($valor === '') {
            return 'Sin Evaluación';
        }

        $mapa = [
            'In Queue' => 'En Cola',
            'Processing' => 'Procesando',
            'In Process' => 'En Proceso',
            'Accepted' => 'Aceptado',
            'Wrong Answer' => 'Respuesta Incorrecta',
            'Time Limit Exceeded' => 'Tiempo Límite Excedido',
            'Compilation Error' => 'Error de Compilación',
            'Memory Limit Exceeded' => 'Memoria Límite Excedida',
            'Output Limit Exceeded' => 'Límite de Salida Excedido',
            'Runtime Error' => 'Error de Ejecución',
            'Runtime Error (SIGSEGV)' => 'Error de Ejecución (Fallo de Segmentación / SIGSEGV)',
            'Runtime Error (SIGXFSZ)' => 'Error de Ejecución (Archivo Muy Grande / SIGXFSZ)',
            'Runtime Error (SIGFPE)' => 'Error de Ejecución (División por Cero / SIGFPE)',
            'Runtime Error (SIGABRT)' => 'Error de Ejecución (Abortado / SIGABRT)',
            'Runtime Error (NZEC)' => 'Error de Ejecución (Código de Salida No Cero / NZEC)',
            'Runtime Error (Other)' => 'Error de Ejecución (Otro)',
            'Internal Error' => 'Error Interno del Juez',
            'Exec Format Error' => 'Error de Formato de Ejecución',
        ];

        if (isset($mapa[$valor])) {
            return $mapa[$valor];
        }

        if (str_starts_with($valor, 'Runtime Error')) {
            return preg_replace('/^Runtime Error\b/', 'Error de Ejecución', $valor) ?? $valor;
        }

        return $valor;
    }
}
