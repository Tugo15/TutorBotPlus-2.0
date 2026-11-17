<?php

namespace Tests\Unit;

use App\Models\Casos_Pruebas;
use App\Services\OutputComparatorService;
use App\Services\SqlEvaluationService;
use PDO;
use PHPUnit\Framework\TestCase;

class SqlPostgresCompatTest extends TestCase
{
    private function evalQuery(string $sql, string $verificationSql = ''): array
    {
        $service = new class(new OutputComparatorService()) extends SqlEvaluationService {
            public function runRaw(string $dbPath, string $sql, string $verification = ''): array
            {
                $caso = new Casos_Pruebas();
                $caso->entradas = $verification;
                return $this->ejecutarCaso($dbPath, $sql, $caso);
            }

            public function normalizeSql(string $sql): string
            {
                return $this->normalizarSintaxisPostgres($sql);
            }
        };

        $dbPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pg_compat_' . uniqid() . '.sqlite';
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("
            CREATE TABLE productos (id INTEGER PRIMARY KEY, nombre TEXT, categoria TEXT, precio REAL, fecha TEXT);
            INSERT INTO productos VALUES
                (1, 'Elden Ring', 'RPG', 59.99, '2025-03-15 14:30:00'),
                (2, 'Hollow Knight', 'Metroidvania', 14.99, '2024-06-01 09:00:00'),
                (3, 'Baldur''s Gate 3', 'rpg', 59.99, '2026-11-01 20:00:00');
        ");
        $pdo = null;

        try {
            return $service->runRaw($dbPath, $sql, $verificationSql);
        } finally {
            @unlink($dbPath);
        }
    }

    public function test_ilike_and_not_ilike_work_case_insensitively(): void
    {
        $res = $this->evalQuery("SELECT nombre FROM productos WHERE categoria ILIKE '%rpg%' ORDER BY id;");
        $this->assertNull($res['error']);
        $this->assertSame("Elden Ring\nBaldur's Gate 3", $res['salida']);

        $resNot = $this->evalQuery("SELECT nombre FROM productos WHERE categoria NOT ILIKE 'rpg';");
        $this->assertNull($resNot['error']);
        $this->assertSame('Hollow Knight', $resNot['salida']);
    }

    public function test_postgres_double_colon_casts_work(): void
    {
        $res = $this->evalQuery("SELECT nombre, precio::int, ('10.5'::numeric + 2)::text, '2025-03-15 14:30:00'::date FROM productos WHERE id = 1;");
        $this->assertNull($res['error']);
        $this->assertSame('Elden Ring|59|12.5|2025-03-15', $res['salida']);
    }

    public function test_extract_and_date_trunc_and_interval_work(): void
    {
        $res = $this->evalQuery("
            SELECT EXTRACT(YEAR FROM fecha), DATE_TRUNC('month', fecha), nombre
            FROM productos
            WHERE fecha >= '2025-01-01'::date + INTERVAL '2 months'
            ORDER BY id;
        ");
        $this->assertNull($res['error']);
        $this->assertSame("2025|2025-03-01 00:00:00|Elden Ring\n2026|2026-11-01 00:00:00|Baldur's Gate 3", $res['salida']);

        $resDateOnly = $this->evalQuery("SELECT '2024-04-01'::date - INTERVAL '1 month';");
        $this->assertNull($resDateOnly['error']);
        $this->assertSame('2024-03-01', $resDateOnly['salida']);
    }

    public function test_string_and_math_postgres_functions_work(): void
    {
        $res = $this->evalQuery("
            SELECT
                SPLIT_PART('a-b-c', '-', 2),
                LEFT('PostgreSQL', 4),
                RIGHT('PostgreSQL', 3),
                CHAR_LENGTH('hola'),
                STRPOS('base de datos', 'de'),
                INITCAP('taller de programacion'),
                TRUNC(59.99, 1),
                CONCAT_WS('-', 'A', NULL, 'B');
        ");
        $this->assertNull($res['error']);
        $this->assertSame('b|Post|SQL|4|6|Taller De Programacion|59.9|A-B', $res['salida']);
    }

    public function test_regex_operators_quantifiers_fetch_and_create_or_replace_view_work(): void
    {
        $resRegex = $this->evalQuery("SELECT nombre FROM productos WHERE categoria ~* '^rpg$' AND id = ANY (SELECT id FROM productos WHERE precio >= ALL (SELECT precio FROM productos)) ORDER BY id FETCH FIRST 1 ROWS ONLY;");
        $this->assertNull($resRegex['error']);
        $this->assertSame('Elden Ring', $resRegex['salida']);

        $resView = $this->evalQuery(
            "CREATE OR REPLACE VIEW v_rpg AS SELECT nombre FROM productos WHERE categoria ILIKE 'rpg';",
            "SELECT * FROM v_rpg ORDER BY nombre;"
        );
        $this->assertNull($resView['error']);
        $this->assertSame("Baldur's Gate 3\nElden Ring", $resView['salida']);
    }

    public function test_string_literals_with_postgres_keywords_are_preserved(): void
    {
        $res = $this->evalQuery("SELECT 'foo::int ILIKE bar' AS txt;");
        $this->assertNull($res['error']);
        $this->assertSame('foo::int ILIKE bar', $res['salida']);
    }
}
