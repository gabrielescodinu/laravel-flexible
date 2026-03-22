<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;

class ArticleController extends Controller
{
    public function index(): View
    {
        if (! $this->blogTablesReady()) {
            return view('articles.index', [
                'posts' => collect(),
                'categories' => collect(),
                'selectedCategory' => null,
            ]);
        }

        $posts = Post::query()
            ->published()
            ->with('categories:id,name,slug')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9);

        $categories = Category::query()
            ->withCount([
                'posts as published_posts_count' => static fn ($query) => $query->published(),
            ])
            ->orderBy('name')
            ->get();

        return view('articles.index', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => null,
        ]);
    }

    public function byCategory(Category $category): View
    {
        if (! $this->blogTablesReady()) {
            throw new ModelNotFoundException();
        }

        $posts = Post::query()
            ->published()
            ->whereHas('categories', static fn ($query) => $query->whereKey($category->id))
            ->with('categories:id,name,slug')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9);

        $categories = Category::query()
            ->withCount([
                'posts as published_posts_count' => static fn ($query) => $query->published(),
            ])
            ->orderBy('name')
            ->get();

        return view('articles.index', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $category,
        ]);
    }

    public function show(Post $post): View|RedirectResponse
    {
        $post->load('categories:id,name,slug');
        $this->ensurePostIsPubliclyVisible($post);

        $primaryCategory = $post->primaryCategory();
        if ($primaryCategory !== null) {
            return redirect()->route('articles.show.category', [
                'category' => $primaryCategory->slug,
                'post' => $post->slug,
            ], 301);
        }

        return $this->renderPost($post);
    }

    public function showByCategory(Category $category, Post $post): View|RedirectResponse
    {
        $post->load('categories:id,name,slug');
        $this->ensurePostIsPubliclyVisible($post);

        if (! $post->categories->contains(static fn (Category $assignedCategory): bool => $assignedCategory->id === $category->id)) {
            throw new ModelNotFoundException();
        }

        $primaryCategory = $post->primaryCategory();
        if ($primaryCategory !== null && $primaryCategory->id !== $category->id) {
            return redirect()->route('articles.show.category', [
                'category' => $primaryCategory->slug,
                'post' => $post->slug,
            ], 301);
        }

        return $this->renderPost($post);
    }

    private function renderPost(Post $post): View
    {
        $relatedPosts = Post::query()
            ->published()
            ->whereKeyNot($post->id)
            ->whereHas('categories', static fn ($query) => $query->whereIn('categories.id', $post->categories->pluck('id')))
            ->with('categories:id,name,slug')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('articles.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }

    private function ensurePostIsPubliclyVisible(Post $post): void
    {
        if ($post->status !== 'published' || ($post->published_at !== null && $post->published_at->isFuture())) {
            throw new ModelNotFoundException();
        }
    }

    private function blogTablesReady(): bool
    {
        return Schema::hasTable('posts') && Schema::hasTable('categories');
    }
}
