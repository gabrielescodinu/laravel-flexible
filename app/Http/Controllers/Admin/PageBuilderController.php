<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageBlock;
use App\Support\Flexible\BlockRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PageBuilderController extends Controller
{
    public function index(): View
    {
        $pages = Page::query()
            ->withCount('blocks')
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function create(BlockRegistry $registry): View
    {
        $page = new Page([
            'status' => 'draft',
        ]);

        return view('admin.pages.form', [
            'page' => $page,
            'formAction' => route('admin.pages.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Crea pagina',
            'blockDefinitions' => $registry->all(),
            'builderBlocks' => [],
        ]);
    }

    public function store(Request $request, BlockRegistry $registry): RedirectResponse
    {
        $page = $this->savePage($request, new Page(), $registry);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Pagina creata.');
    }

    public function edit(Page $page, BlockRegistry $registry): View
    {
        $page->load('blocks');

        return view('admin.pages.form', [
            'page' => $page,
            'formAction' => route('admin.pages.update', $page),
            'formMethod' => 'PUT',
            'submitLabel' => 'Salva modifiche',
            'blockDefinitions' => $registry->all(),
            'builderBlocks' => $this->builderBlocks($page, $registry),
        ]);
    }

    public function update(Request $request, Page $page, BlockRegistry $registry): RedirectResponse
    {
        $this->savePage($request, $page, $registry);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Pagina aggiornata.');
    }

    private function savePage(Request $request, Page $page, BlockRegistry $registry): Page
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('pages', 'slug')->ignore($page->id),
            ],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'blocks' => ['array'],
            'blocks.*.type' => ['required', Rule::in($registry->types())],
            'blocks.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'blocks.*.data' => ['array'],
        ]);

        $blocks = collect($validated['blocks'] ?? [])
            ->sortBy(static fn (array $block): int => (int) ($block['sort_order'] ?? 0))
            ->values();

        DB::transaction(function () use ($page, $validated, $blocks, $registry): void {
            $page->fill([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'status' => $validated['status'],
            ]);
            $page->save();

            $page->blocks()->delete();

            foreach ($blocks as $index => $block) {
                $page->blocks()->create([
                    'type' => $block['type'],
                    'data' => $registry->sanitize($block['type'], $block['data'] ?? []),
                    'sort_order' => $index,
                ]);
            }
        });

        return $page->refresh();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function builderBlocks(Page $page, BlockRegistry $registry): array
    {
        return $page->blocks
            ->map(static fn (PageBlock $block): array => [
                'type' => $block->type,
                'sort_order' => $block->sort_order,
                'data' => $registry->payloadForForm($block->type, $block->data ?? []),
            ])
            ->values()
            ->all();
    }
}
