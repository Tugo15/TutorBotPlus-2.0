<?php

namespace Tests\Feature;

use App\Http\Controllers\ProblemasController;
use App\Models\Certamenes;
use App\Models\Cursa;
use App\Models\Cursos;
use App\Models\LenguajesProgramaciones;
use App\Models\Problemas;
use App\Models\ResolucionCertamenes;
use App\Models\Resolver;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureSingleSession::class]);

        Permission::firstOrCreate(['name' => 'crear problemas']);
        Permission::firstOrCreate(['name' => 'editar problemas']);
        $adminRole = Role::firstOrCreate(['name' => 'administrador']);
        $adminRole->givePermissionTo(['crear problemas', 'editar problemas']);
        Role::firstOrCreate(['name' => 'estudiante']);
    }

    public function test_student_cannot_submit_solution_using_another_students_cursa_or_certamen(): void
    {
        $studentA = User::factory()->create();
        $studentA->assignRole('estudiante');

        $studentB = User::factory()->create();
        $studentB->assignRole('estudiante');

        $curso = Cursos::create([
            'nombre' => 'Programación Segura',
            'codigo' => 'SEC101',
        ]);

        $cursaB = Cursa::create([
            'id_usuario' => $studentB->id,
            'id_curso' => $curso->id,
        ]);

        $problema = Problemas::create([
            'codigo' => 'SEC-PROB-1',
            'nombre' => 'Problema Seguro 1',
            'body_problema' => 'Enunciado',
            'dificultad' => 'Fácil',
            'visible' => true,
        ]);
        $curso->problemas()->attach($problema->id, ['visible' => true]);

        $lang = LenguajesProgramaciones::create([
            'nombre' => 'Python 3',
            'abreviatura' => 'py',
            'extension' => '.py',
            'codigo' => 71,
        ]);

        $resolver = new Resolver();
        $resolver->id_problema = $problema->id;
        $resolver->id_lenguaje = $lang->id;
        $resolver->save();

        // Student A tries to submit using Student B's id_cursa
        $resPractice = $this->actingAs($studentA)->post(route('problemas.enviar'), [
            'codigo' => 'print("hello world")',
            'id_problema' => $problema->id,
            'id_resolver' => $resolver->id,
            'id_cursa' => $cursaB->id,
            'lenguaje' => 71,
        ]);
        $resPractice->assertStatus(403);

        // Student A tries to submit using Student B's ResolucionCertamenes
        $certamen = Certamenes::create([
            'nombre' => 'Certamen Seguro',
            'descripcion' => 'Descripción larga para certamen seguro en pruebas.',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHour(),
            'id_curso' => $curso->id,
            'cantidad_problemas' => 1,
            'restriccion_red' => false,
        ]);

        $resolucionA = new ResolucionCertamenes();
        $resolucionA->token = Str::random(40);
        $resolucionA->id_usuario = $studentA->id;
        $resolucionA->id_certamen = $certamen->id;
        $resolucionA->save();

        $resolucionB = new ResolucionCertamenes();
        $resolucionB->token = Str::random(40);
        $resolucionB->id_usuario = $studentB->id;
        $resolucionB->id_certamen = $certamen->id;
        $resolucionB->save();

        $resExam = $this->actingAs($studentA)->post(route('certamenes.enviar_problema'), [
            'codigo' => 'print("hello world")',
            'id_problema' => $problema->id,
            'id_resolver' => $resolver->id,
            'id_certamen' => $resolucionB->id,
            'lenguaje' => 71,
        ]);
        $resExam->assertStatus(403);
    }

    public function test_upload_imagen_requires_permission_and_rejects_svg(): void
    {
        Storage::fake('public');

        $student = User::factory()->create();
        $student->assignRole('estudiante');

        $fakePng = UploadedFile::fake()->image('avatar.png', 100, 100);
        $this->actingAs($student)
            ->post(route('problemas.upload_imagen'), ['image' => $fakePng])
            ->assertStatus(403);

        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $fakeSvg = UploadedFile::fake()->create('xss.svg', 10, 'image/svg+xml');
        $this->actingAs($admin)
            ->post(route('problemas.upload_imagen'), ['image' => $fakeSvg])
            ->assertSessionHasErrors('image');

        $svgDataUri = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        $processed = ProblemasController::almacenarImagenesBase64("![x]({$svgDataUri})");
        $this->assertStringNotContainsString('/storage/problemas/imagenes/', $processed);
    }

    public function test_student_cannot_download_pdf_of_hidden_or_unassigned_problem(): void
    {
        $student = User::factory()->create();
        $student->assignRole('estudiante');

        $hiddenProblem = Problemas::create([
            'codigo' => 'HIDDEN-PDF',
            'nombre' => 'Problema Oculto',
            'body_problema' => 'Secreto',
            'dificultad' => 'Difícil',
            'visible' => false,
        ]);

        $this->actingAs($student)
            ->get(route('problemas.pdf_enunciado', ['id_problema' => $hiddenProblem->id]))
            ->assertStatus(403);
    }

    public function test_exam_initialization_requires_post_enrollment_and_active_window(): void
    {
        $enrolledStudent = User::factory()->create();
        $enrolledStudent->assignRole('estudiante');

        $outsiderStudent = User::factory()->create();
        $outsiderStudent->assignRole('estudiante');

        $curso = Cursos::create([
            'nombre' => 'Curso Evaluado',
            'codigo' => 'EVA101',
        ]);
        $enrolledStudent->cursos()->attach($curso->id);

        $futureCertamen = Certamenes::create([
            'nombre' => 'Certamen Futuro',
            'descripcion' => 'Descripción larga para certamen futuro en pruebas.',
            'fecha_inicio' => Carbon::now()->addHour(),
            'fecha_termino' => Carbon::now()->addHours(3),
            'id_curso' => $curso->id,
            'cantidad_problemas' => 1,
            'restriccion_red' => false,
        ]);

        // Outsider student gets 403 (both GET and POST)
        $this->actingAs($outsiderStudent)
            ->get(route('certamenes.iniciar_resolucion', ['id_certamen' => $futureCertamen->id]))
            ->assertStatus(403);

        $this->actingAs($outsiderStudent)
            ->post(route('certamenes.iniciar_resolucion', ['id_certamen' => $futureCertamen->id]))
            ->assertStatus(403);

        // Enrolled student before fecha_inicio is redirected with error and no ResolucionCertamenes created
        $this->actingAs($enrolledStudent)
            ->post(route('certamenes.iniciar_resolucion', ['id_certamen' => $futureCertamen->id]))
            ->assertRedirect(route('certamenes.ver', ['id_certamen' => $futureCertamen->id]))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('resolucion_certamenes', 0);
    }

    public function test_student_cannot_open_another_students_exam_token_in_resolver(): void
    {
        $studentA = User::factory()->create();
        $studentA->assignRole('estudiante');

        $studentB = User::factory()->create();
        $studentB->assignRole('estudiante');

        $curso = Cursos::create([
            'nombre' => 'Curso Token',
            'codigo' => 'TOK101',
        ]);
        $studentA->cursos()->attach($curso->id);
        $studentB->cursos()->attach($curso->id);

        $problema = Problemas::create([
            'codigo' => 'TOK-PROB',
            'nombre' => 'Problema Token',
            'body_problema' => 'Enunciado',
            'dificultad' => 'Fácil',
            'visible' => true,
        ]);
        $curso->problemas()->attach($problema->id, ['visible' => true]);

        $certamen = Certamenes::create([
            'nombre' => 'Certamen Token',
            'descripcion' => 'Descripción larga para certamen token en pruebas.',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHour(),
            'id_curso' => $curso->id,
            'cantidad_problemas' => 1,
            'restriccion_red' => false,
        ]);

        $resB = new ResolucionCertamenes();
        $resB->token = Str::random(40);
        $resB->id_usuario = $studentB->id;
        $resB->id_certamen = $certamen->id;
        $resB->save();

        $this->actingAs($studentA)
            ->get(route('certamenes.resolver_problema', [
                'token_certamen' => $resB->token,
                'codigo' => $problema->codigo,
            ]))
            ->assertStatus(403);
    }

    public function test_non_admin_professor_cannot_access_other_courses_problems_or_certamenes(): void
    {
        Permission::firstOrCreate(['name' => 'editar certamen']);
        $profRole = Role::firstOrCreate(['name' => 'profesor']);
        $profRole->givePermissionTo(['editar problemas', 'editar certamen']);

        $profA = User::factory()->create();
        $profA->assignRole('profesor');

        $cursoA = Cursos::create(['nombre' => 'Curso A', 'codigo' => 'CUR-A']);
        $cursoB = Cursos::create(['nombre' => 'Curso B', 'codigo' => 'CUR-B']);
        $profA->cursos()->attach($cursoA->id);

        $problemaB = Problemas::create([
            'codigo' => 'PROB-B',
            'nombre' => 'Problema Curso B',
            'body_problema' => 'Enunciado privado del curso B con suficientes caracteres.',
            'dificultad' => 'Medio',
            'visible' => true,
        ]);
        $cursoB->problemas()->attach($problemaB->id, ['visible' => true]);

        $certamenB = Certamenes::create([
            'nombre' => 'Certamen B',
            'descripcion' => 'Descripción larga para certamen del curso B en pruebas.',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHour(),
            'id_curso' => $cursoB->id,
            'cantidad_problemas' => 1,
            'restriccion_red' => false,
        ]);

        $this->actingAs($profA)->get(route('problemas.editar', ['id' => $problemaB->id]))->assertStatus(403);
        $this->actingAs($profA)->get(route('problemas.editar_config_llm', ['id' => $problemaB->id]))->assertStatus(403);
        $this->actingAs($profA)->get(route('certamen.editar', ['id' => $certamenB->id]))->assertStatus(403);
        $this->actingAs($profA)->get(route('certamenes.ver', ['id_certamen' => $certamenB->id]))->assertStatus(403);
    }

    public function test_markdown_renderer_preserves_math_and_strips_unsafe_html(): void
    {
        $html = \App\Support\MarkdownRenderer::render("Calcula \$a_i * b_i\$ y <script>alert(1)</script>\n\n\$\$x^2 + y^2\$\$");
        $this->assertStringContainsString('$a_i * b_i$', $html);
        $this->assertStringContainsString('$$x^2 + y^2$$', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }
}
