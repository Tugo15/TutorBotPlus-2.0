<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnvioSolucionProblema;
use App\Models\EvaluacionSolucion;
use App\Models\Problemas;
use App\Models\User;
use App\Models\Cursos;
use App\Models\JuecesVirtuales;
use App\Models\LenguajesProgramaciones;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EnvioSolucionProblemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estudiante = User::where('username', 'estudiante')->first() ?? User::first();
        $juez = JuecesVirtuales::first();
        $curso = Cursos::first();

        if (!$estudiante || !$juez || !$curso) {
            return;
        }

        $cursa = DB::table('cursa')
            ->where('id_usuario', $estudiante->id)
            ->where('id_curso', $curso->id)
            ->first();

        if (!$cursa) {
            $cursaId = DB::table('cursa')->insertGetId([
                'id_usuario' => $estudiante->id,
                'id_curso' => $curso->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $cursaId = $cursa->id;
        }

        $pyLang = LenguajesProgramaciones::where('abreviatura', 'LIKE', '%py%')->first();
        $sqlLang = LenguajesProgramaciones::where('abreviatura', 'LIKE', '%sql%')->first();
        $cppLang = LenguajesProgramaciones::where('abreviatura', 'LIKE', '%C++%')->first() ?? LenguajesProgramaciones::first();

        // Helper function to seed an envio with evaluations
        $crearEnvio = function ($codigoProblema, $lenguajeObj, $codigoFuente, $solucionado, $cumpleRestricciones, $verificacionRestricciones) use ($cursaId, $juez, $estudiante) {
            $problema = Problemas::where('codigo', $codigoProblema)->first();
            if (!$problema) return;

            $resolver = DB::table('resolver')
                ->where('id_problema', $problema->id)
                ->where('id_lenguaje', $lenguajeObj ? $lenguajeObj->id : 1)
                ->first();

            if (!$resolver) {
                $resolverId = DB::table('resolver')->insertGetId([
                    'id_problema' => $problema->id,
                    'id_lenguaje' => $lenguajeObj ? $lenguajeObj->id : 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } else {
                $resolverId = $resolver->id;
            }

            $casos = $problema->casos_de_prueba;
            $cantCasos = $casos->count();
            $casosAprobados = $solucionado ? $cantCasos : max(0, $cantCasos - 1);
            $puntajeTotal = $solucionado ? $problema->puntaje_total : 0;

            $envio = EnvioSolucionProblema::create([
                'token' => Str::random(40),
                'id_cursa' => $cursaId,
                'id_resolver' => $resolverId,
                'id_juez' => $juez->id,
                'codigo' => $codigoFuente,
                'inicio' => Carbon::now()->subHours(2),
                'termino' => Carbon::now()->subHours(2)->addSeconds(2),
                'cant_casos_resuelto' => $casosAprobados,
                'puntaje' => $puntajeTotal,
                'solucionado' => $solucionado ? 1 : 0,
                'cumple_restricciones' => $cumpleRestricciones,
                'verificacion_restricciones' => $verificacionRestricciones,
                'ip_origen' => '127.0.0.1',
            ]);

            foreach ($casos as $index => $caso) {
                EvaluacionSolucion::create([
                    'id_envio' => $envio->id,
                    'token' => Str::random(40),
                    'id_caso' => $caso->id,
                    'estado' => $solucionado ? 'Aceptado' : ($index == 0 ? 'Aceptado' : 'Rechazado'),
                    'resultado' => $solucionado ? 'Accepted' : 'Wrong Answer',
                    'stout' => $caso->salidas,
                    'tiempo' => '0.005',
                    'memoria' => '1200',
                ]);
            }
        };

        // 1. Sumar A y B (Aceptado)
        $crearEnvio(
            'suma-a-b',
            $pyLang,
            "import sys\n\nlines = sys.stdin.read().split()\nif len(lines) >= 2:\n    print(int(lines[0]) + int(lines[1]))",
            true,
            null,
            null
        );

        // 2. Tabla de Multiplicar con For (Cumple Restricción)
        $crearEnvio(
            'tabla-multiplicar-for',
            $pyLang,
            "n = int(input())\nres = []\nfor i in range(1, 6):\n    res.append(str(n * i))\nprint(' '.join(res))",
            true,
            1,
            'El código utiliza adecuadamente el ciclo "for" para iterar y no emplea ciclos "while". Cumple con la restricción impuesta.'
        );

        // 3. Tabla de Multiplicar con For (No Cumple Restricción - Usó while)
        $crearEnvio(
            'tabla-multiplicar-for',
            $pyLang,
            "n = int(input())\ni = 1\nres = []\nwhile i <= 5:\n    res.append(str(n * i))\n    i += 1\nprint(' '.join(res))",
            true,
            0,
            'No cumple: El código utiliza un ciclo "while", violando la restricción explícita de utilizar únicamente la estructura "for".'
        );

        // 4. SQL Clientes VIP (Cumple Restricción)
        $crearEnvio(
            'sql-clientes-vip',
            $sqlLang,
            "SELECT c.nombre, SUM(co.monto) AS total_gastado\nFROM clientes c\nINNER JOIN compras co ON c.id = co.cliente_id\nGROUP BY c.id, c.nombre\nHAVING SUM(co.monto) >= 500\nORDER BY total_gastado DESC;",
            true,
            1,
            'Cumple: La consulta utiliza INNER JOIN explícito y agrupamiento con GROUP BY y HAVING sin recurrir a subconsultas.'
        );

        // 5. SQL Clientes VIP (No Cumple Restricción - Usó Subconsulta)
        $crearEnvio(
            'sql-clientes-vip',
            $sqlLang,
            "SELECT nombre, (SELECT SUM(monto) FROM compras WHERE cliente_id = c.id) AS total_gastado\nFROM clientes c\nWHERE (SELECT SUM(monto) FROM compras WHERE cliente_id = c.id) >= 500\nORDER BY total_gastado DESC;",
            true,
            0,
            'No cumple: La consulta utiliza subconsultas en el SELECT y WHERE, lo cual está explícitamente prohibido por la restricción.'
        );

        // 6. Factorial Recursivo (Cumple Restricción)
        $crearEnvio(
            'factorial-recursion',
            $cppLang,
            "#include <iostream>\nusing namespace std;\n\nlong long factorial(int n) {\n    if (n <= 1) return 1;\n    return n * factorial(n - 1);\n}\n\nint main() {\n    int n;\n    if (cin >> n) {\n        cout << factorial(n) << endl;\n    }\n    return 0;\n}",
            true,
            1,
            'Cumple: El problema fue implementado mediante una función recursiva pura sin hacer uso de ciclos iterativos.'
        );
    }
}
