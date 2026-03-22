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
            ->assertRedirect(route('articles.show.category', [
                'category' => $category->slug,
                'post' => $publishedPost->slug,
            ]));

        $this->get(route('articles.show.category', [
            'category' => $category->slug,
            'post' => $publishedPost->slug,
        ]))
            ->assertOk()
            ->assertSee('Articolo pubblico');

        $this->get(route('articles.show', $draftPost->slug))
            ->assertNotFound();

        $this->get(route('articles.show', $futurePost->slug))
            ->assertNotFound();

        Carbon::setTestNow();
    }

    public function test_uncategorized_article_uses_slug_only_url(): void
    {
        Carbon::setTestNow('2026-03-22 15:00:00');

        $author = User::factory()->create();

        $post = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'Articolo senza categoria',
            'slug' => 'articolo-senza-categoria',
            'excerpt' => null,
            'content' => 'Contenuto pubblico',
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);

        $this->get(route('articles.show', $post->slug))
            ->assertOk()
            ->assertSee('Articolo senza categoria');

        Carbon::setTestNow();
    }

    public function test_article_show_renders_custom_seo_meta_tags(): void
    {
        $author = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Marketing',
            'slug' => 'marketing',
            'description' => null,
        ]);

        $post = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'Articolo SEO',
            'slug' => 'articolo-seo',
            'excerpt' => 'Estratto',
            'content' => 'Contenuto',
            'status' => 'published',
            'published_at' => now()->subMinutes(10),
            'seo_title' => 'Titolo SEO Articolo',
            'seo_description' => 'Descrizione SEO articolo',
            'seo_keywords' => 'articolo,seo,blog',
            'seo_robots' => 'index,nofollow',
            'seo_canonical_url' => 'https://example.test/blog/marketing/articolo-seo-canonical',
        ]);
        $post->categories()->sync([$category->id]);

        $this->get(route('articles.show.category', [
            'category' => $category->slug,
            'post' => $post->slug,
        ]))
            ->assertOk()
            ->assertSee('<title>Titolo SEO Articolo</title>', false)
            ->assertSee('<meta name="description" content="Descrizione SEO articolo">', false)
            ->assertSee('<meta name="keywords" content="articolo,seo,blog">', false)
            ->assertSee('<meta name="robots" content="index,nofollow">', false)
            ->assertSee('<link rel="canonical" href="https://example.test/blog/marketing/articolo-seo-canonical">', false);
    }
}
