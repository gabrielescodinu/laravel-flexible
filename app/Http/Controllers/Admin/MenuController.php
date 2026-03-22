<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function edit(): View
    {
        $menu = Menu::query()->firstOrCreate(
            ['location' => Menu::PRIMARY_LOCATION],
            [
                'name' => 'Menu principale',
                'show_search' => true,
            ]
        );

        $menu->load('items');

        return view('admin.menus.edit', [
            'menu' => $menu,
            'menuItems' => $this->itemsForForm($menu),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo_url' => ['nullable', 'string', 'max:2048'],
            'logo_alt' => ['nullable', 'string', 'max:255'],
            'show_search' => ['nullable', 'boolean'],
            'items' => ['array'],
            'items.*.row_key' => ['required', 'string', 'max:80'],
            'items.*.label' => ['required', 'string', 'max:120'],
            'items.*.url' => ['required', 'string', 'max:2048'],
            'items.*.parent_key' => ['nullable', 'string', 'max:80'],
            'items.*.open_in_new_tab' => ['nullable', 'boolean'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $menu = Menu::query()->firstOrCreate(
            ['location' => Menu::PRIMARY_LOCATION],
            [
                'name' => 'Menu principale',
                'show_search' => true,
            ]
        );

        $items = collect($validated['items'] ?? [])
            ->sortBy(static fn (array $item): int => (int) ($item['sort_order'] ?? 0))
            ->values();

        DB::transaction(function () use ($menu, $validated, $items): void {
            $menu->fill([
                'name' => trim((string) $validated['name']),
                'logo_url' => trim((string) ($validated['logo_url'] ?? '')) ?: null,
                'logo_alt' => trim((string) ($validated['logo_alt'] ?? '')) ?: null,
                'show_search' => (bool) ($validated['show_search'] ?? false),
            ]);
            $menu->save();

            $menu->items()->delete();

            /** @var array<string, MenuItem> $createdByRowKey */
            $createdByRowKey = [];
            /** @var array<int, string|null> $pendingParentKeys */
            $pendingParentKeys = [];

            foreach ($items as $index => $item) {
                $rowKey = trim((string) ($item['row_key'] ?? ''));
                if ($rowKey === '') {
                    $rowKey = 'row-'.$index;
                }

                $createdItem = $menu->items()->create([
                    'label' => trim((string) $item['label']),
                    'url' => trim((string) $item['url']),
                    'open_in_new_tab' => (bool) ($item['open_in_new_tab'] ?? false),
                    'sort_order' => $index,
                    'parent_id' => null,
                ]);

                $createdByRowKey[$rowKey] = $createdItem;
                $pendingParentKeys[$createdItem->id] = filled($item['parent_key'] ?? null)
                    ? trim((string) $item['parent_key'])
                    : null;
            }

            foreach ($pendingParentKeys as $itemId => $parentRowKey) {
                if ($parentRowKey === null || ! isset($createdByRowKey[$parentRowKey])) {
                    continue;
                }

                $parentItem = $createdByRowKey[$parentRowKey];
                if ($parentItem->id === $itemId) {
                    continue;
                }

                MenuItem::query()
                    ->whereKey($itemId)
                    ->update(['parent_id' => $parentItem->id]);
            }
        });

        return redirect()
            ->route('admin.menus.edit')
            ->with('success', 'Menu visitatori aggiornato.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function itemsForForm(Menu $menu): array
    {
        if ($menu->items->isNotEmpty()) {
            return $menu->items
                ->map(static fn (MenuItem $item): array => [
                    'row_key' => 'item-'.$item->id,
                    'label' => $item->label,
                    'url' => $item->url,
                    'parent_key' => $item->parent_id ? 'item-'.$item->parent_id : '',
                    'open_in_new_tab' => $item->open_in_new_tab,
                    'sort_order' => $item->sort_order,
                ])
                ->values()
                ->all();
        }

        return [
            [
                'row_key' => 'default-home',
                'label' => 'Home',
                'url' => '/',
                'parent_key' => '',
                'open_in_new_tab' => false,
                'sort_order' => 0,
            ],
            [
                'row_key' => 'default-blog',
                'label' => 'Blog',
                'url' => '/blog',
                'parent_key' => '',
                'open_in_new_tab' => false,
                'sort_order' => 1,
            ],
        ];
    }
}
