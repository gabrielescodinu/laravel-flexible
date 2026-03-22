<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('posts')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['nullable', 'string'],
        ]);

        $slug = $this->resolveUniqueSlug(
            (string) $validated['name'],
            (string) ($validated['slug'] ?? '')
        );

        Category::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria creata.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
        ]);

        $slug = $this->resolveUniqueSlug(
            (string) $validated['name'],
            (string) ($validated['slug'] ?? ''),
            $category->id
        );

        $category->fill([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);
        $category->save();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria aggiornata.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria eliminata.');
    }

    private function resolveUniqueSlug(string $name, string $providedSlug = '', ?int $ignoreCategoryId = null): string
    {
        $base = trim($providedSlug) !== '' ? trim($providedSlug) : Str::slug($name);
        if ($base === '') {
            $base = 'categoria';
        }

        $slug = $base;
        $suffix = 2;

        while (Category::query()
            ->where('slug', $slug)
            ->when($ignoreCategoryId !== null, static fn ($query) => $query->whereKeyNot($ignoreCategoryId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
