<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_categories_with_unique_slug_generation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('admin.categories.store'), [
            'name' => 'News',
            'slug' => '',
            'description' => 'Prima categoria',
        ])->assertRedirect(route('admin.categories.index'));

        $this->post(route('admin.categories.store'), [
            'name' => 'News',
            'slug' => '',
            'description' => 'Seconda categoria',
        ])->assertRedirect(route('admin.categories.index'));

        $first = Category::query()->where('slug', 'news')->firstOrFail();
        $second = Category::query()->where('slug', 'news-2')->firstOrFail();

        $this->put(route('admin.categories.update', $first), [
            'name' => 'News Updated',
            'slug' => 'news-updated',
            'description' => 'Aggiornata',
        ])->assertRedirect(route('admin.categories.index'));

        $first->refresh();
        $this->assertSame('News Updated', $first->name);
        $this->assertSame('news-updated', $first->slug);

        $this->delete(route('admin.categories.destroy', $second))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', [
            'id' => $second->id,
        ]);
    }
}
