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

        $this->get(route('admin.articles.edit', $post))
            ->assertOk()
            ->assertSee('Primo articolo');

        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertDatabaseHas('category_post', [
            'post_id' => $post->id,
            'category_id' => $category->id,
        ]);

        $this->get(route('articles.show.category', [
            'category' => $category->slug,
            'post' => $post->slug,
        ]))
            ->assertOk()
            ->assertSee('Primo articolo');
    }

    public function test_admin_can_update_and_delete_article(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $categoryA = Category::query()->create([
            'name' => 'Design',
            'slug' => 'design',
            'description' => null,
        ]);
        $categoryB = Category::query()->create([
            'name' => 'Tech',
            'slug' => 'tech',
            'description' => null,
        ]);

        $post = Post::query()->create([
            'author_id' => $user->id,
            'title' => 'Vecchio titolo',
            'slug' => 'vecchio-titolo',
            'excerpt' => 'Estratto',
            'content' => 'Contenuto',
            'status' => 'draft',
        ]);
        $post->categories()->sync([$categoryA->id]);

        $this->put(route('admin.articles.update', $post), [
            'title' => 'Titolo aggiornato',
            'slug' => 'titolo-aggiornato',
            'status' => 'published',
            'excerpt' => 'Nuovo estratto',
            'content' => 'Nuovo contenuto',
            'featured_image' => '/storage/media/new.jpg',
            'category_ids' => [$categoryB->id],
        ])->assertRedirect(route('admin.articles.edit', $post));

        $post->refresh();
        $this->assertSame('Titolo aggiornato', $post->title);
        $this->assertSame('titolo-aggiornato', $post->slug);
        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertDatabaseHas('category_post', [
            'post_id' => $post->id,
            'category_id' => $categoryB->id,
        ]);
        $this->assertDatabaseMissing('category_post', [
            'post_id' => $post->id,
            'category_id' => $categoryA->id,
        ]);

        $this->delete(route('admin.articles.destroy', $post))
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }
}
