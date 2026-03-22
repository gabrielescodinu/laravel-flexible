<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaManagerFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_multiple_files_from_media_manager(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $image = UploadedFile::fake()->image('cover.jpg');
        $document = UploadedFile::fake()->create('manuale.pdf', 120, 'application/pdf');

        $this->post(route('admin.media.store'), [
            'files' => [$image, $document],
        ])->assertRedirect(route('admin.media.index'));

        $this->assertDatabaseCount('media', 2);

        $mediaItems = Media::query()->get();
        foreach ($mediaItems as $item) {
            Storage::disk('public')->assertExists($item->path);
        }
    }

    public function test_media_library_endpoint_returns_only_images_and_supports_search(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson(route('admin.media.upload'), [
            'file' => UploadedFile::fake()->image('banner.jpg'),
        ])->assertOk();

        $this->post(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->create('report.pdf', 120, 'application/pdf')],
        ])->assertRedirect(route('admin.media.index'));

        $this->getJson(route('admin.media.library', ['search' => 'banner']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'banner.jpg');
    }

    public function test_admin_can_delete_uploaded_media_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('admin.media.upload'), [
            'file' => UploadedFile::fake()->image('hero.jpg'),
        ])->assertOk();

        $mediaId = (int) $response->json('id');
        $media = Media::query()->findOrFail($mediaId);

        Storage::disk('public')->assertExists($media->path);

        $this->delete(route('admin.media.destroy', $media))
            ->assertRedirect();

        $this->assertDatabaseMissing('media', [
            'id' => $mediaId,
        ]);
        Storage::disk('public')->assertMissing($media->path);
    }
}
