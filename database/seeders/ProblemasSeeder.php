<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Problemas;
use App\Models\Cursos;
use App\Models\Categoria_Problema;
use App\Models\Casos_Pruebas;
use App\Models\LenguajesProgramaciones;
use Carbon\Carbon;

class ProblemasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cursos = Cursos::all();
        $lenguajes = LenguajesProgramaciones::pluck('id')->toArray();

        $catControl = Categoria_Problema::where('nombre', 'Estructuras de Control')->first();
        $catArreglos = Categoria_Problema::where('nombre', 'Arreglos y Vectores')->first();
        $catFunciones = Categoria_Problema::where('nombre', 'Funciones y Recursión')->first();
        $catBusqueda = Categoria_Problema::where('nombre', 'Búsqueda y Ordenamiento')->first();
        $catAvanzadas = Categoria_Problema::where('nombre', 'Estructuras de Datos Avanzadas')->first();
        $catDinamica = Categoria_Problema::where('nombre', 'Programación Dinámica')->first();

        $problemasIniciales = [
            // 1
            [
                'nombre' => 'Sumar A y B',
                'codigo' => 'suma-a-b',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dados dos números enteros A y B en líneas separadas, calcula e imprime la suma total.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "5\n2", "salidas" => "7", "puntos" => 10, "ejemplo" => true],
                    ["entradas" => "25\n4", "salidas" => "29", "puntos" => 20, "ejemplo" => true],
                    ["entradas" => "3500\n932", "salidas" => "4432", "puntos" => 20, "ejemplo" => false],
                ]
            ],
            // 2
            [
                'nombre' => 'Número Par o Impar',
                'codigo' => 'par-o-impar',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dado un entero N, determina si es Par o Impar.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "4", "salidas" => "Par", "puntos" => 25, "ejemplo" => true],
                    ['entradas' => "7", "salidas" => "Impar", "puntos" => 25, "ejemplo" => true],
                ]
            ],
            // 3
            [
                'nombre' => 'Número Mayor de Tres',
                'codigo' => 'mayor-de-tres',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dados tres números enteros A, B y C en líneas independientes, determine cuál es el mayor de ellos.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "10\n45\n23", "salidas" => "45", "puntos" => 25, "ejemplo" => true],
                    ['entradas' => "99\n12\n5", "salidas" => "99", "puntos" => 25, "ejemplo" => false],
                ]
            ],
            // 4
            [
                'nombre' => 'Calculadora de Descuento',
                'codigo' => 'calculadora-descuento',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dado el valor entero de un producto N y el porcentaje de descuento D, calcula el precio final redondeado al entero más cercano.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "1000\n10", "salidas" => "900", "puntos" => 25, "ejemplo" => true],
                    ['entradas' => "5000\n20", "salidas" => "4000", "puntos" => 25, "ejemplo" => false],
                ]
            ],
            // 5
            [
                'nombre' => 'Tabla de Multiplicar',
                'codigo' => 'tabla-multiplicar',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dado un entero N, imprima los primeros 5 múltiplos de N (N*1, N*2, N*3, N*4, N*5) separados por un espacio.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "3", "salidas" => "3 6 9 12 15", "puntos" => 25, "ejemplo" => true],
                    ['entradas' => "7", "salidas" => "7 14 21 28 35", "puntos" => 25, "ejemplo" => false],
                ]
            ],
            // 6
            [
                'nombre' => 'Contar Vocales',
                'codigo' => 'contar-vocales',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dada una cadena de caracteres en minúsculas sin espacios, determine la cantidad total de vocales (a, e, i, o, u).',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "algoritmo", "salidas" => "4", "puntos" => 25, "ejemplo" => true],
                    ['entradas' => "programacion", "salidas" => "5", "puntos" => 25, "ejemplo" => false],
                ]
            ],
            // 7
            [
                'nombre' => 'Suma de Elementos en Arreglo',
                'codigo' => 'suma-elementos-arreglo',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado el tamaño N y en la siguiente línea los N números enteros de un arreglo separados por espacio, calcula la suma total.',
                'habilitar_llm' => true,
                'limite_llm' => 2,
                'categorias' => $catArreglos ? [$catArreglos->id] : [],
                'casos' => [
                    ['entradas' => "5\n1 3 5 7 9", "salidas" => "25", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "4\n10 -5 20 -10", "salidas" => "15", "puntos" => 50, "ejemplo" => false],
                ]
            ],
            // 8
            [
                'nombre' => 'Invertir un Arreglo',
                'codigo' => 'invertir-arreglo',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado N y un arreglo de N elementos enteros, imprima los elementos en orden inverso separados por un espacio.',
                'habilitar_llm' => true,
                'limite_llm' => 2,
                'categorias' => $catArreglos ? [$catArreglos->id] : [],
                'casos' => [
                    ['entradas' => "4\n1 2 3 4", "salidas" => "4 3 2 1", "puntos" => 50, "ejemplo" => true],
                ]
            ],
            // 9
            [
                'nombre' => 'Elemento Máximo y Mínimo',
                'codigo' => 'max-min-arreglo',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado N y N enteros, encuentre e imprima el valor mínimo y máximo del arreglo en una sola línea separados por espacio.',
                'habilitar_llm' => true,
                'limite_llm' => 2,
                'categorias' => $catArreglos ? [$catArreglos->id] : [],
                'casos' => [
                    ['entradas' => "5\n14 2 89 4 11", "salidas" => "2 89", "puntos" => 50, "ejemplo" => true],
                ]
            ],
            // 10
            [
                'nombre' => 'Búsqueda Lineal en Lista',
                'codigo' => 'busqueda-lineal',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado N, los N elementos de la lista y un entero X en la última línea, imprima la primera posición (índice base 0) de X o -1 si no se encuentra.',
                'habilitar_llm' => true,
                'limite_llm' => 2,
                'categorias' => $catBusqueda ? [$catBusqueda->id, $catArreglos->id] : [],
                'casos' => [
                    ['entradas' => "5\n4 8 15 16 23\n15", "salidas" => "2", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "3\n1 2 3\n9", "salidas" => "-1", "puntos" => 50, "ejemplo" => false],
                ]
            ],
            // 11
            [
                'nombre' => 'Búsqueda Binaria',
                'codigo' => 'busqueda-binaria',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado un arreglo ordenado de N enteros y un valor X a buscar, retorne el índice (base 0) donde se ubica X o -1 si no existe.',
                'habilitar_llm' => true,
                'limite_llm' => 2,
                'categorias' => $catBusqueda ? [$catBusqueda->id, $catArreglos->id] : [],
                'casos' => [
                    ['entradas' => "5\n10 20 30 40 50\n30", "salidas" => "2", "puntos" => 50, "ejemplo" => true],
                ]
            ],
            // 12
            [
                'nombre' => 'Ordenamiento por Selección',
                'codigo' => 'ordenamiento-seleccion',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado N y N enteros desordenados, ordene el arreglo de menor a mayor e imprímalo separado por espacios.',
                'habilitar_llm' => true,
                'limite_llm' => 2,
                'categorias' => $catBusqueda ? [$catBusqueda->id] : [],
                'casos' => [
                    ['entradas' => "5\n5 2 9 1 3", "salidas" => "1 2 3 5 9", "puntos" => 50, "ejemplo" => true],
                ]
            ],
            // 13
            [
                'nombre' => 'Factorial con Recursión',
                'codigo' => 'factorial-recursion',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado un número entero no negativo N, calcula su factorial N! utilizando una función recursiva.',
                'habilitar_llm' => true,
                'limite_llm' => 2,
                'categorias' => $catFunciones ? [$catFunciones->id] : [],
                'casos' => [
                    ['entradas' => "5", "salidas" => "120", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "0", "salidas" => "1", "puntos" => 50, "ejemplo" => false],
                ]
            ],
            // 14
            [
                'nombre' => 'Secuencia de Fibonacci',
                'codigo' => 'secuencia-fibonacci',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado un entero N (N >= 0), determine el N-ésimo término de la secuencia de Fibonacci donde F(0)=0 y F(1)=1.',
                'habilitar_llm' => true,
                'limite_llm' => 2,
                'categorias' => $catFunciones ? [$catFunciones->id] : [],
                'casos' => [
                    ['entradas' => "6", "salidas" => "8", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "8", "salidas" => "21", "puntos" => 50, "ejemplo" => false],
                ]
            ],
            // 15
            [
                'nombre' => 'Verificación de Palíndromo',
                'codigo' => 'palindromo-palabras',
                'dificultad' => 'Medio',
                'body_problema' => 'Dada una palabra en minúsculas sin espacios, imprima "SI" si la palabra se lee igual al derecho y al revés, o "NO" en caso contrario.',
                'habilitar_llm' => true,
                'limite_llm' => 2,
                'categorias' => $catFunciones ? [$catFunciones->id] : [],
                'casos' => [
                    ['entradas' => "reconocer", "salidas" => "SI", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "tutorbot", "salidas" => "NO", "puntos" => 50, "ejemplo" => false],
                ]
            ],
            // 16
            [
                'nombre' => 'Matriz Transpuesta',
                'codigo' => 'matriz-transpuesta',
                'dificultad' => 'Medio',
                'body_problema' => 'Dada una matriz de N filas por M columnas, imprima la matriz transpuesta de M filas por N columnas.',
                'habilitar_llm' => true,
                'limite_llm' => 2,
                'categorias' => $catArreglos ? [$catArreglos->id] : [],
                'casos' => [
                    ['entradas' => "2 3\n1 2 3\n4 5 6", "salidas" => "1 4\n2 5\n3 6", "puntos" => 50, "ejemplo" => true],
                ]
            ],
            // 17
            [
                'nombre' => 'Suma de Diagonal Principal',
                'codigo' => 'suma-diagonal-matriz',
                'dificultad' => 'Medio',
                'body_problema' => 'Dada una matriz cuadrada de orden N x N, calcula la suma de los elementos que pertenecen a la diagonal principal.',
                'habilitar_llm' => true,
                'limite_llm' => 2,
                'categorias' => $catArreglos ? [$catArreglos->id] : [],
                'casos' => [
                    ['entradas' => "3\n1 2 3\n4 5 6\n7 8 9", "salidas" => "15", "puntos" => 50, "ejemplo" => true],
                ]
            ],
            // 18
            [
                'nombre' => 'Torres de Hanói',
                'codigo' => 'torres-hanoi',
                'dificultad' => 'Difícil',
                'body_problema' => 'Dado el número N de discos en el problema clásico de las Torres de Hanói, calcula el número mínimo de movimientos requeridos (2^N - 1).',
                'habilitar_llm' => false,
                'limite_llm' => 0,
                'categorias' => $catFunciones ? [$catFunciones->id] : [],
                'casos' => [
                    ['entradas' => "3", "salidas" => "7", "puntos" => 100, "ejemplo" => true],
                    ['entradas' => "5", "salidas" => "31", "puntos" => 100, "ejemplo" => false],
                ]
            ],
            // 19
            [
                'nombre' => 'Camino Mínimo en Matriz',
                'codigo' => 'camino-minimo-matriz',
                'dificultad' => 'Difícil',
                'body_problema' => 'Dada una matriz de N x M números positivos, determine el costo del camino de menor suma desde la esquina superior izquierda a la inferior derecha.',
                'habilitar_llm' => false,
                'limite_llm' => 0,
                'categorias' => $catDinamica ? [$catDinamica->id] : [],
                'casos' => [
                    ['entradas' => "3 3\n1 3 1\n1 5 1\n4 2 1", "salidas" => "7", "puntos" => 100, "ejemplo" => true],
                ]
            ],
            // 20
            [
                'nombre' => 'Paréntesis Balanceados',
                'codigo' => 'parentesis-balanceados',
                'dificultad' => 'Difícil',
                'body_problema' => 'Dada una cadena compuesta exclusivamente de caracteres de paréntesis () [] {}, determine si la estructura de agrupación está correctamente balanceada ("Correcto" o "Incorrecto").',
                'habilitar_llm' => false,
                'limite_llm' => 0,
                'categorias' => $catAvanzadas ? [$catAvanzadas->id] : [],
                'casos' => [
                    ['entradas' => "{[()]}", "salidas" => "Correcto", "puntos" => 100, "ejemplo" => true],
                    ['entradas' => "{[(])}", "salidas" => "Incorrecto", "puntos" => 100, "ejemplo" => false],
                ]
            ],
        ];

        $cursoMap = $cursos->keyBy('codigo');

        foreach ($problemasIniciales as $pData) {
            $catIds = $pData['categorias'];
            unset($pData['categorias']);
            
            $cursoCodigo = $pData['curso_codigo'] ?? 'IN1045C';
            unset($pData['curso_codigo']);

            $problema = Problemas::create([
                'nombre' => $pData['nombre'],
                'codigo' => $pData['codigo'],
                'dificultad' => $pData['dificultad'],
                'body_problema' => $pData['body_problema'],
                'habilitar_llm' => $pData['habilitar_llm'],
                'limite_llm' => $pData['limite_llm'],
                'visible' => true,
            ]);

            $targetCurso = $cursoMap->get($cursoCodigo) ?? $cursos->first();
            if ($targetCurso) {
                $problema->cursos()->sync([$targetCurso->id]);
            }

            if (!empty($lenguajes)) {
                $problema->lenguajes()->sync($lenguajes);
            }

            if (!empty($catIds)) {
                $problema->categorias()->sync($catIds);
            }

            $problema->casos_de_prueba()->createMany($pData['casos']);
            $problema->puntaje_total = collect($pData['casos'])->sum('puntos');
            $problema->save();
        }
    }
}
