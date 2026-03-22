<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BlogFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_and_category_filter_show_only_published_posts(): void
    {
        $author = User::factory()->create();

        $categoryNews = Category::query()->create([
            'name' => 'News',
            'slug' => 'news',
            'description' => null,
        ]);
        $categoryDesign = Category::query()->create([
            'name' => 'Design',
            'slug' => 'design',
            'description' => null,
        ]);

        $publishedPost = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'Post Pubblicato',
            'slug' => 'post-pubblicato',
            'excerpt' => 'Estratto',
            'content' => 'Contenuto',
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);
        $publishedPost->categories()->sync([$categoryNews->id]);

        $draftPost = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'Post Draft',
            'slug' => 'post-draft',
            'excerpt' => 'Estratto',
            'content' => 'Contenuto',
            'status' => 'draft',
            'published_at' => null,
        ]);
        $draftPost->categories()->sync([$categoryNews->id]);

        $futurePost = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'Post Futuro',
            'slug' => 'post-futuro',
            'excerpt' => 'Estratto',
            'content' => 'Contenuto',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);
        $futurePost->categories()->sync([$categoryDesign->id]);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee('Post Pubblicato')
            ->assertDontSee('Post Draft')
            ->assertDontSee('Post Futuro');

        $this->get(route('articles.category', $categoryNews->slug))
            ->assertOk()
            ->assertSee('Post Pubblicato')
            ->assertDontSee('Post Draft')
            ->assertDontSee('Post Futuro');
    }

    public function test_article_show_is_visible_only_for_published_posts(): void
    {
        Carbon::setTestNow('2026-03-22 15:00:00');

        $author = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Tech',
            'slug' => 'tech',
            'description' => null,
        ]);

        $publishedPost = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'Articolo pubblico',
            'slug' => 'articolo-pubblico',
            'excerpt' => null,
            'content' => 'Contenuto pubblico',
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);
        $publishedPost->categories()->sync([$category->id]);

        $draftPost = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'Articolo draft',
            'slug' => 'articolo-draft',
            'excerpt' => null,
            'content' => 'Contenuto draft',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $futurePost = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'Articolo futuro',
            'slug' => 'articolo-futuro',
            'excerpt' => null,
            'content' => 'Contenuto futuro',
            'status' => 'published',
            'published_at' => now()->addHours(2),
        ]);

        $this->get(route('articles.show', $publishedPost->slug))
            ->assertOk()
            ->assertSee('Articolo pubblico');

        $this->get(route('articles.show', $draftPost->slug))
            ->assertNotFound();

        $this->get(route('articles.show', $futurePost->slug))
            ->assertNotFound();

        Carbon::setTestNow();
    }
}
