<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cursos;
use App\Models\Certamenes;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TutorBotAuditTest extends TestCase
{
    use RefreshDatabase;

    protected Certamenes $certamen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureSingleSession::class]);

        $permisos = [
            'acceso al panel de administración',
            'ver problemas',
            'ver informe del certamen',
            'ver informe del curso',
            'ver certamen',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        $roleAdmin = Role::firstOrCreate(['name' => 'administrador']);
        $roleAdmin->syncPermissions($permisos);

        $roleProfesor = Role::firstOrCreate(['name' => 'profesor']);
        $roleProfesor->syncPermissions($permisos);

        $roleEstudiante = Role::firstOrCreate(['name' => 'estudiante']);

        $admin = User::factory()->create(['email' => 'admin@tutorbot.com']);
        $admin->assignRole($roleAdmin);

        $profesor = User::factory()->create(['email' => 'profesor@tutorbot.com']);
        $profesor->assignRole($roleProfesor);

        $estudiante = User::factory()->create(['email' => 'estudiante@tutorbot.com']);
        $estudiante->assignRole($roleEstudiante);

        $curso = Cursos::create([
            'nombre' => 'Programación I',
            'descripcion' => 'Curso de prueba QA',
            'codigo' => 'INF-QA-01',
        ]);

        $profesor->cursos()->attach($curso->id);
        $estudiante->cursos()->attach($curso->id);

        $this->certamen = Certamenes::create([
            'nombre' => 'Certamen QA',
            'descripcion' => 'Evaluación de prueba para auditoría',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHours(2),
            'id_curso' => $curso->id,
            'cantidad_problemas' => 2,
            'restriccion_red' => false,
        ]);
    }

    public function test_scheduled_command_finalizar_certamenes_expirados_runs_cleanly(): void
    {
        $exitCode = Artisan::call('app:finalizar-certamenes-expirados');
        $this->assertSame(0, $exitCode);
    }

    public function test_admin_can_access_all_problems_and_export_exam_report_csv(): void
    {
        $admin = User::where('email', 'admin@tutorbot.com')->first();
        $this->assertNotNull($admin);

        // 1. Vista "Todos" en Gestión de Problemas
        $resProblemas = $this->actingAs($admin)->get('/problemas/index?todos=1');
        $resProblemas->assertStatus(200);

        // 2. Informe de Certamen y Exportación CSV
        $resInforme = $this->actingAs($admin)->get('/informes/evaluacion/' . $this->certamen->id);
        $resInforme->assertStatus(200);

        $resExport = $this->actingAs($admin)->get('/informes/evaluacion/' . $this->certamen->id . '/exportar');
        $resExport->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resExport->headers->get('Content-Type'));
    }

    public function test_profesor_and_estudiante_role_access_controls(): void
    {
        $profesor = User::where('email', 'profesor@tutorbot.com')->first();
        $estudiante = User::where('email', 'estudiante@tutorbot.com')->first();
        $this->assertNotNull($profesor);
        $this->assertNotNull($estudiante);

        // Profesor accede a su panel e informes
        $this->actingAs($profesor)->get('/inicio')->assertStatus(200);
        $this->actingAs($profesor)->get('/problemas/index?todos=1')->assertStatus(200);

        // Estudiante no puede entrar al panel de administración (/inicio -> 403)
        $this->actingAs($estudiante)->get('/inicio')->assertStatus(403);
        // Estudiante accede a sus cursos y evaluaciones
        $this->actingAs($estudiante)->get('/')->assertStatus(200);
        $this->actingAs($estudiante)->get('/evaluaciones')->assertStatus(200);
    }
}

