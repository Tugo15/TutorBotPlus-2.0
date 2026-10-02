<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cursos;
use App\Models\Problemas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InformeCursoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'ver informe del curso']);
        Permission::firstOrCreate(['name' => 'acceso al panel de administración']);
        $role = Role::firstOrCreate(['name' => 'administrador']);
        $role->givePermissionTo(['ver informe del curso', 'acceso al panel de administración']);
    }

    public function test_ver_informe_curso_renders_successfully()
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $curso = Cursos::create([
            'codigo' => 'INF101',
            'nombre' => 'Introducción a la Programación',
            'descripcion' => 'Curso inicial',
        ]);

        $response = $this->actingAs($admin)->get(route('informe.curso', ['id_curso' => $curso->id]));

        $response->assertStatus(200);
        $response->assertSee('Informe del Curso');
        $response->assertSee($curso->nombre);
    }
}
