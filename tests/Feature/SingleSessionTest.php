<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SingleSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SingleSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_login_registers_active_session()
    {
        $user = User::factory()->create([
            'email' => 'test_single_session@tutorbot.com',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => 'test_single_session@tutorbot.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/cursos');
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotNull($user->current_session_id);

        $service = app(SingleSessionService::class);
        $this->assertTrue($service->isValidSession($user, $user->current_session_id));
    }

    public function test_concurrent_login_invalidates_first_session()
    {
        $user = User::factory()->create([
            'email' => 'test_concurrent@tutorbot.com',
            'password' => 'password123',
        ]);

        $service = app(SingleSessionService::class);

        // Session 1 is registered for User
        $session1Id = 'mock_session_id_device_1';
        $service->registerSession($user, $session1Id);

        $this->assertTrue($service->isValidSession($user, $session1Id));

        // Device 2 logs in -> registers Session 2
        $session2Id = 'mock_session_id_device_2';
        $service->registerSession($user, $session2Id);

        // Session 1 should now be invalid
        $this->assertFalse($service->isValidSession($user, $session1Id));

        // Session 2 should be valid
        $this->assertTrue($service->isValidSession($user, $session2Id));
    }

    public function test_middleware_redirects_invalid_session()
    {
        $user = User::factory()->create([
            'email' => 'test_middleware@tutorbot.com',
            'password' => 'password123',
        ]);

        $service = app(SingleSessionService::class);

        // Register a different active session ID in the service (simulating a second login)
        $service->registerSession($user, 'another_device_session_id');

        // Access protected route as authenticated user with a different session ID
        $response = $this->actingAs($user)
            ->withSession(['_token' => 'dummy_token'])
            ->get('/cursos');

        // Should be logged out and redirected to login with error
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
