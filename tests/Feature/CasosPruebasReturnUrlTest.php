<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Problemas;
use App\Models\Casos_Pruebas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CasosPruebasReturnUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'editar problemas']);
        $role = Role::firstOrCreate(['name' => 'administrador']);
        $role->givePermissionTo(['editar problemas']);
    }

    public function test_return_url_is_captured_and_rendered_in_volver_button()
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnsureSingleSession::class]);

        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $problema = Problemas::create([
            'codigo' => 'PROB_RETURN_TEST',
            'nombre' => 'Problema Retorno',
            'body_problema' => 'Test body',
            'dificultad' => 'Fácil',
            'visible' => true,
        ]);

        $previousUrl = 'http://localhost/problemas/index?id_curso=42';

        $response = $this->actingAs($admin)
            ->from($previousUrl)
            ->get(route('casos_pruebas.assign', ['id' => $problema->id, 'id_curso' => 42]));

        $response->assertStatus(200);
        $response->assertSessionHas('url_retorno_casos_problema_' . $problema->id, $previousUrl);
        $response->assertSee($previousUrl, false);

        // When adding a test case while preserving session
        $postResponse = $this->actingAs($admin)
            ->withSession([
                'url_retorno_casos_problema_' . $problema->id => $previousUrl,
                'id_curso_problema_' . $problema->id => 42,
            ])
            ->from(route('casos_pruebas.assign', ['id' => $problema->id]))
            ->post(route('casos_pruebas.add', ['id' => $problema->id]), [
                'entradas' => '1 2',
                'salidas' => '3',
                'puntos' => 10,
                'id_curso' => 42,
            ]);

        $postResponse->assertRedirect(route('casos_pruebas.assign', ['id' => $problema->id, 'id_curso' => 42]));

        // Visit assign again with that session - it retains the original course return URL in "Volver" button
        $secondGet = $this->actingAs($admin)
            ->withSession([
                'url_retorno_casos_problema_' . $problema->id => $previousUrl,
                'id_curso_problema_' . $problema->id => 42,
            ])
            ->from(route('casos_pruebas.assign', ['id' => $problema->id, 'id_curso' => 42]))
            ->get(route('casos_pruebas.assign', ['id' => $problema->id, 'id_curso' => 42]));

        $secondGet->assertStatus(200);
        $secondGet->assertSee($previousUrl, false);
    }

    public function test_finalizar_wizard_redirects_to_saved_return_url()
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnsureSingleSession::class]);

        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $problema = Problemas::create([
            'codigo' => 'PROB_WIZARD_TEST',
            'nombre' => 'Problema Wizard',
            'body_problema' => 'Test body',
            'dificultad' => 'Fácil',
            'visible' => true,
        ]);

        $previousUrl = 'http://localhost/problemas/index?id_curso=99';

        $response = $this->actingAs($admin)
            ->withSession([
                'url_retorno_casos_problema_' . $problema->id => $previousUrl,
                'id_curso_problema_' . $problema->id => 99,
                'wizard_creacion_problema_' . $problema->id => true,
            ])
            ->get(route('casos_pruebas.assign', [
                'id' => $problema->id,
                'finalizar_wizard' => 1,
            ]));

        $response->assertRedirect($previousUrl);
        $response->assertSessionMissing('url_retorno_casos_problema_' . $problema->id);
        $response->assertSessionMissing('wizard_creacion_problema_' . $problema->id);
    }
}
