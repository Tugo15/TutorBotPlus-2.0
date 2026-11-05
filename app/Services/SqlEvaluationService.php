<?php

namespace App\Services;

use App\Models\EnvioSolucionProblema;
use App\Models\EvaluacionSolucion;
use App\Models\Problemas;
use App\Models\Casos_Pruebas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use Exception;

class SqlEvaluationService
{
    protected OutputComparatorService $comparator;

    public function __construct(OutputComparatorService $comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * Evalúa de forma completa y aislada un envío de SQL.
     * Soporta: Consultas (SELECT), Vistas (CREATE VIEW), Triggers (CREATE TRIGGER),
     * Tablas (DDL/DML) y scripts de comprobación en los casos de prueba.
     *
     * @param EnvioSolucionProblema $envio
     * @return array ['estado' => bool, 'mensaje' => string|null]
     */
    public function evaluar(EnvioSolucionProblema $envio): array
    {
        $problema = $envio->problema;
        $casos = $problema->casos_de_prueba()->orderBy('id', 'asc')->get();

        if ($casos->isEmpty()) {
            return ['estado' => false, 'mensaje' => 'El problema no tiene casos de prueba asignados.'];
        }

        $baseDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sql_eval_' . $envio->id . '_' . time();
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        try {
            // 1. Preparar la plantilla base de la base de datos
            $templateDbPath = $this->prepararBaseDatosPlantilla($problema, $baseDir);

            $cantResueltos = 0;
            $puntajeTotal = 0;

            DB::beginTransaction();

            // Limpiar evaluaciones previas en proceso si las hubiera
            $envio->evaluaciones()->delete();

            foreach ($casos as $index => $caso) {
                // Cada caso de prueba corre en su propia copia aislada e independiente
                $caseDbPath = $baseDir . DIRECTORY_SEPARATOR . 'case_' . $caso->id . '.sqlite';
                if ($templateDbPath && file_exists($templateDbPath)) {
                    copy($templateDbPath, $caseDbPath);
                } else {
                    touch($caseDbPath);
                }

                $evaluacion = new EvaluacionSolucion;
                $evaluacion->token = \Illuminate\Support\Str::random(40);
                $evaluacion->envio()->associate($envio);
                $evaluacion->casos_pruebas()->associate($caso);

                $resultadoCaso = $this->ejecutarCaso($caseDbPath, $envio->codigo, $caso);

                $salidaEsperada = trim($caso->salidas ?? '');
                $salidaObtenida = trim($resultadoCaso['salida'] ?? '');
                $errorMensaje = $resultadoCaso['error'];

                if ($errorMensaje) {
                    $evaluacion->estado = 'Error';
                    $evaluacion->resultado = 'Error de Compilación';
                    $evaluacion->error_compilacion = base64_encode($errorMensaje);
                    $evaluacion->stout = base64_encode('');
                } else {
                    $exactMatch = ($salidaObtenida === $salidaEsperada);
                    $flexMatch = !$exactMatch && $this->comparator->isMatch($salidaObtenida, $salidaEsperada);
                    $isAccepted = $exactMatch || $flexMatch;

                    $evaluacion->estado = $isAccepted ? 'Aceptado' : 'Rechazado';
                    $evaluacion->resultado = $exactMatch ? 'Accepted' : ($flexMatch ? 'Aceptado (Variación de Formato)' : 'Wrong Answer');
                    $evaluacion->stout = base64_encode($salidaObtenida);

                    if ($isAccepted) {
                        $cantResueltos++;
                        $puntajeTotal += ($caso->puntos ?? 0);
                    }
                }

                $evaluacion->tiempo = $resultadoCaso['tiempo'] ?? '0.005';
                $evaluacion->memoria = '1500';
                $evaluacion->save();

                // Eliminar base de datos del caso
                if (file_exists($caseDbPath)) {
                    @unlink($caseDbPath);
                }
            }

            $envio->cant_casos_resuelto = $cantResueltos;
            $envio->puntaje = $puntajeTotal;
            $envio->solucionado = ($cantResueltos === count($casos) && count($casos) > 0);
            $envio->save();

            DB::commit();

            // Si hay restricciones de IA configuradas
            if ($problema && !empty(trim($problema->restricciones ?? ''))) {
                try {
                    \App\Http\Controllers\LlmController::ejecutar_verificacion_restricciones($envio);
                } catch (\Exception $e) {
                    // Si falla el LLM, no interrumpe el flujo
                }
            }

            return ['estado' => true, 'mensaje' => 'Evaluación completada exitosamente.'];

        } catch (Exception $e) {
            DB::rollBack();
            return ['estado' => false, 'mensaje' => 'Error durante la evaluación SQL: ' . $e->getMessage()];
        } finally {
            // Limpieza del directorio temporal
            $this->eliminarDirectorioRecursivo($baseDir);
        }
    }

    /**
     * Ejecuta el código del estudiante y las consultas de verificación contra la BD del caso.
     */
    protected function ejecutarCaso(string $dbPath, string $codigoEstudiante, Casos_Pruebas $caso): array
    {
        $startTime = microtime(true);
        $salida = '';
        $error = null;

        try {
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);

            $this->registrarFuncionesCompatibilidad($pdo);

            // 1. Limpiar código del alumno (remover comandos .open de sqlite si los pegó)
            $cleanCode = preg_replace('/^\s*\.open\s+\S+/mi', '', $codigoEstudiante);
            $cleanCode = trim($cleanCode);

            // 2. Consulta o Script de Comprobación del Caso de Prueba
            $scriptVerificacion = trim($caso->entradas ?? '');

            if (!empty($scriptVerificacion)) {
                // CASO CON VERIFICACIÓN (Vistas, Triggers, Funciones, Modificaciones DDL/DML):
                // A) Ejecutar primero el código del estudiante (crea la vista, el trigger, etc.)
                if (!empty($cleanCode)) {
                    $pdo->exec($cleanCode);
                }

                // B) Ejecutar el script de comprobación y capturar la salida de la última consulta
                $salida = $this->ejecutarScriptYCapturarSalida($pdo, $scriptVerificacion);

            } else {
                // CASO SIN VERIFICACIÓN (Consulta normal SELECT):
                // Ejecutar directamente el código del estudiante y capturar su salida
                $salida = $this->ejecutarScriptYCapturarSalida($pdo, $cleanCode);
            }

        } catch (\PDOException $ex) {
            $error = 'Error SQL: ' . $ex->getMessage();
        } catch (Exception $ex) {
            $error = 'Error de Ejecución: ' . $ex->getMessage();
        }

        $tiempo = number_format(microtime(true) - $startTime, 4, '.', '');

        return [
            'salida' => $salida,
            'error' => $error,
            'tiempo' => $tiempo
        ];
    }

    /**
     * Ejecuta una o múltiples sentencias SQL y captura el resultado de la(s) consulta(s) SELECT.
     */
    protected function ejecutarScriptYCapturarSalida(PDO $pdo, string $sqlScript): string
    {
        $script = trim($sqlScript);
        if (empty($script)) {
            return '';
        }

        // Dividir sentencias por punto y coma (respetando cadenas de texto)
        $statements = $this->dividirSentenciasSql($script);
        $outputRows = [];

        foreach ($statements as $stmtSql) {
            $stmtSql = trim($stmtSql);
            if (empty($stmtSql)) continue;

            // Remover comentarios SQL previos para determinar correctamente el tipo de sentencia
            $stmtWithoutComments = preg_replace('/--[^\r\n]*|\/\*[\s\S]*?\*\//', '', $stmtSql);
            $stmtWithoutComments = trim($stmtWithoutComments);

            $isSelectOrPragma = preg_match('/^\s*(SELECT|PRAGMA|WITH|VALUES|EXPLAIN)\b/i', $stmtWithoutComments);

            if ($isSelectOrPragma) {
                $stmt = $pdo->query($stmtSql);
                $rows = $stmt->fetchAll(PDO::FETCH_NUM);
                foreach ($rows as $row) {
                    $formattedRow = array_map(function($val) {
                        if ($val === null) return '';
                        // Formato booleano de texto si aplica
                        if ($val === true || $val === '1' && strlen($val) === 1) return $val;
                        return $val;
                    }, $row);
                    $outputRows[] = implode('|', $formattedRow);
                }
            } else {
                $pdo->exec($stmtSql);
            }
        }

        return implode("\n", $outputRows);
    }

    /**
     * Registra funciones de compatibilidad para soportar sintaxis habitual de PostgreSQL/MySQL en SQLite.
     */
    protected function registrarFuncionesCompatibilidad(PDO $pdo): void
    {
        // 1. date_part('year', fecha) / date_part('month', fecha) / etc.
        $pdo->sqliteCreateFunction('date_part', function($part, $date) {
            if (!$date) return null;
            $time = strtotime($date);
            if ($time === false) return null;
            switch (strtolower((string)$part)) {
                case 'year': return (int)date('Y', $time);
                case 'month': return (int)date('m', $time);
                case 'day': return (int)date('d', $time);
                case 'hour': return (int)date('H', $time);
                case 'minute': return (int)date('i', $time);
                case 'second': return (int)date('s', $time);
                default: return null;
            }
        }, 2);

        // 2. now()
        $pdo->sqliteCreateFunction('now', function() {
            return date('Y-m-d H:i:s');
        }, 0);

        // 3. current_date()
        $pdo->sqliteCreateFunction('current_date', function() {
            return date('Y-m-d');
        }, 0);

        // 4. concat(...)
        $pdo->sqliteCreateFunction('concat', function(...$args) {
            return implode('', $args);
        });

        // 5. greatest(...)
        $pdo->sqliteCreateFunction('greatest', function(...$args) {
            $filtered = array_filter($args, fn($a) => $a !== null);
            return empty($filtered) ? null : max($filtered);
        });

        // 6. least(...)
        $pdo->sqliteCreateFunction('least', function(...$args) {
            $filtered = array_filter($args, fn($a) => $a !== null);
            return empty($filtered) ? null : min($filtered);
        });

        // 7. to_char(...)
        $pdo->sqliteCreateFunction('to_char', function($val, $format = null) {
            if ($val === null) return null;
            if ($format === null) return (string)$val;
            $time = strtotime($val);
            if ($time !== false) {
                // Conversión básica de formato fecha tipo 'YYYY-MM-DD'
                $f = str_ireplace(['yyyy', 'mm', 'dd', 'hh24', 'mi', 'ss'], ['Y', 'm', 'd', 'H', 'i', 's'], $format);
                return date($f, $time);
            }
            return (string)$val;
        });
    }

    /**
     * Prepara el archivo SQLite base para el problema a partir de su archivo_adicional.
     */
    protected function prepararBaseDatosPlantilla(Problemas $problema, string $baseDir): ?string
    {
        if (empty($problema->archivo_adicional)) {
            return null;
        }

        $filePath = storage_path('app/public/archivos_adicionales/' . $problema->archivo_adicional);
        if (!file_exists($filePath)) {
            return null;
        }

        $templateDb = $baseDir . DIRECTORY_SEPARATOR . 'template.sqlite';

        // 1. Archivo ZIP
        if (str_ends_with(strtolower($filePath), '.zip')) {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $extractDir = $baseDir . DIRECTORY_SEPARATOR . 'extracted_zip';
                mkdir($extractDir, 0777, true);
                $zip->extractTo($extractDir);
                $zip->close();

                // Buscar archivo .sqlite, .db o .sql recursivamente en el ZIP
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractDir, \FilesystemIterator::SKIP_DOTS));
                $foundDb = null;
                $foundSql = null;
                foreach ($iterator as $item) {
                    if ($item->isFile()) {
                        $ext = strtolower($item->getExtension());
                        if (in_array($ext, ['sqlite', 'db', 'sqlite3']) && !$foundDb) {
                            $foundDb = $item->getPathname();
                        } else if ($ext === 'sql' && !$foundSql) {
                            $foundSql = $item->getPathname();
                        }
                    }
                }

                if ($foundDb) {
                    copy($foundDb, $templateDb);
                    return $templateDb;
                }

                if ($foundSql) {
                    $pdo = new PDO('sqlite:' . $templateDb);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $pdo->exec(file_get_contents($foundSql));
                    return $templateDb;
                }
            }
        }

        // 2. Archivo .sqlite / .db directo
        if (preg_match('/\.(sqlite|db|sqlite3)$/i', $filePath)) {
            copy($filePath, $templateDb);
            return $templateDb;
        }

        // 3. Archivo .sql directo
        if (str_ends_with(strtolower($filePath), '.sql')) {
            $pdo = new PDO('sqlite:' . $templateDb);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec(file_get_contents($filePath));
            return $templateDb;
        }

        return null;
    }

    /**
     * Divide un script SQL en sentencias individuales respetando cadenas de texto y bloques BEGIN ... END de triggers.
     */
    protected function dividirSentenciasSql(string $sql): array
    {
        $tokens = [];
        $length = strlen($sql);
        $current = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inBlock = 0;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($char === "'" && !$inDoubleQuote) {
                if ($i > 0 && $sql[$i - 1] === '\\') {
                    // escape
                } else {
                    $inSingleQuote = !$inSingleQuote;
                }
            } elseif ($char === '"' && !$inSingleQuote) {
                if ($i > 0 && $sql[$i - 1] === '\\') {
                    // escape
                } else {
                    $inDoubleQuote = !$inDoubleQuote;
                }
            }

            if (!$inSingleQuote && !$inDoubleQuote) {
                if (preg_match('/^\bBEGIN\b/i', substr($sql, $i, 6))) {
                    $inBlock++;
                } elseif (preg_match('/^\bEND\b/i', substr($sql, $i, 4))) {
                    if ($inBlock > 0) {
                        $inBlock--;
                    }
                }

                if ($char === ';' && $inBlock === 0) {
                    $trimmed = trim($current);
                    if ($trimmed !== '') {
                        $tokens[] = $trimmed;
                    }
                    $current = '';
                    continue;
                }
            }

            $current .= $char;
        }

        $trimmed = trim($current);
        if ($trimmed !== '') {
            $tokens[] = $trimmed;
        }

        return $tokens;
    }

    /**
     * Elimina recursivamente un directorio y todos sus archivos temporales.
     */
    protected function eliminarDirectorioRecursivo(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->eliminarDirectorioRecursivo($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
