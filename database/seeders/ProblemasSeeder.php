<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Problemas;
use App\Models\Cursos;
use App\Models\Categoria_Problema;
use App\Models\Casos_Pruebas;
use App\Models\LenguajesProgramaciones;
use Illuminate\Support\Facades\Storage;
use PDO;
use ZipArchive;

class ProblemasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cursosMap = Cursos::all()->keyBy('codigo');

        $sqlLenguaje = LenguajesProgramaciones::where('nombre', 'LIKE', '%sql%')->orWhere('abreviatura', 'LIKE', '%sql%')->first();
        $nonSqlLenguajes = LenguajesProgramaciones::where('nombre', 'NOT LIKE', '%sql%')->where('abreviatura', 'NOT LIKE', '%sql%')->pluck('id')->toArray();

        $catControl = Categoria_Problema::where('nombre', 'Estructuras de Control')->first();
        $catArreglos = Categoria_Problema::where('nombre', 'Arreglos y Vectores')->first();
        $catFunciones = Categoria_Problema::where('nombre', 'Funciones y Recursión')->first();
        $catBusqueda = Categoria_Problema::where('nombre', 'Búsqueda y Ordenamiento')->first();
        $catAvanzadas = Categoria_Problema::where('nombre', 'Estructuras de Datos Avanzadas')->first();
        $catDinamica = Categoria_Problema::where('nombre', 'Programación Dinámica')->first();
        $catBD = Categoria_Problema::where('nombre', 'Consultas SQL')->first() ?? $catControl;

        $directory = storage_path('app/public/archivos_adicionales');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Generar bases de datos SQLite para problemas de SQL
        $this->crearBaseDatosSQLiteClientes($directory);
        $this->crearBaseDatosSQLiteProductos($directory);
        $this->crearBaseDatosSQLiteEmpleados($directory);
        $this->crearBaseDatosSQLitePedidos($directory);
        $this->crearBaseDatosSQLiteProyectos($directory);

        $problemasIniciales = [
            // =========================================================================
            // 1. INTRODUCCIÓN A LA INGENIERÍA INFORMÁTICA (IN1039C)
            // =========================================================================
            [
                'nombre' => 'Sumar A y B',
                'codigo' => 'suma-a-b',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dados dos números enteros A y B en líneas separadas, calcula e imprime la suma total.',
                'restricciones' => null,
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1039C'],
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "5\n2", "salidas" => "7", "puntos" => 10, "ejemplo" => true],
                    ["entradas" => "25\n4", "salidas" => "29", "puntos" => 20, "ejemplo" => true],
                    ["entradas" => "3500\n932", "salidas" => "4432", "puntos" => 20, "ejemplo" => false],
                ]
            ],
            [
                'nombre' => 'Número Par o Impar',
                'codigo' => 'par-o-impar',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dado un entero N, determina si es Par o Impar.',
                'restricciones' => null,
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1039C'],
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "4", "salidas" => "Par", "puntos" => 25, "ejemplo" => true],
                    ['entradas' => "7", "salidas" => "Impar", "puntos" => 25, "ejemplo" => true],
                ]
            ],
            [
                'nombre' => 'Área de un Triángulo',
                'codigo' => 'area-triangulo',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dadas la base B y la altura H de un triángulo en líneas separadas (números flotantes o enteros), calcula su área utilizando la fórmula (B * H) / 2. Imprime el resultado.',
                'restricciones' => 'Prohibido usar funciones de librerías matemáticas avanzadas. Utilizar operaciones aritméticas directas.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1039C'],
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "10\n5", "salidas" => "25.0", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "7\n3", "salidas" => "10.5", "puntos" => 50, "ejemplo" => false],
                ]
            ],

            // =========================================================================
            // 2. TALLER DE PROGRAMACIÓN (IN1045C)
            // =========================================================================
            [
                'nombre' => 'Tabla de Multiplicar con For',
                'codigo' => 'tabla-multiplicar-for',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dado un entero N, imprima los primeros 5 múltiplos de N (N*1, N*2, N*3, N*4, N*5) separados por un espacio.',
                'restricciones' => 'Debe utilizar únicamente la estructura iterativa for. Prohibido usar ciclo while.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1045C'],
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "3", "salidas" => "3 6 9 12 15", "puntos" => 25, "ejemplo" => true],
                    ['entradas' => "7", "salidas" => "7 14 21 28 35", "puntos" => 25, "ejemplo" => false],
                ]
            ],
            [
                'nombre' => 'Contador de Vocales',
                'codigo' => 'contador-vocales',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dada una palabra en minúsculas, cuenta cuántas vocales (a, e, i, o, u) contiene e imprime el total.',
                'restricciones' => 'Utilizar ciclo while para iterar sobre los caracteres. Prohibido usar funciones de reemplazo o conteo nativas.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1045C'],
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "programacion", "salidas" => "5", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "xyz", "salidas" => "0", "puntos" => 50, "ejemplo" => false],
                ]
            ],
            [
                'nombre' => 'Mayor de Tres Números',
                'codigo' => 'mayor-tres-numeros',
                'dificultad' => 'Fácil',
                'body_problema' => 'Dados tres enteros A, B y C en líneas separadas, determina e imprime el número mayor.',
                'restricciones' => 'Prohibido usar la función max(). Utilizar únicamente condicionales if/else encadenados o anidados.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1045C'],
                'categorias' => $catControl ? [$catControl->id] : [],
                'casos' => [
                    ['entradas' => "15\n42\n8", "salidas" => "42", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "100\n50\n75", "salidas" => "100", "puntos" => 50, "ejemplo" => false],
                ]
            ],

            // =========================================================================
            // 3. TALLER DE PROGRAMACIÓN II (IN1071C)
            // =========================================================================
            [
                'nombre' => 'Factorial Recursivo',
                'codigo' => 'factorial-recursion',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado un número entero no negativo N, calcula su factorial N! utilizando una función recursiva.',
                'restricciones' => 'Debe ser implementado mediante función recursiva. Prohibido usar estructuras de ciclo (for, while).',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1071C'],
                'categorias' => $catFunciones ? [$catFunciones->id] : [],
                'casos' => [
                    ['entradas' => "5", "salidas" => "120", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "0", "salidas" => "1", "puntos" => 50, "ejemplo" => false],
                ]
            ],
            [
                'nombre' => 'Verificación de Palíndromo',
                'codigo' => 'palindromo-palabras',
                'dificultad' => 'Medio',
                'body_problema' => 'Dada una palabra en minúsculas sin espacios, imprima "SI" si la palabra se lee igual al derecho y al revés, o "NO" en caso contrario.',
                'restricciones' => 'Prohibido usar funciones de inversión de cadenas como reverse() o slicing con paso negativo [::-1].',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1071C'],
                'categorias' => $catFunciones ? [$catFunciones->id] : [],
                'casos' => [
                    ['entradas' => "reconocer", "salidas" => "SI", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "tutorbot", "salidas" => "NO", "puntos" => 50, "ejemplo" => false],
                ]
            ],
            [
                'nombre' => 'Fibonacci Recursivo',
                'codigo' => 'fibonacci-recursion',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado un número N (N >= 0), calcula el N-ésimo término de la sucesión de Fibonacci (F(0)=0, F(1)=1) mediante una función recursiva.',
                'restricciones' => 'Debe implementar obligatoriamente la función recursiva. Prohibido resolver mediante enfoque iterativo puro.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1071C'],
                'categorias' => $catFunciones ? [$catFunciones->id] : [],
                'casos' => [
                    ['entradas' => "6", "salidas" => "8", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "10", "salidas" => "55", "puntos" => 50, "ejemplo" => false],
                ]
            ],

            // =========================================================================
            // 4. ESTRUCTURA DE DATOS (IN1069C)
            // =========================================================================
            [
                'nombre' => 'Búsqueda de Elemento en Arreglo',
                'codigo' => 'busqueda-arreglo',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado un tamaño N, una lista de N enteros y un elemento K a buscar en la siguiente línea, imprima la posición (índice 0-basado) del elemento K o -1 si no existe.',
                'restricciones' => 'Debe utilizar ciclo while para recorrer el arreglo.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1069C'],
                'categorias' => $catArreglos ? [$catArreglos->id] : [],
                'casos' => [
                    ['entradas' => "5\n10 20 30 40 50\n30", "salidas" => "2", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "4\n1 2 3 4\n9", "salidas" => "-1", "puntos" => 50, "ejemplo" => false],
                ]
            ],
            [
                'nombre' => 'Búsqueda Binaria en Vector Ordenado',
                'codigo' => 'busqueda-binaria',
                'dificultad' => 'Medio',
                'body_problema' => 'Dado un tamaño N, una lista ordenada de N enteros en una sola línea y un valor K a buscar en la línea siguiente, implementa la búsqueda binaria para retornar la posición de K o -1 si no se encuentra.',
                'restricciones' => 'Implementar la búsqueda binaria mediante ciclo while. Prohibido usar búsqueda lineal O(N) o funciones nativas de búsqueda.',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1069C'],
                'categorias' => $catBusqueda ? [$catBusqueda->id] : [],
                'casos' => [
                    ['entradas' => "6\n2 5 8 12 16 23\n12", "salidas" => "3", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "5\n1 3 5 7 9\n4", "salidas" => "-1", "puntos" => 50, "ejemplo" => false],
                ]
            ],
            [
                'nombre' => 'Balanceo de Paréntesis con Pila',
                'codigo' => 'balanceo-parentesis',
                'dificultad' => 'Difícil',
                'body_problema' => 'Dada una cadena de paréntesis "(" y ")", determina si la secuencia está correctamente balanceada. Imprime "BALANCEADO" o "DESBALANCEADO".',
                'restricciones' => 'Prohibido usar expresiones regulares. Debe implementar el chequeo utilizando el concepto de estructura Pila (Stack).',
                'habilitar_llm' => true,
                'limite_llm' => 3,
                'is_sql' => false,
                'curso_codigos' => ['IN1069C'],
                'categorias' => $catAvanzadas ? [$catAvanzadas->id] : [],
                'casos' => [
                    ['entradas' => "(())()", "salidas" => "BALANCEADO", "puntos" => 50, "ejemplo" => true],
                    ['entradas' => "(()", "salidas" => "DESBALANCEADO", "puntos" => 50, "ejemplo" => false],
                ]
            ],

            // =========================================================================
            // 5. BASE DE DATOS (IN1075C)
            // =========================================================================
            [
                'nombre' => 'Consulta SQL: Clientes VIP',
                'codigo' => 'sql-clientes-vip',
                'dificultad' => 'Medio',
                'body_problema' => "Dada la estructura de una base de datos relacional con las tablas:\n\n- **clientes**: `id`, `nombre`, `email`\n- **compras**: `id`, `cliente_id`, `monto`, `fecha` \n\nEscribe una consulta SQL que obtenga el `nombre` del cliente y la suma total de sus compras con el alias `total_gastado`, únicamente para aquellos clientes cuyo total gastado sea mayor o igual a 500.\n\nLa lista resultante debe ser ordenada descendentemente por el `total_gastado`.",
                'restricciones' => 'Utilizar únicamente INNER JOIN explícito y agrupamiento con GROUP BY y HAVING. Prohibido usar subconsultas.',
                'archivo_adicional' => 'sql-clientes-vip.zip',
                'habilitar_llm' => true,
                'limite_llm' => 5,
                'is_sql' => true,
                'curso_codigos' => ['IN1075C'],
                'categorias' => $catBD ? [$catBD->id] : [],
                'casos' => [
                    ['entradas' => "", "salidas" => "Juan Perez|1250.5\nMaria Lopez|780.0", "puntos" => 100, "ejemplo" => true],
                ]
            ],
            [
                'nombre' => 'Consulta SQL: Productos e Inventario',
                'codigo' => 'sql-productos-stock',
                'dificultad' => 'Fácil',
                'body_problema' => "Se dispone de la base de datos de inventario con las siguientes tablas:\n\n- **productos**: `id`, `nombre`, `precio`, `stock`\n- **ventas**: `id`, `producto_id`, `cantidad`, `fecha` \n\nEscribe una consulta SQL que obtenga el `nombre` del producto, su `stock` y la suma de unidades vendidas con el alias `total_vendido`, únicamente para aquellos productos cuyo `stock` sea menor a 15.\n\nEl resultado debe ordenarse descendentemente por `stock`.",
                'restricciones' => 'Utilizar únicamente INNER JOIN y cláusula WHERE. Prohibido usar subconsultas.',
                'archivo_adicional' => 'sql-productos-stock.zip',
                'habilitar_llm' => true,
                'limite_llm' => 5,
                'is_sql' => true,
                'curso_codigos' => ['IN1075C'],
                'categorias' => $catBD ? [$catBD->id] : [],
                'casos' => [
                    ['entradas' => "", "salidas" => "Teclado Mecanico|10|20\nAudifonos|8|0\nMouse Gamer|5|2", "puntos" => 100, "ejemplo" => true],
                ]
            ],
            [
                'nombre' => 'Consulta SQL: Pedidos Recientes',
                'codigo' => 'sql-pedidos-recientes',
                'dificultad' => 'Fácil',
                'body_problema' => "Dada la tabla **pedidos** (`id`, `cliente`, `monto`, `fecha`), escribe una consulta SQL que obtenga el `cliente` y el `monto` de los pedidos realizados a partir del '2024-03-01' inclusive.\n\nOrdena el resultado por `fecha` de forma descendente.",
                'restricciones' => 'Utilizar la cláusula WHERE con comparación de fecha y ORDER BY. Prohibido usar JOIN.',
                'archivo_adicional' => 'sql-pedidos-recientes.zip',
                'habilitar_llm' => true,
                'limite_llm' => 5,
                'is_sql' => true,
                'curso_codigos' => ['IN1075C'],
                'categorias' => $catBD ? [$catBD->id] : [],
                'casos' => [
                    ['entradas' => "", "salidas" => "Daniel Lopez|310.0\nAna Gomez|230.5\nCarlos Perez|150.0", "puntos" => 100, "ejemplo" => true],
                ]
            ],

            // =========================================================================
            // 6. TALLER DE BASE DE DATOS (IN1078C)
            // =========================================================================
            [
                'nombre' => 'Consulta SQL: Salarios por Departamento',
                'codigo' => 'sql-empleados-deptos',
                'dificultad' => 'Difícil',
                'body_problema' => "Dada la estructura organizacional con las tablas:\n\n- **departamentos**: `id`, `nombre`\n- **empleados**: `id`, `nombre`, `sueldo`, `departamento_id`\n\nEscribe una consulta SQL que obtenga el `nombre` del departamento y el salario promedio de sus empleados con el alias `promedio_sueldo`, únicamente para los departamentos con un promedio mayor a 800000.",
                'restricciones' => 'Utilizar JOIN explícito, GROUP BY y HAVING. Prohibido utilizar la palabra clave DISTINCT.',
                'archivo_adicional' => 'sql-empleados-deptos.zip',
                'habilitar_llm' => true,
                'limite_llm' => 5,
                'is_sql' => true,
                'curso_codigos' => ['IN1078C'],
                'categorias' => $catBD ? [$catBD->id] : [],
                'casos' => [
                    ['entradas' => "", "salidas" => "Tecnología|1075000.0", "puntos" => 100, "ejemplo" => true],
                ]
            ],
            [
                'nombre' => 'Consulta SQL: Auditoría de Proyectos sin Tareas',
                'codigo' => 'sql-proyectos-tareas',
                'dificultad' => 'Difícil',
                'body_problema' => "Dada la estructura de gestión de proyectos:\n\n- **proyectos**: `id`, `nombre_proyecto`, `presupuesto`\n- **tareas**: `id`, `proyecto_id`, `descripcion`, `estado`\n\nEscribe una consulta SQL que obtenga el `nombre_proyecto` y el `presupuesto` de aquellos proyectos que NO tienen ninguna tarea asignada.\n\nOrdena el resultado descendentemente por `presupuesto`.",
                'restricciones' => 'Utilizar LEFT JOIN y filtrar mediante WHERE t.id IS NULL. Prohibido usar subconsultas como NOT IN o NOT EXISTS.',
                'archivo_adicional' => 'sql-proyectos-tareas.zip',
                'habilitar_llm' => true,
                'limite_llm' => 5,
                'is_sql' => true,
                'curso_codigos' => ['IN1078C'],
                'categorias' => $catBD ? [$catBD->id] : [],
                'casos' => [
                    ['entradas' => "", "salidas" => "Migración Cloud|4500000.0\nRediseño Web|2000000.0", "puntos" => 100, "ejemplo" => true],
                ]
            ],
        ];

        foreach ($problemasIniciales as $pData) {
            $catIds = $pData['categorias'];
            unset($pData['categorias']);

            $isSql = $pData['is_sql'] ?? false;
            unset($pData['is_sql']);

            $cursoCodigos = $pData['curso_codigos'] ?? [];
            unset($pData['curso_codigos']);

            $archivoAdicional = $pData['archivo_adicional'] ?? null;
            unset($pData['archivo_adicional']);

            $problema = Problemas::create([
                'nombre' => $pData['nombre'],
                'codigo' => $pData['codigo'],
                'dificultad' => $pData['dificultad'],
                'body_problema' => $pData['body_problema'],
                'body_problema_resumido' => $pData['nombre'],
                'restricciones' => $pData['restricciones'],
                'archivo_adicional' => $archivoAdicional,
                'habilitar_llm' => $pData['habilitar_llm'],
                'limite_llm' => $pData['limite_llm'],
                'visible' => true,
            ]);

            // Asignación de Cursos específicos
            $targetCursoIds = [];
            foreach ($cursoCodigos as $codigo) {
                if (isset($cursosMap[$codigo])) {
                    $targetCursoIds[] = $cursosMap[$codigo]->id;
                }
            }
            $problema->cursos()->sync($targetCursoIds);

            // Asignación de Lenguajes
            if ($isSql) {
                if ($sqlLenguaje) {
                    $problema->lenguajes()->sync([$sqlLenguaje->id]);
                }
            } else {
                if (!empty($nonSqlLenguajes)) {
                    $problema->lenguajes()->sync($nonSqlLenguajes);
                }
            }

            if (!empty($catIds)) {
                $problema->categorias()->sync($catIds);
            }

            $problema->casos_de_prueba()->createMany($pData['casos']);
            $problema->puntaje_total = collect($pData['casos'])->sum('puntos');
            $problema->save();
        }
    }

    private function crearBaseDatosSQLiteClientes($directory)
    {
        $dbPath = $directory . '/db.sqlite';
        if (file_exists($dbPath)) unlink($dbPath);

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("
        CREATE TABLE clientes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            email TEXT NOT NULL
        );
        CREATE TABLE compras (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cliente_id INTEGER NOT NULL,
            monto REAL NOT NULL,
            fecha DATE NOT NULL,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        );
        INSERT INTO clientes (id, nombre, email) VALUES (1, 'Juan Perez', 'juan@example.com');
        INSERT INTO clientes (id, nombre, email) VALUES (2, 'Maria Lopez', 'maria@example.com');
        INSERT INTO clientes (id, nombre, email) VALUES (3, 'Carlos Soto', 'carlos@example.com');

        INSERT INTO compras (id, cliente_id, monto, fecha) VALUES (1, 1, 500.0, '2024-01-01');
        INSERT INTO compras (id, cliente_id, monto, fecha) VALUES (2, 1, 750.5, '2024-01-02');
        INSERT INTO compras (id, cliente_id, monto, fecha) VALUES (3, 2, 780.0, '2024-01-03');
        INSERT INTO compras (id, cliente_id, monto, fecha) VALUES (4, 3, 200.0, '2024-01-04');
        ");

        $zipPath = $directory . '/sql-clientes-vip.zip';
        if (file_exists($zipPath)) unlink($zipPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($dbPath, 'db.sqlite');
            $zip->close();
        }
    }

    private function crearBaseDatosSQLiteProductos($directory)
    {
        $dbPath = $directory . '/db_prod.sqlite';
        if (file_exists($dbPath)) unlink($dbPath);

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("
        CREATE TABLE productos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            precio REAL NOT NULL,
            stock INTEGER NOT NULL
        );
        CREATE TABLE ventas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            producto_id INTEGER NOT NULL,
            cantidad INTEGER NOT NULL,
            fecha DATE NOT NULL,
            FOREIGN KEY (producto_id) REFERENCES productos(id)
        );
        INSERT INTO productos (id, nombre, precio, stock) VALUES (1, 'Teclado Mecanico', 45000, 10);
        INSERT INTO productos (id, nombre, precio, stock) VALUES (2, 'Mouse Gamer', 25000, 5);
        INSERT INTO productos (id, nombre, precio, stock) VALUES (3, 'Monitor 24', 120000, 20);
        INSERT INTO productos (id, nombre, precio, stock) VALUES (4, 'Audifonos', 35000, 8);

        INSERT INTO ventas (id, producto_id, cantidad, fecha) VALUES (1, 1, 15, '2024-02-01');
        INSERT INTO ventas (id, producto_id, cantidad, fecha) VALUES (2, 1, 5, '2024-02-02');
        INSERT INTO ventas (id, producto_id, cantidad, fecha) VALUES (3, 2, 2, '2024-02-03');
        ");

        $zipPath = $directory . '/sql-productos-stock.zip';
        if (file_exists($zipPath)) unlink($zipPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($dbPath, 'db.sqlite');
            $zip->close();
        }
    }

    private function crearBaseDatosSQLiteEmpleados($directory)
    {
        $dbPath = $directory . '/db_emp.sqlite';
        if (file_exists($dbPath)) unlink($dbPath);

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("
        CREATE TABLE departamentos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL
        );
        CREATE TABLE empleados (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            sueldo REAL NOT NULL,
            departamento_id INTEGER NOT NULL,
            FOREIGN KEY (departamento_id) REFERENCES departamentos(id)
        );
        INSERT INTO departamentos (id, nombre) VALUES (1, 'Tecnología');
        INSERT INTO departamentos (id, nombre) VALUES (2, 'Ventas');
        INSERT INTO departamentos (id, nombre) VALUES (3, 'Recursos Humanos');

        INSERT INTO empleados (id, nombre, sueldo, departamento_id) VALUES (1, 'Ana Rivas', 1200000, 1);
        INSERT INTO empleados (id, nombre, sueldo, departamento_id) VALUES (2, 'Pedro Soto', 950000, 1);
        INSERT INTO empleados (id, nombre, sueldo, departamento_id) VALUES (3, 'Laura Gomez', 850000, 2);
        INSERT INTO empleados (id, nombre, sueldo, departamento_id) VALUES (4, 'Diego Morales', 600000, 2);
        INSERT INTO empleados (id, nombre, sueldo, departamento_id) VALUES (5, 'Sofia Castro', 700000, 3);
        ");

        $zipPath = $directory . '/sql-empleados-deptos.zip';
        if (file_exists($zipPath)) unlink($zipPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($dbPath, 'db.sqlite');
            $zip->close();
        }
    }

    private function crearBaseDatosSQLitePedidos($directory)
    {
        $dbPath = $directory . '/db_ped.sqlite';
        if (file_exists($dbPath)) unlink($dbPath);

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("
        CREATE TABLE pedidos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cliente TEXT NOT NULL,
            monto REAL NOT NULL,
            fecha DATE NOT NULL
        );
        INSERT INTO pedidos (id, cliente, monto, fecha) VALUES (1, 'Carlos Perez', 150.0, '2024-03-01');
        INSERT INTO pedidos (id, cliente, monto, fecha) VALUES (2, 'Ana Gomez', 230.5, '2024-03-05');
        INSERT INTO pedidos (id, cliente, monto, fecha) VALUES (3, 'Beatriz Silva', 90.0, '2024-02-28');
        INSERT INTO pedidos (id, cliente, monto, fecha) VALUES (4, 'Daniel Lopez', 310.0, '2024-03-10');
        ");

        $zipPath = $directory . '/sql-pedidos-recientes.zip';
        if (file_exists($zipPath)) unlink($zipPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($dbPath, 'db.sqlite');
            $zip->close();
        }
    }

    private function crearBaseDatosSQLiteProyectos($directory)
    {
        $dbPath = $directory . '/db_proy.sqlite';
        if (file_exists($dbPath)) unlink($dbPath);

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("
        CREATE TABLE proyectos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre_proyecto TEXT NOT NULL,
            presupuesto REAL NOT NULL
        );
        CREATE TABLE tareas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            proyecto_id INTEGER NOT NULL,
            descripcion TEXT NOT NULL,
            estado TEXT NOT NULL,
            FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
        );
        INSERT INTO proyectos (id, nombre_proyecto, presupuesto) VALUES (1, 'Sistema CRM', 5000000);
        INSERT INTO proyectos (id, nombre_proyecto, presupuesto) VALUES (2, 'App Movil', 3000000);
        INSERT INTO proyectos (id, nombre_proyecto, presupuesto) VALUES (3, 'Migración Cloud', 4500000);
        INSERT INTO proyectos (id, nombre_proyecto, presupuesto) VALUES (4, 'Rediseño Web', 2000000);

        INSERT INTO tareas (id, proyecto_id, descripcion, estado) VALUES (1, 1, 'Diseño DB', 'Completado');
        INSERT INTO tareas (id, proyecto_id, descripcion, estado) VALUES (2, 2, 'Auth API', 'En Progreso');
        INSERT INTO tareas (id, proyecto_id, descripcion, estado) VALUES (3, 1, 'Frontend Admin', 'Pendiente');
        ");

        $zipPath = $directory . '/sql-proyectos-tareas.zip';
        if (file_exists($zipPath)) unlink($zipPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($dbPath, 'db.sqlite');
            $zip->close();
        }
    }
}
