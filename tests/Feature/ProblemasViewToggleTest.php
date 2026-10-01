<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cursos;
use App\Models\Problemas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProblemasViewToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'ver problemas']);
        Permission::firstOrCreate(['name' => 'acceso al panel de administración']);
        $role = Role::firstOrCreate(['name' => 'administrador']);
        $role->givePermissionTo(['ver problemas', 'acceso al panel de administración']);
    }

    public function test_problemas_index_renders_toggle_buttons_and_views()
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $curso = Cursos::create([
            'codigo' => 'INF101',
            'nombre' => 'Introducción a la Programación',
            'descripcion' => 'Curso inicial',
        ]);

        $problema = Problemas::create([
            'codigo' => 'PROB_01',
            'nombre' => 'Suma Simple',
            'body_problema' => 'Calcular la suma de dos enteros.',
            'dificultad' => 'Fácil',
            'visible' => true,
        ]);

        $problema->cursos()->attach($curso->id);

        $response = $this->actingAs($admin)->get(route('problemas.index'));

        $response->assertStatus(200);
        $response->assertSee('Vista Carpetas');
        $response->assertSee('Todos');
        $response->assertSee('vista-carpetas');
        $response->assertSee('vista-todos');
        $response->assertSee('PROB_01');
        $response->assertSee('Suma Simple');
    }
}
