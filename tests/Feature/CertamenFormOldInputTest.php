<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cursos;
use App\Models\Problemas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CertamenFormOldInputTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'crear certamen']);
        $role = Role::firstOrCreate(['name' => 'profesor']);
        $role->givePermissionTo(['crear certamen']);
    }

    public function test_selected_problems_and_banks_are_preserved_when_validation_fails()
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnsureSingleSession::class]);

        $profesor = User::factory()->create();
        $profesor->assignRole('profesor');

        $curso = Cursos::create([
            'nombre' => 'Algoritmos y Estructuras',
            'codigo' => 'CS101',
        ]);
        $profesor->cursos()->attach($curso->id);

        $p1 = Problemas::create([
            'codigo' => 'P1_TEST',
            'nombre' => 'Problema Uno',
            'body_problema' => 'Descripción del problema uno',
            'dificultad' => 'Fácil',
            'visible' => true,
        ]);
        $p2 = Problemas::create([
            'codigo' => 'P2_TEST',
            'nombre' => 'Problema Dos',
            'body_problema' => 'Descripción del problema dos',
            'dificultad' => 'Medio',
            'visible' => true,
        ]);

        $curso->problemas()->attach([$p1->id, $p2->id]);

        $bancosJson = json_encode([
            [
                'numero' => 1,
                'id_categoria' => null,
                'nombre_banco' => 'Pregunta 1',
                'problemas' => [$p1->id, $p2->id],
            ],
            [
                'numero' => 2,
                'id_categoria' => null,
                'nombre_banco' => 'Pregunta 2',
                'problemas' => [$p2->id],
            ]
        ]);

        // Submit to store with intentionally missing 'nombre' (validation failure)
        $response = $this->actingAs($profesor)
            ->from(route('certamen.crear', ['id_curso' => $curso->id]))
            ->post(route('certamen.store'), [
                // 'nombre' is missing to trigger validation error
                'descripcion' => str_repeat('a', 60),
                'fecha_inicio' => now()->addDay()->format('Y-m-d H:i'),
                'fecha_termino' => now()->addDay()->addHours(2)->format('Y-m-d H:i'),
                'curso' => $curso->id,
                'bancos_preguntas_json' => $bancosJson,
                'cantidad_problemas' => 2,
            ]);

        $response->assertRedirect(route('certamen.crear', ['id_curso' => $curso->id]));
        $response->assertSessionHasErrors('nombre');

        // Now follow redirect back to crear view with the flashed old input
        $getView = $this->actingAs($profesor)
            ->withSession([
                '_old_input' => [
                    'curso' => $curso->id,
                    'bancos_preguntas_json' => $bancosJson,
                    'cantidad_problemas' => 2,
                    'descripcion' => str_repeat('a', 60),
                ]
            ])
            ->get(route('certamen.crear', ['id_curso' => $curso->id]));

        $getView->assertStatus(200);

        // Assert that the banks and selected problems are rendered in the HTML/JS
        $getView->assertSee('"id":' . $p1->id, false);
        $getView->assertSee('"id":' . $p2->id, false);
        $getView->assertSee('"nombre_banco":"Pregunta 2"', false);
    }
}

