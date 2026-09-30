<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cursos;
use App\Models\Certamenes;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionalNetworkTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cursos $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->curso = Cursos::create([
            'nombre' => 'Programación I',
            'descripcion' => 'Curso de prueba para red institucional',
            'codigo' => 'IN2533C-01',
        ]);

        $this->user->cursos()->attach($this->curso->id);
    }

    public function test_access_allowed_when_network_restriction_is_disabled()
    {
        $certamen = Certamenes::create([
            'nombre' => 'Certamen Sin Restricción',
            'descripcion' => 'Evaluación abierta para prueba de red sin restricciones',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHours(2),
            'id_curso' => $this->curso->id,
            'restriccion_red' => false,
            'ips_autorizadas' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->withServerVariables(['REMOTE_ADDR' => '200.1.2.3'])
            ->get(route('certamenes.iniciar_resolucion', ['id_certamen' => $certamen->id]));

        $response->assertSessionHasNoErrors();
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_access_allowed_from_loopback_ip()
    {
        $certamen = Certamenes::create([
            'nombre' => 'Certamen Restringido Loopback',
            'descripcion' => 'Evaluación con restricción de red para probar IP local',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHours(2),
            'id_curso' => $this->curso->id,
            'restriccion_red' => true,
            'ips_autorizadas' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get(route('certamenes.iniciar_resolucion', ['id_certamen' => $certamen->id]));

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_access_allowed_from_institutional_private_ip()
    {
        $certamen = Certamenes::create([
            'nombre' => 'Certamen Restringido IP Privada',
            'descripcion' => 'Evaluación con restricción para IP privada institucional 10.x.x.x',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHours(2),
            'id_curso' => $this->curso->id,
            'restriccion_red' => true,
            'ips_autorizadas' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->withServerVariables(['REMOTE_ADDR' => '10.20.30.40'])
            ->get(route('certamenes.iniciar_resolucion', ['id_certamen' => $certamen->id]));

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_access_denied_from_external_ip()
    {
        $certamen = Certamenes::create([
            'nombre' => 'Certamen Restringido IP Externa',
            'descripcion' => 'Evaluación que debe rechazar conexiones fuera de la red institucional',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHours(2),
            'id_curso' => $this->curso->id,
            'restriccion_red' => true,
            'ips_autorizadas' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->withServerVariables(['REMOTE_ADDR' => '200.50.100.15'])
            ->get(route('certamenes.iniciar_resolucion', ['id_certamen' => $certamen->id]));

        $response->assertRedirect(route('certamenes.listado'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Acceso restringido', session('error'));
    }

    public function test_access_denied_from_external_ip_json_request()
    {
        $certamen = Certamenes::create([
            'nombre' => 'Certamen Restringido JSON Request',
            'descripcion' => 'Evaluación con restricción probando respuesta JSON 403',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHours(2),
            'id_curso' => $this->curso->id,
            'restriccion_red' => true,
            'ips_autorizadas' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->withServerVariables(['REMOTE_ADDR' => '200.50.100.15'])
            ->getJson(route('certamenes.iniciar_resolucion', ['id_certamen' => $certamen->id]));

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('Acceso restringido', $response->json('error'));
    }

    public function test_access_allowed_from_custom_authorized_cidr()
    {
        $certamen = Certamenes::create([
            'nombre' => 'Certamen Rango Personalizado CIDR',
            'descripcion' => 'Evaluación con subred personalizada en ips_autorizadas',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHours(2),
            'id_curso' => $this->curso->id,
            'restriccion_red' => true,
            'ips_autorizadas' => "200.50.0.0/16\n203.0.113.88",
        ]);

        $responseAllowedCidr = $this->actingAs($this->user)
            ->withServerVariables(['REMOTE_ADDR' => '200.50.12.34'])
            ->get(route('certamenes.iniciar_resolucion', ['id_certamen' => $certamen->id]));

        $this->assertNotEquals(403, $responseAllowedCidr->getStatusCode());
    }

    public function test_access_allowed_from_custom_authorized_exact_ip()
    {
        $certamen = Certamenes::create([
            'nombre' => 'Certamen IP Exacta Personalizada',
            'descripcion' => 'Evaluación con IP exacta autorizada',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHours(2),
            'id_curso' => $this->curso->id,
            'restriccion_red' => true,
            'ips_autorizadas' => "200.50.0.0/16\n203.0.113.88",
        ]);

        $responseAllowedExact = $this->actingAs($this->user)
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.88'])
            ->get(route('certamenes.iniciar_resolucion', ['id_certamen' => $certamen->id]));

        $this->assertNotEquals(403, $responseAllowedExact->getStatusCode());
    }

    public function test_access_denied_outside_custom_authorized_range()
    {
        $certamen = Certamenes::create([
            'nombre' => 'Certamen Rechazo Fuera de Rango Personalizado',
            'descripcion' => 'Evaluación rechaza IP fuera de la lista personalizada',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_termino' => Carbon::now()->addHours(2),
            'id_curso' => $this->curso->id,
            'restriccion_red' => true,
            'ips_autorizadas' => "200.50.0.0/16\n203.0.113.88",
        ]);

        $responseDenied = $this->actingAs($this->user)
            ->withServerVariables(['REMOTE_ADDR' => '198.51.100.1'])
            ->get(route('certamenes.iniciar_resolucion', ['id_certamen' => $certamen->id]));

        $responseDenied->assertRedirect(route('certamenes.listado'));
        $responseDenied->assertSessionHas('error');
    }
}
