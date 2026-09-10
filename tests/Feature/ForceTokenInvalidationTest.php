<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SingleSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ForceTokenInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_forcibly_invalidate_user_session_remotely()
    {
        Permission::create(['name' => 'editar usuario']);

        $admin = User::factory()->create([
            'email' => 'admin_test@tutorbot.com',
            'username' => 'admin_user',
        ]);
        $admin->givePermissionTo('editar usuario');
        
        $targetUser = User::factory()->create([
            'email' => 'target_test@tutorbot.com',
            'username' => 'target_user',
        ]);

        $service = app(SingleSessionService::class);
        $service->registerSession($targetUser, 'target_active_session_123');

        $this->assertNotNull($targetUser->fresh()->current_session_id);
        $this->assertTrue($service->isValidSession($targetUser, 'target_active_session_123'));

        // Admin forcibly invalidates the target user's session
        $response = $this->actingAs($admin)
            ->post("/usuarios/{$targetUser->id}/forzar-invalidacion");

        $response->assertStatus(302);
        $this->assertNull($targetUser->fresh()->current_session_id);

        // Target user attempts to make a request with the invalidated session ID
        $accessResponse = $this->actingAs($targetUser)
            ->withSession(['_token' => 'mock_token'])
            ->get('/cursos');

        // Access should be blocked and redirected to login
        $accessResponse->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_can_close_other_active_sessions_from_profile()
    {
        $user = User::factory()->create([
            'email' => 'user_profile_test@tutorbot.com',
            'username' => 'profile_user',
        ]);

        $service = app(SingleSessionService::class);

        // Register initial session
        $oldSessionId = 'device_1_old_session';
        $service->registerSession($user, $oldSessionId);

        // User forcibly invalidates other sessions via service / controller
        $currentSessionId = 'device_2_current_session';
        $service->forceInvalidateOtherSessions($user, $currentSessionId);

        // Old session should no longer match active session ID
        $this->assertNotEquals($oldSessionId, $user->fresh()->current_session_id);

        // Current session should be valid
        $this->assertTrue($service->isValidSession($user, $user->fresh()->current_session_id));
        $this->assertEquals($currentSessionId, $user->fresh()->current_session_id);
    }

    public function test_api_remote_token_session_revocation()
    {
        $user = User::factory()->create([
            'email' => 'api_test@tutorbot.com',
            'username' => 'api_user',
        ]);

        $service = app(SingleSessionService::class);
        $service->registerSession($user, 'api_session_999');

        // Directly invoke force invalidation for API user
        $service->forceInvalidateSession($user);

        $this->assertNull($user->fresh()->current_session_id);
    }
}
