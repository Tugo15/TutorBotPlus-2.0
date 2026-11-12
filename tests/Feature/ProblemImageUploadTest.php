<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProblemImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'crear problemas']);
        $role = Role::firstOrCreate(['name' => 'administrador']);
        $role->givePermissionTo(['crear problemas']);
    }

    public function test_authenticated_user_can_upload_image_for_problem()
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnsureSingleSession::class]);

        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $fakeImage = UploadedFile::fake()->image('deck.webp', 300, 300);

        $response = $this->actingAs($admin)
            ->post(route('problemas.upload_imagen'), [
                'image' => $fakeImage,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'url',
            'relative_url',
            'filename',
        ]);

        $filename = $response->json('filename');
        Storage::disk('public')->assertExists('problemas/imagenes/' . $filename);
    }

    public function test_upload_rejects_non_image_files()
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnsureSingleSession::class]);

        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $fakeFile = UploadedFile::fake()->create('malicious.php', 100);

        $response = $this->actingAs($admin)
            ->post(route('problemas.upload_imagen'), [
                'image' => $fakeFile,
            ]);

        $response->assertSessionHasErrors('image');
    }

    public function test_almacenar_imagenes_base64_converts_data_uri_to_stored_file()
    {
        Storage::fake('public');

        // 1x1 png transparente en base64
        $base64Png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        $markdown = "Por lo tanto, quedan 2 cajas sobre la meseta. ![deck.webp](data:image/png;base64,{$base64Png}) Fin.";

        $processed = \App\Http\Controllers\ProblemasController::almacenarImagenesBase64($markdown);

        $this->assertStringNotContainsString('data:image/png;base64', $processed);
        $this->assertStringContainsString('/storage/problemas/imagenes/', $processed);
        $this->assertMatchesRegularExpression('/!\[deck\.webp\]\(\/storage\/problemas\/imagenes\/[a-zA-Z0-9_\.]+\.png\)/', $processed);
    }
}

