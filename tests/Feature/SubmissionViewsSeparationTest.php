<?php

namespace Tests\Feature;

use App\Models\Cursa;
use App\Models\Cursos;
use App\Models\EnvioSolucionProblema;
use App\Models\LenguajesProgramaciones;
use App\Models\Problemas;
use App\Models\Resolver;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubmissionViewsSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureSingleSession::class]);

        Permission::firstOrCreate(['name' => 'ver informe del problema']);
        $profRole = Role::firstOrCreate(['name' => 'profesor']);
        $profRole->givePermissionTo(['ver informe del problema']);
        Role::firstOrCreate(['name' => 'estudiante']);
    }

    public function test_student_sees_classic_result_view_and_profesor_sees_split_detail_view(): void
    {
        $profesor = User::factory()->create();
        $profesor->assignRole('profesor');

        $outsiderProf = User::factory()->create();
        $outsiderProf->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('estudiante');

        $curso = Cursos::create([
            'nombre' => 'Taller de Programación II',
            'codigo' => 'TP2-01',
        ]);

        Cursa::create([
            'id_usuario' => $profesor->id,
            'id_curso' => $curso->id,
        ]);
        Cursa::create([
            'id_usuario' => $student->id,
            'id_curso' => $curso->id,
        ]);
        $cursa = Cursa::where('id_usuario', $student->id)
            ->where('id_curso', $curso->id)
            ->firstOrFail();

        $problema = Problemas::create([
            'codigo' => 'fibonacci-recursion',
            'nombre' => 'Fibonacci Recursivo',
            'body_problema' => 'Dado un número N, calcula el N-ésimo término.',
            'dificultad' => 'Medio',
            'visible' => true,
        ]);
        $curso->problemas()->attach($problema->id, ['visible' => true]);

        $lang = LenguajesProgramaciones::create([
            'nombre' => 'C (GCC 7.4.0)',
            'abreviatura' => 'c',
            'extension' => '.c',
            'codigo' => 50,
        ]);

        $resolver = new Resolver();
        $resolver->id_problema = $problema->id;
        $resolver->id_lenguaje = $lang->id;
        $resolver->save();

        $envio = new EnvioSolucionProblema();
        $envio->token = Str::random(32);
        $envio->codigo = 'int main() { return 0; }';
        $envio->id_resolver = $resolver->id;
        $envio->id_cursa = $cursa->id;
        $envio->inicio = Carbon::now()->subMinute();
        $envio->termino = Carbon::now();
        $envio->solucionado = true;
        $envio->puntaje = 100;
        $envio->cant_casos_resuelto = 2;
        $envio->save();

        // Student accessing envios.ver sees the classic result view
        $resStudent = $this->actingAs($student)->get(route('envios.ver', ['token' => $envio->token]));
        $resStudent->assertStatus(200);
        $resStudent->assertSee('Resultados de la Evaluación');
        $resStudent->assertSee('Tiempo en Total:');
        $resStudent->assertDontSee('Vista Dividida (Enunciado y Envío)');

        // Student cannot access the professor detail view
        $resStudentDetalle = $this->actingAs($student)->get(route('informe.envio.detalle', ['token' => $envio->token]));
        $resStudentDetalle->assertStatus(403);

        // Outsider professor not enrolled in the course cannot access the submission detail
        $this->actingAs($outsiderProf)
            ->get(route('informe.envio.detalle', ['token' => $envio->token]))
            ->assertStatus(403);

        // Professor assigned to the course accessing informe.envio.detalle sees the split view
        $resProf = $this->actingAs($profesor)->get(route('informe.envio.detalle', ['token' => $envio->token]));
        $resProf->assertStatus(200);
        $resProf->assertSee('Vista Dividida');
        $resProf->assertSee('Enunciado del Problema');
        $resProf->assertSee('Solo Envío');
        $resProf->assertSee('Código Fuente Entregado');
    }
}
