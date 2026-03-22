<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageRevision;
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

        $trashedPagesCount = Page::query()->onlyTrashed()->count();

        return view('admin.pages.index', compact('pages', 'trashedPagesCount'));
    }

    public function trash(): View
    {
        $pages = Page::query()
            ->onlyTrashed()
            ->withCount('blocks')
            ->orderByDesc('deleted_at')
            ->paginate(20);

        $trashedPagesCount = (int) $pages->total();

        return view('admin.pages.trash', compact('pages', 'trashedPagesCount'));
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
            'revisions' => collect(),
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
        $revisions = $page->revisions()
            ->with('user')
            ->limit(20)
            ->get();

        return view('admin.pages.form', [
            'page' => $page,
            'formAction' => route('admin.pages.update', $page),
            'formMethod' => 'PUT',
            'submitLabel' => 'Salva modifiche',
            'blockDefinitions' => $registry->all(),
            'builderBlocks' => $this->builderBlocks($page, $registry),
            'revisions' => $revisions,
        ]);
    }

    public function update(Request $request, Page $page, BlockRegistry $registry): RedirectResponse
    {
        $this->savePage($request, $page, $registry);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Pagina aggiornata.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Pagina spostata nel cestino.');
    }

    public function restoreFromTrash(int $pageId): RedirectResponse
    {
        $page = Page::query()
            ->onlyTrashed()
            ->findOrFail($pageId);

        $page->restore();

        return redirect()
            ->route('admin.pages.trash')
            ->with('success', 'Pagina ripristinata.');
    }

    public function forceDeleteFromTrash(int $pageId): RedirectResponse
    {
        $page = Page::query()
            ->onlyTrashed()
            ->findOrFail($pageId);

        $page->forceDelete();

        return redirect()
            ->route('admin.pages.trash')
            ->with('success', 'Pagina eliminata definitivamente.');
    }

    public function restore(Page $page, PageRevision $revision, BlockRegistry $registry): RedirectResponse
    {
        if ($revision->page_id !== $page->id) {
            abort(404);
        }

        $slugConflict = false;

        DB::transaction(function () use ($page, $registry, $revision, &$slugConflict): void {
            $snapshotPage = is_array($revision->page_data) ? $revision->page_data : [];
            $snapshotBlocks = is_array($revision->blocks) ? $revision->blocks : [];

            $snapshotTitle = trim((string) ($snapshotPage['title'] ?? $page->title));
            $snapshotSlug = trim((string) ($snapshotPage['slug'] ?? $page->slug));
            $snapshotStatus = (string) ($snapshotPage['status'] ?? $page->status);
            $resolvedStatus = in_array($snapshotStatus, ['draft', 'published'], true) ? $snapshotStatus : $page->status;

            $slugConflict = $snapshotSlug !== ''
                && Page::query()
                    ->where('slug', $snapshotSlug)
                    ->whereKeyNot($page->id)
                    ->exists();

            $resolvedSlug = $snapshotSlug === '' || $slugConflict
                ? (string) $page->slug
                : $snapshotSlug;

            $page->fill([
                'title' => $snapshotTitle === '' ? (string) $page->title : $snapshotTitle,
                'slug' => $resolvedSlug,
                'status' => $resolvedStatus,
            ]);
            $page->save();

            $page->blocks()->delete();

            $nextSortOrder = 0;
            foreach ($snapshotBlocks as $block) {
                if (! is_array($block)) {
                    continue;
                }

                $type = (string) ($block['type'] ?? '');
                if ($type === '' || ! $registry->has($type)) {
                    continue;
                }

                $rawData = is_array($block['data'] ?? null) ? $block['data'] : [];

                $page->blocks()->create([
                    'type' => $type,
                    'data' => $registry->sanitize($type, $rawData),
                    'sort_order' => $nextSortOrder,
                ]);

                $nextSortOrder++;
            }

            $this->createRevisionSnapshot(
                $page,
                auth()->id(),
                'Ripristino revisione #'.$revision->id
            );
        });

        $message = $slugConflict
            ? 'Revisione ripristinata. Slug non aggiornato per conflitto con un\'altra pagina.'
            : 'Revisione ripristinata con successo.';

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', $message);
    }

    private function savePage(Request $request, Page $page, BlockRegistry $registry): Page
    {
        $isNewPage = ! $page->exists;

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

        DB::transaction(function () use ($page, $validated, $blocks, $registry, $request, $isNewPage): void {
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

            $this->createRevisionSnapshot(
                $page,
                $request->user()?->id,
                $isNewPage ? 'Creazione pagina' : 'Salvataggio manuale'
            );
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

    private function createRevisionSnapshot(Page $page, ?int $userId, ?string $label = null): void
    {
        $blocksSnapshot = $page->blocks()
            ->orderBy('sort_order')
            ->get()
            ->map(static fn (PageBlock $block): array => [
                'type' => $block->type,
                'data' => $block->data ?? [],
                'sort_order' => (int) $block->sort_order,
            ])
            ->values()
            ->all();

        $page->revisions()->create([
            'user_id' => $userId,
            'label' => $label,
            'page_data' => [
                'title' => (string) $page->title,
                'slug' => (string) $page->slug,
                'status' => (string) $page->status,
            ],
            'blocks' => $blocksSnapshot,
        ]);
    }
}
