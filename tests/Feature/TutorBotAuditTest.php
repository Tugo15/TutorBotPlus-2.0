<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class TutorBotAuditTest extends TestCase
{
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
        $resInforme = $this->actingAs($admin)->get('/informes/evaluacion/13');
        $resInforme->assertStatus(200);

        $resExport = $this->actingAs($admin)->get('/informes/evaluacion/13/exportar');
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

