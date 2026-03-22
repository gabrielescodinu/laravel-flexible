<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category_and_article_with_category_assignment(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('admin.categories.store'), [
            'name' => 'News',
            'slug' => '',
            'description' => 'Aggiornamenti dal team',
        ])->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->where('slug', 'news')->firstOrFail();

        $this->post(route('admin.articles.store'), [
            'title' => 'Primo articolo',
            'slug' => 'primo-articolo',
            'status' => 'published',
            'excerpt' => 'Estratto breve',
            'content' => 'Contenuto completo del post',
            'featured_image' => '/storage/media/test.jpg',
            'category_ids' => [$category->id],
        ])->assertRedirect();

        $post = Post::query()->where('slug', 'primo-articolo')->firstOrFail();

        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertDatabaseHas('category_post', [
            'post_id' => $post->id,
            'category_id' => $category->id,
        ]);

        $this->get(route('articles.show', $post->slug))
            ->assertOk()
            ->assertSee('Primo articolo');
    }
}
