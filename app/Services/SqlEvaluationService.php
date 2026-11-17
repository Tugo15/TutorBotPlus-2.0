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
                    $evaluacion->resultado = $exactMatch ? 'Aceptado' : ($flexMatch ? 'Aceptado (Variación de Formato)' : 'Respuesta Incorrecta');
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

            // Normalización de compatibilidad PostgreSQL (ALL, ANY)
            $cleanCode = $this->normalizarSintaxisPostgres($cleanCode);

            // 2. Consulta o Script de Comprobación del Caso de Prueba
            $scriptVerificacion = $this->normalizarSintaxisPostgres(trim($caso->entradas ?? ''));

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
        // 1. date_part('year', fecha) / EXTRACT(... FROM ...)
        $pdo->sqliteCreateFunction('date_part', function($part, $date) {
            if ($date === null || $date === '') return null;
            $time = strtotime((string)$date);
            if ($time === false) return null;
            switch (strtolower(trim((string)$part))) {
                case 'year': return (int)date('Y', $time);
                case 'quarter': return (int)ceil((int)date('n', $time) / 3);
                case 'month': return (int)date('n', $time);
                case 'week': return (int)date('W', $time);
                case 'day': return (int)date('j', $time);
                case 'doy': return (int)date('z', $time) + 1;
                case 'dow': return (int)date('w', $time); // 0=domingo..6=sábado como PostgreSQL
                case 'isodow': return (int)date('N', $time); // 1=lunes..7=domingo
                case 'hour': return (int)date('G', $time);
                case 'minute': return (int)date('i', $time);
                case 'second': return (int)date('s', $time);
                case 'epoch': return $time;
                default: return null;
            }
        }, 2);

        // 2. date_trunc('month', fecha)
        $pdo->sqliteCreateFunction('date_trunc', function($part, $date) {
            if ($date === null || $date === '') return null;
            $time = strtotime((string)$date);
            if ($time === false) return null;
            switch (strtolower(trim((string)$part))) {
                case 'year': return date('Y-01-01 00:00:00', $time);
                case 'quarter':
                    $qMonth = ((int)ceil((int)date('n', $time) / 3) - 1) * 3 + 1;
                    return sprintf('%s-%02d-01 00:00:00', date('Y', $time), $qMonth);
                case 'month': return date('Y-m-01 00:00:00', $time);
                case 'week':
                    $dowIso = (int)date('N', $time);
                    $monday = strtotime('-' . ($dowIso - 1) . ' days', $time);
                    return date('Y-m-d 00:00:00', $monday);
                case 'day': return date('Y-m-d 00:00:00', $time);
                case 'hour': return date('Y-m-d H:00:00', $time);
                case 'minute': return date('Y-m-d H:i:00', $time);
                case 'second': return date('Y-m-d H:i:s', $time);
                default: return null;
            }
        }, 2);

        // 3. age(fecha1[, fecha2])
        $pdo->sqliteCreateFunction('age', function(...$args) {
            if (count($args) === 0 || $args[0] === null) return null;
            try {
                if (count($args) === 1) {
                    $d1 = new \DateTimeImmutable('today');
                    $d2 = new \DateTimeImmutable((string)$args[0]);
                } else {
                    if ($args[1] === null) return null;
                    $d1 = new \DateTimeImmutable((string)$args[0]);
                    $d2 = new \DateTimeImmutable((string)$args[1]);
                }
                $diff = $d2->diff($d1);
                $sign = $diff->invert ? '-' : '';
                $parts = [];
                if ($diff->y > 0) $parts[] = $sign . $diff->y . ($diff->y === 1 ? ' year' : ' years');
                if ($diff->m > 0) $parts[] = $sign . $diff->m . ($diff->m === 1 ? ' mon' : ' mons');
                if ($diff->d > 0 || empty($parts)) $parts[] = $sign . $diff->d . ($diff->d === 1 ? ' day' : ' days');
                return implode(' ', $parts);
            } catch (\Throwable $e) {
                return null;
            }
        });

        // 4. now() y current_date()
        $pdo->sqliteCreateFunction('now', fn() => date('Y-m-d H:i:s'), 0);
        $pdo->sqliteCreateFunction('current_date', fn() => date('Y-m-d'), 0);

        // 5. Funciones de texto estilo PostgreSQL
        $pdo->sqliteCreateFunction('concat', fn(...$args) => implode('', array_map(fn($a) => $a === null ? '' : (string)$a, $args)));
        $pdo->sqliteCreateFunction('concat_ws', function($sep, ...$args) {
            if ($sep === null) return null;
            $filtered = array_filter($args, fn($a) => $a !== null);
            return implode((string)$sep, $filtered);
        });
        $pdo->sqliteCreateFunction('split_part', function($str, $delim, $field) {
            if ($str === null || $delim === null || $field === null) return null;
            $idx = (int)$field;
            if ($idx <= 0) return null;
            $parts = $delim === '' ? [(string)$str] : explode((string)$delim, (string)$str);
            return $parts[$idx - 1] ?? '';
        }, 3);
        $pdo->sqliteCreateFunction('left', fn($str, $n) => $str === null ? null : ((int)$n < 0 ? mb_substr((string)$str, 0, max(0, mb_strlen((string)$str) + (int)$n)) : mb_substr((string)$str, 0, (int)$n)), 2);
        $pdo->sqliteCreateFunction('right', fn($str, $n) => $str === null ? null : ((int)$n <= 0 ? '' : mb_substr((string)$str, -((int)$n))), 2);
        $pdo->sqliteCreateFunction('char_length', fn($str) => $str === null ? null : mb_strlen((string)$str), 1);
        $pdo->sqliteCreateFunction('character_length', fn($str) => $str === null ? null : mb_strlen((string)$str), 1);
        $pdo->sqliteCreateFunction('strpos', function($haystack, $needle) {
            if ($haystack === null || $needle === null) return null;
            if ((string)$needle === '') return 1;
            $pos = mb_strpos((string)$haystack, (string)$needle);
            return $pos === false ? 0 : $pos + 1;
        }, 2);
        $pdo->sqliteCreateFunction('initcap', fn($str) => $str === null ? null : mb_convert_case(mb_strtolower((string)$str), MB_CASE_TITLE, 'UTF-8'), 1);
        $pdo->sqliteCreateFunction('btrim', fn($str, $chars = " \t\n\r\0\x0B") => $str === null ? null : trim((string)$str, (string)$chars));
        $pdo->sqliteCreateFunction('lpad', function($str, $len, $pad = ' ') {
            if ($str === null) return null;
            $target = max(0, (int)$len);
            $s = (string)$str;
            if (mb_strlen($s) >= $target) return mb_substr($s, 0, $target);
            $p = (string)$pad === '' ? ' ' : (string)$pad;
            $needed = $target - mb_strlen($s);
            return mb_substr(str_repeat($p, (int)ceil($needed / mb_strlen($p))), 0, $needed) . $s;
        });
        $pdo->sqliteCreateFunction('rpad', function($str, $len, $pad = ' ') {
            if ($str === null) return null;
            $target = max(0, (int)$len);
            $s = (string)$str;
            if (mb_strlen($s) >= $target) return mb_substr($s, 0, $target);
            $p = (string)$pad === '' ? ' ' : (string)$pad;
            $needed = $target - mb_strlen($s);
            return $s . mb_substr(str_repeat($p, (int)ceil($needed / mb_strlen($p))), 0, $needed);
        });
        $pdo->sqliteCreateFunction('reverse', fn($str) => $str === null ? null : implode('', array_reverse(mb_str_split((string)$str))), 1);
        $pdo->sqliteCreateFunction('repeat', fn($str, $n) => $str === null ? null : str_repeat((string)$str, max(0, (int)$n)), 2);

        // 6. Funciones numéricas y de comparación
        $pdo->sqliteCreateFunction('greatest', function(...$args) {
            $filtered = array_filter($args, fn($a) => $a !== null);
            return empty($filtered) ? null : max($filtered);
        });
        $pdo->sqliteCreateFunction('least', function(...$args) {
            $filtered = array_filter($args, fn($a) => $a !== null);
            return empty($filtered) ? null : min($filtered);
        });
        $pdo->sqliteCreateFunction('trunc', function($val, $decimals = 0) {
            if ($val === null || !is_numeric($val)) return null;
            $factor = pow(10, (int)$decimals);
            $num = (float)$val * $factor;
            $truncated = $num >= 0 ? floor($num) : ceil($num);
            return $factor == 1 ? (int)$truncated : $truncated / $factor;
        });

        // 7. to_char / to_date / to_timestamp
        $pdo->sqliteCreateFunction('to_char', function($val, $format = null) {
            if ($val === null) return null;
            if ($format === null) return (string)$val;
            $time = strtotime((string)$val);
            if ($time !== false) {
                $f = str_ireplace(['yyyy', 'yy', 'mm', 'dd', 'hh24', 'mi', 'ss'], ['Y', 'y', 'm', 'd', 'H', 'i', 's'], (string)$format);
                return date($f, $time);
            }
            return (string)$val;
        });
        $pdo->sqliteCreateFunction('to_date', function($val, $format = null) {
            if ($val === null) return null;
            $time = strtotime(str_replace('/', '-', (string)$val));
            return $time === false ? null : date('Y-m-d', $time);
        });
        $pdo->sqliteCreateFunction('to_timestamp', function($val, $format = null) {
            if ($val === null) return null;
            $time = strtotime(str_replace('/', '-', (string)$val));
            return $time === false ? null : date('Y-m-d H:i:s', $time);
        });

        // 8. Expresiones regulares (~, ~*, REGEXP)
        $pdo->sqliteCreateFunction('regexp', function($pattern, $subject) {
            if ($pattern === null || $subject === null) return 0;
            return @preg_match('/' . str_replace('/', '\/', (string)$pattern) . '/u', (string)$subject) === 1 ? 1 : 0;
        }, 2);
        $pdo->sqliteCreateFunction('iregexp', function($pattern, $subject) {
            if ($pattern === null || $subject === null) return 0;
            return @preg_match('/' . str_replace('/', '\/', (string)$pattern) . '/iu', (string)$subject) === 1 ? 1 : 0;
        }, 2);

        // 9. Fallback string_agg para versiones de SQLite anteriores a 3.44.0
        $sqliteVer = (string)$pdo->query('SELECT sqlite_version()')->fetchColumn();
        if (version_compare($sqliteVer, '3.44.0', '<')) {
            $pdo->sqliteCreateAggregate(
                'string_agg',
                function (&$context, $rownum, $val, $sep = ',') {
                    if ($val !== null) {
                        $context['items'][] = (string)$val;
                        $context['sep'] = (string)$sep;
                    }
                },
                function (&$context) {
                    return empty($context['items']) ? null : implode($context['sep'] ?? ',', $context['items']);
                },
                2
            );
        }
    }

    /**
     * Normaliza construcciones sintácticas propias de PostgreSQL a equivalentes compatibles con SQLite,
     * protegiendo primero literales de texto y comentarios para evitar falsos positivos.
     */
    protected function normalizarSintaxisPostgres(string $sql): string
    {
        if (empty(trim($sql))) {
            return $sql;
        }

        $placeholders = [];
        $protectedSql = $this->protegerLiteralesYComentarios($sql, $placeholders);

        $protectedSql = $this->normalizarDdlYLimitesPostgres($protectedSql);
        $protectedSql = $this->normalizarCastPostgres($protectedSql);
        $protectedSql = $this->normalizarIntervalosPostgres($protectedSql, $placeholders);
        $protectedSql = $this->normalizarExtractPostgres($protectedSql);
        $protectedSql = $this->normalizarIlikePostgres($protectedSql);
        $protectedSql = $this->normalizarOperadoresRegexPostgres($protectedSql);
        $protectedSql = $this->normalizarCuantificadoresPostgres($protectedSql);

        return strtr($protectedSql, $placeholders);
    }

    /**
     * Reemplaza comentarios (--, /* *\/) y literales de cadena ('...') por marcadores únicos.
     */
    protected function protegerLiteralesYComentarios(string $sql, array &$placeholders): string
    {
        $pattern = "/'(?:''|[^'])*'|--[^\r\n]*|\/\*[\s\S]*?\*\//";
        return preg_replace_callback($pattern, function ($m) use (&$placeholders) {
            $key = '__TB_SQL_TOK_' . count($placeholders) . '__';
            $placeholders[$key] = $m[0];
            return $key;
        }, $sql);
    }

    /**
     * Normaliza DDL y cláusulas de paginación estilo PostgreSQL.
     */
    protected function normalizarDdlYLimitesPostgres(string $sql): string
    {
        // CREATE OR REPLACE VIEW nombre AS -> DROP VIEW IF EXISTS nombre; CREATE VIEW nombre AS
        $sql = preg_replace(
            '/\bCREATE\s+OR\s+REPLACE\s+VIEW\s+((?:`[^`]+`|"[^"]+"|[\w]+)(?:\.(?:`[^`]+`|"[^"]+"|[\w]+))?)\s+AS\b/i',
            'DROP VIEW IF EXISTS $1; CREATE VIEW $1 AS',
            $sql
        );

        // SERIAL / BIGSERIAL / SMALLSERIAL PRIMARY KEY -> INTEGER PRIMARY KEY AUTOINCREMENT
        $sql = preg_replace('/\b(?:BIGSERIAL|SMALLSERIAL|SERIAL)\s+PRIMARY\s+KEY\b/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
        $sql = preg_replace('/\b(?:BIGSERIAL|SMALLSERIAL|SERIAL)\b/i', 'INTEGER', $sql);

        // DEFAULT NOW() -> DEFAULT (datetime('now'))
        $sql = preg_replace('/\bDEFAULT\s+NOW\s*\(\s*\)/i', "DEFAULT (datetime('now'))", $sql);

        // FETCH FIRST N ROW(S) ONLY / FETCH NEXT N ROW(S) ONLY -> LIMIT N
        $sql = preg_replace('/\bFETCH\s+(?:FIRST|NEXT)\s+(\d+)\s+ROWS?\s+ONLY\b/i', 'LIMIT $1', $sql);

        return $sql;
    }

    /**
     * Normaliza aritmética de fechas con INTERVAL 'X unit' de PostgreSQL a funciones date/datetime de SQLite.
     */
    protected function normalizarIntervalosPostgres(string $sql, array $placeholders): string
    {
        $unitMap = [
            'año' => 'years', 'años' => 'years', 'year' => 'years', 'years' => 'years',
            'mes' => 'months', 'meses' => 'months', 'month' => 'months', 'months' => 'months',
            'semana' => 'weeks', 'semanas' => 'weeks', 'week' => 'weeks', 'weeks' => 'weeks',
            'día' => 'days', 'días' => 'days', 'dia' => 'days', 'dias' => 'days', 'day' => 'days', 'days' => 'days',
            'hora' => 'hours', 'horas' => 'hours', 'hour' => 'hours', 'hours' => 'hours',
            'minuto' => 'minutes', 'minutos' => 'minutes', 'minute' => 'minutes', 'minutes' => 'minutes',
            'segundo' => 'seconds', 'segundos' => 'seconds', 'second' => 'seconds', 'seconds' => 'seconds',
        ];

        $exprPat = '([a-zA-Z_][\w.]*\s*\(\s*[^()]*\s*\)|CURRENT_DATE|CURRENT_TIMESTAMP|__TB_SQL_TOK_\d+__|[a-zA-Z_][\w.]*)';

        return preg_replace_callback(
            '/' . $exprPat . '\s*([+\-])\s*INTERVAL\s+(__TB_SQL_TOK_\d+__)/iu',
            function ($m) use ($placeholders, $unitMap) {
                $baseExpr = trim($m[1]);
                $sign = $m[2];
                $litRaw = $placeholders[$m[3]] ?? '';
                $litInner = trim(str_replace("''", "'", substr($litRaw, 1, -1)));

                if (!preg_match('/^([+\-]?\d+)\s+([\p{L}]+)$/u', $litInner, $im)) {
                    return $m[0];
                }
                $qty = (int)$im[1];
                if ($sign === '-') {
                    $qty = -$qty;
                }
                $unit = $unitMap[mb_strtolower($im[2])] ?? null;
                if ($unit === null) {
                    return $m[0];
                }
                if ($unit === 'weeks') {
                    $qty *= 7;
                    $unit = 'days';
                }

                $mod = ($qty >= 0 ? '+' : '') . $qty . ' ' . $unit;
                $isDateOnly = preg_match('/^(?:CURRENT_DATE|DATE\s*\()/i', $baseExpr)
                    && in_array($unit, ['years', 'months', 'days'], true);
                $fn = $isDateOnly ? 'date' : 'datetime';
                return "{$fn}({$baseExpr}, '{$mod}')";
            },
            $sql
        );
    }

    /**
     * Normaliza EXTRACT(part FROM expr) a date_part('part', expr).
     */
    protected function normalizarExtractPostgres(string $sql): string
    {
        $offset = 0;
        while (preg_match('/\bEXTRACT\s*\(\s*([a-zA-Z_]+)\s+FROM\s+/i', $sql, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $matchStart = $m[0][1];
            $exprStart = $matchStart + strlen($m[0][0]);
            $len = strlen($sql);
            $depth = 1;
            $pos = $exprStart;

            while ($pos < $len && $depth > 0) {
                if ($sql[$pos] === '(') {
                    $depth++;
                } elseif ($sql[$pos] === ')') {
                    $depth--;
                }
                $pos++;
            }

            if ($depth !== 0) {
                break;
            }

            $expr = trim(substr($sql, $exprStart, $pos - $exprStart - 1));
            $part = strtolower($m[1][0]);
            $replacement = "date_part('{$part}', {$expr})";
            $sql = substr($sql, 0, $matchStart) . $replacement . substr($sql, $pos);
            $offset = $matchStart + strlen($replacement);
        }

        return $sql;
    }

    /**
     * Normaliza <expr> [NOT] ILIKE <pat> a LOWER(<expr>) [NOT] LIKE LOWER(<pat>).
     */
    protected function normalizarIlikePostgres(string $sql): string
    {
        $operandPat = '(?:[a-zA-Z_][\w.]*\s*\([^()]*\)|\([^()]+\)|__TB_SQL_TOK_\d+__|"[^"]+"|[a-zA-Z_][\w.]*)';
        return preg_replace(
            '/(' . $operandPat . ')\s+(NOT\s+)?ILIKE\s+(' . $operandPat . ')/i',
            'LOWER($1) $2LIKE LOWER($3)',
            $sql
        );
    }

    /**
     * Normaliza operadores regex de PostgreSQL (~, !~, ~*, !~*) a llamadas regexp()/iregexp().
     */
    protected function normalizarOperadoresRegexPostgres(string $sql): string
    {
        $operandPat = '(?:[a-zA-Z_][\w.]*\s*\([^()]*\)|__TB_SQL_TOK_\d+__|"[^"]+"|[a-zA-Z_][\w.]*)';
        return preg_replace_callback(
            '/(' . $operandPat . ')\s*(!?~\*?)\s*(' . $operandPat . ')/',
            function ($m) {
                $lhs = $m[1];
                $op = $m[2];
                $rhs = $m[3];
                return match ($op) {
                    '~' => "regexp({$rhs}, {$lhs})",
                    '!~' => "NOT regexp({$rhs}, {$lhs})",
                    '~*' => "iregexp({$rhs}, {$lhs})",
                    '!~*' => "NOT iregexp({$rhs}, {$lhs})",
                    default => $m[0],
                };
            },
            $sql
        );
    }

    /**
     * Normaliza casting ::tipo de PostgreSQL a CAST/DATE/DATETIME de SQLite.
     */
    protected function normalizarCastPostgres(string $sql): string
    {
        $exprPat = '(\([^()]+\)|[a-zA-Z_][\w.]*\s*\([^()]*\)|__TB_SQL_TOK_\d+__|"[^"]+"|[a-zA-Z_][\w.]*|\d+(?:\.\d+)?)';
        $typePat = '(bigint|smallint|integer|int2|int4|int8|int|numeric|decimal|double\s+precision|real|float4|float8|float|character\s+varying|varchar|char|text|boolean|bool|timestamp(?:\s+without\s+time\s+zone)?|date)(?:\s*\(\s*\d+(?:\s*,\s*\d+)?\s*\))?';

        $limit = 10;
        while ($limit-- > 0 && preg_match('/' . $exprPat . '\s*::\s*' . $typePat . '\b/i', $sql)) {
            $sql = preg_replace_callback(
                '/' . $exprPat . '\s*::\s*' . $typePat . '\b/i',
                function ($m) {
                    $expr = trim($m[1]);
                    $type = strtolower(preg_replace('/\s+/', ' ', trim($m[2])));
                    if ($type === 'date') {
                        return "DATE({$expr})";
                    }
                    if (str_starts_with($type, 'timestamp')) {
                        return "DATETIME({$expr})";
                    }
                    if (in_array($type, ['int', 'integer', 'bigint', 'smallint', 'int2', 'int4', 'int8'], true)) {
                        return "CAST({$expr} AS INTEGER)";
                    }
                    if (in_array($type, ['numeric', 'decimal', 'float', 'float4', 'float8', 'real', 'double precision'], true)) {
                        return "CAST({$expr} AS REAL)";
                    }
                    if (in_array($type, ['bool', 'boolean'], true)) {
                        return "(CASE WHEN LOWER(CAST({$expr} AS TEXT)) IN ('1','t','true','y','yes') THEN 1 ELSE 0 END)";
                    }
                    return "CAST({$expr} AS TEXT)";
                },
                $sql
            );
        }

        return $sql;
    }

    /**
     * Normaliza cuantificadores ALL / ANY de PostgreSQL.
     */
    protected function normalizarCuantificadoresPostgres(string $sql): string
    {
        // Convierte: expr >= ALL (SELECT col FROM ...) en: expr >= (SELECT MAX(col) FROM ...)
        $sql = preg_replace_callback(
            '/(>|>=|<|<=)\s*ALL\s*\(\s*SELECT\s+(.+?)\s+FROM\s+/is',
            function($m) {
                $op = $m[1];
                $expr = trim($m[2]);
                if ($op === '>' || $op === '>=') {
                    return "{$op} (SELECT max({$expr}) FROM ";
                } elseif ($op === '<' || $op === '<=') {
                    return "{$op} (SELECT min({$expr}) FROM ";
                }
                return $m[0];
            },
            $sql
        );

        // Convierte: = ANY (...) en: IN (...)
        $sql = preg_replace('/=\s*ANY\s*\(/i', 'IN (', $sql);

        // Convierte: (!= ALL | <> ALL) en: NOT IN (...)
        $sql = preg_replace('/(!=|<>)\s*ALL\s*\(/i', 'NOT IN (', $sql);

        return $sql;
    }

    /**
     * Prepara el archivo SQLite base para el problema a partir de su archivo_adicional.
     */
    protected function prepararBaseDatosPlantilla(Problemas $problema, string $baseDir): ?string
    {
        if (empty($problema->archivo_adicional)) {
            return null;
        }

        $rawPath = $problema->archivo_adicional;
        $filePath = storage_path('app/public/archivos_adicionales/' . basename($rawPath));
        if (!file_exists($filePath)) {
            $filePath = storage_path('app/public/' . ltrim($rawPath, '/\\'));
        }
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
