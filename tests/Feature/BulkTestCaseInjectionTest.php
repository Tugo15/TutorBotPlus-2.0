<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Problemas;
use App\Models\Casos_Pruebas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BulkTestCaseInjectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'editar problemas']);
        $role = Role::firstOrCreate(['name' => 'administrador']);
        $role->givePermissionTo(['editar problemas']);
    }

    public function test_bulk_add_casos_with_json_format()
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $problema = Problemas::create([
            'codigo' => 'PROB_BULK_JSON',
            'nombre' => 'Problema Inyeccion JSON',
            'body_problema' => 'Suma dos numeros',
            'dificultad' => 'Fácil',
            'visible' => true,
        ]);

        $jsonPayload = json_encode([
            ['entradas' => "2\n3", 'salidas' => '5', 'puntos' => 10, 'ejemplo' => true],
            ['entradas' => "10\n20", 'salidas' => '30', 'puntos' => 15, 'ejemplo' => false],
        ]);

        $response = $this->actingAs($admin)->post(route('casos_pruebas.bulk_add'), [
            'id_problema' => $problema->id,
            'contenido_masivo' => $jsonPayload,
            'puntos_defecto' => 10,
        ]);

        $response->assertRedirect(route('casos_pruebas.assign', ['id' => $problema->id]));
        $response->assertSessionHas('success');

        $this->assertEquals(2, $problema->casos_de_prueba()->count());
        $this->assertEquals(25, $problema->fresh()->puntaje_total);
    }

    public function test_bulk_add_casos_with_block_delimiter_format()
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $problema = Problemas::create([
            'codigo' => 'PROB_BULK_BLOCK',
            'nombre' => 'Problema Inyeccion Bloque',
            'body_problema' => 'Determinar Par o Impar',
            'dificultad' => 'Fácil',
            'visible' => true,
        ]);

        $blockContent = "===\nINPUT:\n4\nOUTPUT:\nPaR\nPUNTOS: 10\nEJEMPLO: 1\n===\nINPUT:\n7\nOUTPUT:\nImpaR\nPUNTOS: 10\n===";

        $response = $this->actingAs($admin)->post(route('casos_pruebas.bulk_add'), [
            'id_problema' => $problema->id,
            'contenido_masivo' => $blockContent,
            'puntos_defecto' => 10,
        ]);

        $response->assertRedirect(route('casos_pruebas.assign', ['id' => $problema->id]));
        $response->assertSessionHas('success');

        $this->assertEquals(2, $problema->casos_de_prueba()->count());
        $this->assertEquals(20, $problema->fresh()->puntaje_total);
    }

    public function test_bulk_add_casos_with_pipe_format()
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $problema = Problemas::create([
            'codigo' => 'PROB_BULK_PIPE',
            'nombre' => 'Problema Inyeccion Pipe',
            'body_problema' => 'Evaluacion de lineas pipe',
            'dificultad' => 'Medio',
            'visible' => true,
        ]);

        $pipeContent = "2 | PaR\n4 | PaR\n3 | ImpaR";

        $response = $this->actingAs($admin)->post(route('casos_pruebas.bulk_add'), [
            'id_problema' => $problema->id,
            'contenido_masivo' => $pipeContent,
            'puntos_defecto' => 5,
        ]);

        $response->assertRedirect(route('casos_pruebas.assign', ['id' => $problema->id]));
        $response->assertSessionHas('success');

        $this->assertEquals(3, $problema->casos_de_prueba()->count());
        $this->assertEquals(15, $problema->fresh()->puntaje_total);
    }
}
