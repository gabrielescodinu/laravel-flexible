<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->with('categories:id,name,slug')
            ->withCount('categories')
            ->orderByDesc('updated_at')
            ->paginate(20);

        $trashedPostsCount = Post::query()->onlyTrashed()->count();

        return view('admin.articles.index', compact('posts', 'trashedPostsCount'));
    }

    public function trash(): View
    {
        $posts = Post::query()
            ->onlyTrashed()
            ->with('categories:id,name,slug')
            ->withCount('categories')
            ->orderByDesc('deleted_at')
            ->paginate(20);

        $trashedPostsCount = (int) $posts->total();

        return view('admin.articles.trash', compact('posts', 'trashedPostsCount'));
    }

    public function create(): View
    {
        return view('admin.articles.form', [
            'post' => new Post([
                'status' => 'draft',
            ]),
            'categories' => Category::query()->orderBy('name')->get(),
            'selectedCategoryIds' => [],
            'formAction' => route('admin.articles.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Crea articolo',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $post = $this->persist($request, new Post());

        return redirect()
            ->route('admin.articles.edit', $post)
            ->with('success', 'Articolo creato.');
    }

    public function edit(Post $post): View
    {
        $post->load('categories:id,name,slug');
        $selectedCategoryIds = $post->categories
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        return view('admin.articles.form', [
            'post' => $post,
            'categories' => Category::query()->orderBy('name')->get(),
            'selectedCategoryIds' => $selectedCategoryIds,
            'formAction' => route('admin.articles.update', $post),
            'formMethod' => 'PUT',
            'submitLabel' => 'Salva articolo',
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->persist($request, $post);

        return redirect()
            ->route('admin.articles.edit', $post)
            ->with('success', 'Articolo aggiornato.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Articolo spostato nel cestino.');
    }

    public function restoreFromTrash(int $postId): RedirectResponse
    {
        $post = Post::query()
            ->onlyTrashed()
            ->findOrFail($postId);

        $post->restore();

        return redirect()
            ->route('admin.articles.trash')
            ->with('success', 'Articolo ripristinato.');
    }

    public function forceDeleteFromTrash(int $postId): RedirectResponse
    {
        $post = Post::query()
            ->onlyTrashed()
            ->findOrFail($postId);

        $post->forceDelete();

        return redirect()
            ->route('admin.articles.trash')
            ->with('success', 'Articolo eliminato definitivamente.');
    }

    private function persist(Request $request, Post $post): Post
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('posts', 'slug')->ignore($post->id),
            ],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'category_ids' => ['array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $publishedAt = null;
        if ($validated['status'] === 'published') {
            $publishedAt = ! empty($validated['published_at'])
                ? Carbon::parse((string) $validated['published_at'])
                : now();
        }

        $categoryIds = collect($validated['category_ids'] ?? [])
            ->map(static fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($post, $validated, $request, $publishedAt, $categoryIds): void {
            $post->fill([
                'author_id' => $post->author_id ?: $request->user()?->id,
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'excerpt' => $validated['excerpt'] ?? null,
                'content' => $validated['content'] ?? null,
                'featured_image' => $validated['featured_image'] ?? null,
                'status' => $validated['status'],
                'published_at' => $publishedAt,
            ]);
            $post->save();

            $post->categories()->sync($categoryIds);
        });

        return $post->refresh()->load('categories:id,name,slug');
    }
}
