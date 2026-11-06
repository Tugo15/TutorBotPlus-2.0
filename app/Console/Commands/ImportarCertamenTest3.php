<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cursos;
use App\Models\Problemas;
use App\Models\Casos_Pruebas;
use App\Models\Categoria_Problema;
use App\Models\Certamenes;
use App\Models\LenguajesProgramaciones;
use Carbon\Carbon;
use PDO;
use Exception;

class ImportarCertamenTest3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certamen:importar-test3 {--curso= : Código o ID del curso objetivo (ej. IN1075C)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa automáticamente el Certamen 3 (Venta de Juegos / Steam 2025) con sus 14 problemas, salidas calculadas y banco por categorías';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("================================================================================");
        $this->info(" IMPORTADOR AUTOMÁTICO: TEST 3 - VENTA DE JUEGOS DIGITALES (STEAM 2025)");
        $this->info("================================================================================");

        // 1. Obtener curso objetivo
        $cursoCodigoOrId = $this->option('curso');
        $curso = null;

        if ($cursoCodigoOrId) {
            $curso = Cursos::where('codigo', $cursoCodigoOrId)
                ->orWhere('id', $cursoCodigoOrId)
                ->first();
        }

        if (!$curso) {
            // Buscar por defecto cursos de Base de Datos
            $curso = Cursos::where('nombre', 'LIKE', '%Base de Datos%')->first();
        }

        if (!$curso) {
            $curso = Cursos::first();
        }

        if (!$curso) {
            $this->error("Error: No se encontró ningún curso en la base de datos.");
            return 1;
        }

        $this->info("-> Curso asignado: [{$curso->codigo}] {$curso->nombre} (ID: {$curso->id})");

        // 2. Lenguaje SQL
        $sqlLang = LenguajesProgramaciones::where('nombre', 'LIKE', '%sql%')
            ->orWhere('abreviatura', 'LIKE', '%sql%')
            ->first();

        if (!$sqlLang) {
            $sqlLang = LenguajesProgramaciones::create([
                'nombre' => 'SQL (SQLite / Postgres Compatible)',
                'abreviatura' => 'sql',
                'id_judge0' => 82,
                'extension' => 'sql',
                'activo' => true
            ]);
        }
        $this->info("-> Lenguaje SQL: [{$sqlLang->id}] {$sqlLang->nombre}");

        // 3. Verificar base de datos SQLite
        $sqliteRelPath = 'venta_juegos.sqlite';
        $sqliteFullPath = storage_path('app/public/archivos_adicionales/' . $sqliteRelPath);

        if (!file_exists($sqliteFullPath)) {
            $this->error("Error: No se encontró el archivo SQLite en: {$sqliteFullPath}");
            return 1;
        }
        $this->info("-> Archivo SQLite verificado: {$sqliteRelPath}");

        // 4. Crear o encontrar las 3 Categorías
        $cat1 = Categoria_Problema::firstOrCreate(
            ['nombre' => 'Test 3: Filtros y Relaciones'],
            ['descripcion' => 'Consultas básicas, filtros relacionales, proyecciones y joins entre tablas.']
        );
        $cat2 = Categoria_Problema::firstOrCreate(
            ['nombre' => 'Test 3: Agrupaciones y Agregación'],
            ['descripcion' => 'Consultas con funciones de agregación (COUNT, SUM, AVG) y cláusulas GROUP BY / HAVING.']
        );
        $cat3 = Categoria_Problema::firstOrCreate(
            ['nombre' => 'Test 3: Subconsultas y Correlacionadas'],
            ['descripcion' => 'Consultas complejas, subconsultas escalares, correlacionadas y operadores lógicos avanzados.']
        );

        $this->info("-> Categorías configuradas:");
        $this->line("   - Cat 1: {$cat1->nombre} (ID: {$cat1->id})");
        $this->line("   - Cat 2: {$cat2->nombre} (ID: {$cat2->id})");
        $this->line("   - Cat 3: {$cat3->nombre} (ID: {$cat3->id})");

        // 5. Conexión SQLite para calcular las salidas de prueba
        $pdo = new PDO('sqlite:' . $sqliteFullPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Funciones auxiliares para compatibilidad
        $pdo->sqliteCreateFunction('date_part', function($part, $date) {
            if (!$date) return null;
            $time = strtotime($date);
            if ($time === false) return null;
            switch (strtolower((string)$part)) {
                case 'year': return (int)date('Y', $time);
                case 'month': return (int)date('m', $time);
                case 'day': return (int)date('d', $time);
                default: return null;
            }
        }, 2);

        // 6. Definición de los 14 problemas
        $problemasData = $this->getDefinicionProblemas();

        $this->info("\n-> Procesando y cargando los " . count($problemasData) . " problemas:");

        $bar = $this->output->createProgressBar(count($problemasData));
        $bar->start();

        foreach ($problemasData as $p) {
            // Calcular salida esperada
            $salidaEsperada = $this->calcularSalidaSql($pdo, $p['sql']);

            // Determinar categoría
            $catId = $cat1->id;
            if ($p['grupo'] == 2) $catId = $cat2->id;
            if ($p['grupo'] == 3) $catId = $cat3->id;

            // Formatear cuerpo con diagrama y enunciado
            $bodyHtml = $this->construirEnunciadoMarkdown($p);

            // Crear o actualizar problema
            $problema = Problemas::updateOrCreate(
                ['codigo' => $p['codigo']],
                [
                    'nombre' => $p['nombre'],
                    'dificultad' => $p['dificultad'],
                    'body_problema' => $bodyHtml,
                    'body_problema_resumido' => $p['nombre'],
                    'restricciones' => $p['restricciones'] ?? null,
                    'habilitar_llm' => false,
                    'limite_llm' => 0,
                    'is_sql' => true,
                    'visible' => true,
                    'archivo_adicional' => $sqliteRelPath,
                    'puntaje_total' => 100,
                ]
            );

            // Asociar al curso, categoría y lenguaje
            $problema->cursos()->syncWithoutDetaching([$curso->id]);
            $problema->lenguajes()->sync([$sqlLang->id]);
            $problema->categorias()->sync([$catId]);

            // Recrear caso de prueba
            $problema->casos_de_prueba()->delete();
            $problema->casos_de_prueba()->create([
                'entradas' => '',
                'salidas' => $salidaEsperada,
                'puntos' => 100,
                'ejemplo' => false,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // 7. Crear o actualizar el Certamen
        $this->info("-> Configurando Certamen oficial...");

        $certamen = Certamenes::updateOrCreate(
            [
                'nombre' => 'Test 3 - Plataforma de Venta de Juegos (Steam 2025)',
                'id_curso' => $curso->id,
            ],
            [
                'descripcion' => 'Evaluación práctica oficial sobre consultas SQL avanzadas en bases de datos relacionales (Filtros, Joins, Agrupaciones y Subconsultas). Cada estudiante resolverá 1 variante al azar de cada grupo de complejidad.',
                'fecha_inicio' => Carbon::now()->subHours(1),
                'fecha_termino' => Carbon::now()->addDays(20),
                'penalizacion_error' => 0.20,
                'cantidad_penalizacion' => 3,
                'restriccion_red' => false,
                'ips_autorizadas' => null,
                'dificultad' => 'Medio',
                'cantidad_problemas' => 3,
            ]
        );

        // Vincular las 3 categorías en banco_problemas_certamenes
        $certamen->categorias()->sync([$cat1->id, $cat2->id, $cat3->id]);

        $this->info("================================================================================");
        $this->info(" ¡IMPORTACIÓN COMPLETADA CON ÉXITO!");
        $this->info("================================================================================");
        $this->line(" Certamen ID:          {$certamen->id}");
        $this->line(" Nombre:               {$certamen->nombre}");
        $this->line(" Curso:                [{$curso->codigo}] {$curso->nombre}");
        $this->line(" Total de Ejercicios:  " . count($problemasData) . " problemas (14 variantes)");
        $this->line(" Banco de Categorías:  3 categorías (1 problema aleatorio por categoría por alumno)");
        $this->line(" Base de datos SQLite: {$sqliteRelPath}");
        $this->info("================================================================================\n");

        return 0;
    }

    /**
     * Ejecuta la consulta SQL maestra en SQLite y formatea las filas como col1|col2|...
     */
    protected function calcularSalidaSql(PDO $pdo, string $sql): string
    {
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);
        $outputRows = [];

        foreach ($rows as $row) {
            $formattedRow = array_map(function($val) {
                if ($val === null) return '';
                if ($val === true || $val === '1' && strlen($val) === 1) return $val;
                return $val;
            }, $row);
            $outputRows[] = implode('|', $formattedRow);
        }

        return implode("\n", $outputRows);
    }

    /**
     * Construye el enunciado Markdown/HTML con el diagrama y el formato visual adecuado.
     */
    protected function construirEnunciadoMarkdown(array $p): string
    {
        $md = "### " . htmlspecialchars($p['nombre']) . "\n\n";
        $md .= "#### Descripción del Modelo de Datos\n";
        $md .= "Se dispone de la base de datos de una plataforma de venta y distribución de videojuegos digitales (tipo Steam).\n\n";
        $md .= "![Diagrama Entidad Relación](/assets/img/certamenes/steam_2025.png)\n\n";
        $md .= "**Tablas del sistema:**\n";
        $md .= "- `pais (m49, nombre, moneda, conversion)`\n";
        $md .= "- `usuario (correo, nombre, alias, pais, password)`\n";
        $md .= "- `juego (codigo, titulo, descripcion, precio, descuento, anio)`\n";
        $md .= "- `compra (codigo, juego, usuario, fecha, precio_final)`\n";
        $md .= "- `critica (juego, usuario, valoracion, texto, fecha)`\n";
        $md .= "- `amistad (usuario_solicitante, usuario_solicitado, aceptado)`\n\n";
        
        $md .= "---\n\n";
        $md .= "#### Requerimiento de la Consulta\n";
        $md .= $p['enunciado'] . "\n\n";

        if (!empty($p['columnas'])) {
            $md .= "#### Columnas a proyectar en el resultado:\n";
            foreach ($p['columnas'] as $col) {
                $md .= "- " . $col . "\n";
            }
            $md .= "\n";
        }

        if (!empty($p['orden'])) {
            $md .= "**Orden requerido:** " . $p['orden'] . "\n\n";
        }

        if (!empty($p['restricciones'])) {
            $md .= "> **Nota / Restricciones:** " . $p['restricciones'] . "\n\n";
        }

        return $md;
    }

    /**
     * Catálogo completo de los 14 problemas de Test 3.
     */
    protected function getDefinicionProblemas(): array
    {
        return [
            // =========================================================================
            // GRUPO 1: FILTROS Y JOINS (1A, 1B, 1C)
            // =========================================================================
            [
                'grupo' => 1,
                'codigo' => 'test3-1a-usuarios-chilenos',
                'nombre' => 'Test 3 - 1A: Usuarios Chilenos y Compras',
                'dificultad' => 'Fácil',
                'enunciado' => 'Mostrar una lista de usuarios chilenos (país con nombre "Chile") con su alias, junto a los títulos de los juegos que ha adquirido cada uno y el año de adquisición. No considerar juegos gratuitos para generar la lista.',
                'columnas' => [
                    '`alias` del usuario',
                    '`titulo` del juego',
                    'Año de adquisición (`date_part(\'year\', fecha)` o equivalente)'
                ],
                'orden' => 'Alfabéticamente según el alias del usuario.',
                'restricciones' => 'No considerar juegos cuyo precio base sea 0.',
                'sql' => "
                    SELECT usuario.alias, titulo, date_part('year', fecha)
                    FROM juego, usuario, compra, pais
                    WHERE compra.usuario = usuario.correo
                    AND compra.juego = juego.codigo
                    AND pais.m49 = usuario.pais
                    AND juego.precio <> 0
                    AND pais.nombre = 'Chile'
                    ORDER BY usuario.alias;
                "
            ],
            [
                'grupo' => 1,
                'codigo' => 'test3-1b-resenas-sin-despectivos',
                'nombre' => 'Test 3 - 1B: Reseñas sin Términos Despectivos',
                'dificultad' => 'Fácil',
                'enunciado' => 'Seleccionar todas las reseñas a excepción de aquellas que contengan términos despectivos o peyorativos en el texto. Se consideran peyorativas las palabras "porqueria" y "aburrido".',
                'columnas' => [
                    'Título del juego (`titulo`)',
                    'Descripción del juego (`descripcion`)',
                    'Texto de la reseña (`texto`)',
                    'Valoración del juego (`valoracion`)'
                ],
                'orden' => 'Según el título del juego de manera ascendente, y luego desde la reseña más reciente a la más antigua.',
                'restricciones' => 'Excluir reseñas donde el texto contenga "aburrido" o "porqueria".',
                'sql' => "
                    SELECT juego.titulo,
                        juego.descripcion,
                        critica.texto,
                        critica.valoracion 
                    FROM critica, juego
                    WHERE critica.juego = juego.codigo
                    AND (texto NOT LIKE '%aburrido%' AND texto NOT LIKE '%porqueria%')
                    ORDER BY titulo ASC, fecha DESC;
                "
            ],
            [
                'grupo' => 1,
                'codigo' => 'test3-1c-juegos-rpg-criticas',
                'nombre' => 'Test 3 - 1C: Juegos RPG y Críticas',
                'dificultad' => 'Fácil',
                'enunciado' => 'La base de datos no cuenta con etiquetas fijas de clasificación, por lo que se requiere una consulta que busque todos los juegos del género RPG. Seleccionar los juegos que incluyan la sigla \'RPG\' o \'rpg\' en su descripción o título.',
                'columnas' => [
                    'Título (`titulo`)',
                    'Precio con descuento aplicado (`precio * descuento / 100`) nombrado como `precio`',
                    'Año de lanzamiento (`anio`)',
                    'Valoración (`valoracion`)',
                    'Texto de la crítica nombrado como `critica`'
                ],
                'orden' => 'Según el año de lanzamiento de forma ascendente.',
                'restricciones' => 'Considerar coincidencias de \'RPG\' en mayúsculas y minúsculas.',
                'sql' => "
                    SELECT titulo,
                        precio*descuento/100 AS precio,
                        anio,
                        valoracion, texto AS critica
                    FROM juego, critica 
                    WHERE critica.juego = juego.codigo
                    AND (descripcion LIKE '%RPG%' OR descripcion LIKE '%rpg%')
                    ORDER BY anio;
                "
            ],

            // =========================================================================
            // GRUPO 2: AGRUPACIONES Y AGREGACIÓN (2A, 2B, 2C, 2D)
            // =========================================================================
            [
                'grupo' => 2,
                'codigo' => 'test3-2a-resumen-compras-paises',
                'nombre' => 'Test 3 - 2A: Resumen de Compras por Países',
                'dificultad' => 'Medio',
                'enunciado' => 'Mostrar un resumen de compras agrupadas por países. No considerar juegos gratuitos para el cálculo.',
                'columnas' => [
                    'Nombre del país (`nombre`)',
                    'Número de copias compradas (`cantidad`)',
                    'Valor promedio de los juegos adquiridos (`promedio`)'
                ],
                'orden' => 'Por cantidad de copias vendidas de menor a mayor.',
                'restricciones' => 'Excluir compras de juegos con precio 0.',
                'sql' => "
                    SELECT pais.nombre, count(*) AS cantidad, avg(precio_final) AS promedio
                    FROM pais, usuario, compra, juego
                    WHERE pais.m49 = usuario.pais
                    AND usuario.correo = compra.usuario
                    AND compra.juego = juego.codigo
                    AND juego.precio <> 0
                    GROUP BY pais.m49
                    ORDER BY cantidad;
                "
            ],
            [
                'grupo' => 2,
                'codigo' => 'test3-2b-juegos-precio-sobre-promedio',
                'nombre' => 'Test 3 - 2B: Juegos con Precio Superior al Promedio',
                'dificultad' => 'Medio',
                'enunciado' => 'Seleccionar los juegos cuyo precio base (sin descuentos) es mayor o igual al precio promedio de todos los juegos de pago (no gratuitos) de la plataforma.',
                'columnas' => [
                    'Título del juego (`titulo`)',
                    'Código del juego (`codigo`)',
                    'Precio base (`precio`)',
                    'Cantidad de ventas registradas (`cantidad_ventas`)'
                ],
                'orden' => 'Agrupado por código de juego.',
                'restricciones' => 'El promedio general debe considerar solo juegos con precio distinto de cero.',
                'sql' => "
                    SELECT juego.titulo,
                        juego.codigo,
                        juego.precio,
                        count(*) AS cantidad_ventas 
                    FROM juego, compra
                    WHERE precio >= (SELECT avg(precio) FROM juego WHERE precio <> 0)
                    AND juego.codigo = compra.juego
                    GROUP BY juego.codigo;
                "
            ],
            [
                'grupo' => 2,
                'codigo' => 'test3-2c-gasto-total-usuario',
                'nombre' => 'Test 3 - 2C: Gasto Total por Usuario',
                'dificultad' => 'Medio',
                'enunciado' => 'Generar una lista de usuarios con su país de origen, la cantidad de juegos que han comprado y el monto total de dinero gastado en la tienda.',
                'columnas' => [
                    'Nombre de usuario (`nombre`)',
                    'Nombre del país (`nombre`)',
                    'Cantidad de juegos comprados (`cantidad`)',
                    'Dinero total gastado (`dinero_gastado`)'
                ],
                'orden' => 'Alfabéticamente según el nombre del usuario.',
                'restricciones' => 'No considerar juegos cuyo precio base sea 0.',
                'sql' => "
                    SELECT usuario.nombre,
                        pais.nombre,
                        count(*) AS cantidad,
                        sum(compra.precio_final) AS dinero_gastado
                    FROM usuario, compra, juego, pais
                    WHERE usuario.correo = compra.usuario 
                    AND pais.m49 = usuario.pais
                    AND juego.codigo = compra.juego
                    AND juego.precio <> 0
                    GROUP BY usuario.correo, pais.m49
                    ORDER BY usuario.nombre;
                "
            ],
            [
                'grupo' => 2,
                'codigo' => 'test3-2d-juegos-ventas-sobre-100',
                'nombre' => 'Test 3 - 2D: Juegos con Ventas Superiores a 100 USD',
                'dificultad' => 'Medio',
                'enunciado' => 'Seleccionar todos los juegos que han acumulado un monto total de ventas superior a 100 dólares.',
                'columnas' => [
                    'Código del juego (`codigo`)',
                    'Título del juego (`titulo`)',
                    'Cantidad de copias/usuarios adquiridos (`usuarios`)',
                    'Monto total recaudado (`dinero_acumulado`)'
                ],
                'orden' => 'Agrupado por código de juego, filtrando los totales mayores a 100 con HAVING.',
                'restricciones' => 'Monto acumulado superior estricto a 100.',
                'sql' => "
                    SELECT juego.codigo,
                        juego.titulo,
                        count(*) AS usuarios,
                        sum(precio_final) AS dinero_acumulado
                    FROM juego, compra
                    WHERE juego.codigo = compra.juego
                    GROUP BY juego.codigo
                    HAVING sum(precio_final) > 100;
                "
            ],

            // =========================================================================
            // GRUPO 3: SUBCONSULTAS Y CORRELACIONADAS (3A, 3B, 3C, 3D, 3E, 3F)
            // =========================================================================
            [
                'grupo' => 3,
                'codigo' => 'test3-3a-juegos-no-vendidos-recientes',
                'nombre' => 'Test 3 - 3A: Juegos No Vendidos en los Últimos Dos Años',
                'dificultad' => 'Difícil',
                'enunciado' => 'Seleccionar los títulos de juegos que no han registrado ventas en los últimos dos años (a partir del 01 de enero de 2023 en adelante), pero que sí se han vendido en algún periodo anterior. No considerar juegos gratuitos.',
                'columnas' => [
                    'Título del juego (`titulo`)'
                ],
                'orden' => 'Alfabéticamente según el título.',
                'restricciones' => 'Utilizar subconsulta con NOT IN sobre la fecha de compra.',
                'sql' => "
                    SELECT titulo
                    FROM compra, juego
                    WHERE juego.codigo = compra.juego
                    AND precio <> 0
                    AND juego.codigo NOT IN (SELECT juego FROM compra WHERE fecha >= '2023-01-01')
                    ORDER BY titulo;
                "
            ],
            [
                'grupo' => 3,
                'codigo' => 'test3-3b-ultimo-juego-adquirido',
                'nombre' => 'Test 3 - 3B: Último Juego Adquirido por Usuario',
                'dificultad' => 'Difícil',
                'enunciado' => 'Listar a cada usuario con su país de procedencia y el título del juego adquirido en su compra más reciente registrada en la plataforma.',
                'columnas' => [
                    'Nombre del usuario (`nombre`)',
                    'Nombre del país (`nombre`)',
                    'Título del último juego comprado (`ultimo_juego`)'
                ],
                'orden' => 'Listado de usuarios de la plataforma.',
                'restricciones' => 'Utilizar subconsulta correlacionada para obtener el juego de la fecha máxima de compra.',
                'sql' => "
                    SELECT usuario.nombre,
                        pais.nombre,
                        (SELECT titulo
                         FROM juego, compra
                         WHERE juego.codigo = compra.juego
                         AND compra.usuario = usuario.correo
                         ORDER BY compra.fecha DESC LIMIT 1) AS ultimo_juego
                    FROM pais, usuario 
                    WHERE pais.m49 = usuario.pais;
                "
            ],
            [
                'grupo' => 3,
                'codigo' => 'test3-3c-resumen-global-usuarios',
                'nombre' => 'Test 3 - 3C: Resumen Global de Usuarios y Actividad',
                'dificultad' => 'Difícil',
                'enunciado' => 'Crear una lista resumen con todos los usuarios registrados en la plataforma. Deben aparecer todos los usuarios, incluso si no han realizado compras ni escrito críticas.',
                'columnas' => [
                    'Nombre de usuario (`nombre`)',
                    'País de origen (`nombre`)',
                    'Cantidad total de juegos adquiridos (`cantidad_juegos`)',
                    'Cantidad de reseñas escritas (`cantidad_reseñas`)'
                ],
                'orden' => 'Alfabéticamente según el nombre del usuario.',
                'restricciones' => 'Garantizar que se listen todos los usuarios usando subconsultas correlacionadas escalares.',
                'sql' => "
                    SELECT usuario.nombre,
                        pais.nombre,
                        (SELECT count(*) FROM compra WHERE compra.usuario = usuario.correo) AS cantidad_juegos,
                        (SELECT count(*) FROM critica WHERE critica.usuario = usuario.correo) AS cantidad_reseñas
                    FROM pais, usuario 
                    WHERE pais.m49 = usuario.pais
                    ORDER BY usuario.nombre;
                "
            ],
            [
                'grupo' => 3,
                'codigo' => 'test3-3d-usuario-mayor-cantidad-juegos',
                'nombre' => 'Test 3 - 3D: Usuario con Mayor Cantidad de Juegos',
                'dificultad' => 'Difícil',
                'enunciado' => 'Seleccionar el nombre y correo del usuario (o usuarios, si hubiese empate) que posee la mayor cantidad de juegos comprados en su biblioteca.',
                'columnas' => [
                    'Nombre del usuario (`nombre`)',
                    'Correo electrónico (`correo`)'
                ],
                'orden' => 'Resultado de la coincidencia con el máximo general.',
                'restricciones' => 'Usar agregación con HAVING comparado contra subconsulta del conteo máximo.',
                'sql' => "
                    SELECT nombre, correo
                    FROM usuario, compra
                    WHERE correo = usuario
                    GROUP BY usuario.correo
                    HAVING count(*) = (
                        SELECT max(cnt) FROM (
                            SELECT count(*) AS cnt
                            FROM compra
                            GROUP BY usuario
                        )
                    );
                "
            ],
            [
                'grupo' => 3,
                'codigo' => 'test3-3e-total-amistades-aceptadas',
                'nombre' => 'Test 3 - 3E: Total de Amistades Aceptadas',
                'dificultad' => 'Difícil',
                'enunciado' => 'Generar una lista de todos los usuarios mostrando su nombre, alias y la cantidad total de amistades confirmadas. Considerar que un usuario puede ser solicitante o receptor de la solicitud, y únicamente se deben contabilizar aquellas solicitudes aceptadas (aceptado = true / 1).',
                'columnas' => [
                    'Nombre (`nombre`)',
                    'Alias (`alias`)',
                    'Cantidad total de amistades (`cantidad_amistades`)'
                ],
                'orden' => 'Por orden natural de la tabla usuario.',
                'restricciones' => 'Sumar tanto solicitudes enviadas como recibidas que tengan aceptado = 1 (o true).',
                'sql' => "
                    SELECT nombre,
                        alias,
                        (SELECT count(*) FROM amistad 
                        WHERE usuario_solicitante = usuario.correo
                        AND aceptado = 1) +
                        (SELECT count(*) FROM amistad 
                        WHERE usuario_solicitado = usuario.correo
                        AND aceptado = 1) AS cantidad_amistades
                    FROM usuario;
                "
            ],
            [
                'grupo' => 3,
                'codigo' => 'test3-3f-precios-moneda-polonia',
                'nombre' => 'Test 3 - 3F: Precios y Conversión en Moneda Polaca',
                'dificultad' => 'Difícil',
                'enunciado' => 'Listar los juegos junto con sus precios convertidos a la moneda nacional de Polonia (usando el factor de conversión registrado en la tabla país para \'Polonia\').',
                'columnas' => [
                    'Título del juego (`titulo`)',
                    'Descripción (`descripcion`)',
                    'Cantidad de críticas registradas (`cantidad_criticas`)',
                    'Precio normal convertido a moneda local (`precio_normal`)',
                    'Precio con descuento convertido a moneda local (`precio_descuento`)'
                ],
                'orden' => 'Según el precio con descuento de menor a mayor (`precio_descuento`).',
                'restricciones' => 'Obtener la tasa de conversión mediante subconsulta `(SELECT conversion FROM pais WHERE nombre = \'Polonia\')`.',
                'sql' => "
                    SELECT titulo,
                        descripcion,
                        (SELECT count(*) FROM critica WHERE juego=juego.codigo) AS cantidad_criticas,
                        precio * (SELECT conversion FROM pais WHERE nombre = 'Polonia') AS precio_normal,
                        (precio - precio*descuento/100.0) * (SELECT conversion FROM pais WHERE nombre = 'Polonia') AS precio_descuento
                    FROM juego
                    GROUP BY juego.codigo
                    ORDER BY precio_descuento;
                "
            ],
        ];
    }
}
