<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Support\Flexible\BlockRegistry;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BlockRegistry::class, static fn (): BlockRegistry => new BlockRegistry());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $fallbackItems = [
                [
                    'id' => null,
                    'label' => 'Home',
                    'url' => route('pages.home'),
                    'open_in_new_tab' => false,
                    'children' => [],
                ],
                [
                    'id' => null,
                    'label' => 'Blog',
                    'url' => route('articles.index'),
                    'open_in_new_tab' => false,
                    'children' => [],
                ],
                [
                    'id' => null,
                    'label' => 'Prodotti',
                    'url' => route('products.index'),
                    'open_in_new_tab' => false,
                    'children' => [],
                ],
            ];

            $ecommerceEnabled = SiteSetting::ecommerceEnabled();
            $request = request();
            $cartItems = $request->hasSession()
                ? $request->session()->get('cart.items', [])
                : [];
            $cartItemCount = is_array($cartItems)
                ? (int) collect($cartItems)->sum(static fn (mixed $quantity): int => max(0, (int) $quantity))
                : 0;

            if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
                $view->with('visitorMenu', [
                    'name' => 'Menu principale',
                    'logo_url' => null,
                    'logo_alt' => config('app.name').' logo',
                    'show_search' => true,
                    'items' => $fallbackItems,
                    'ecommerceEnabled' => $ecommerceEnabled,
                    'cartItemCount' => $cartItemCount,
                ]);

                return;
            }

            $menu = Menu::query()
                ->where('location', Menu::PRIMARY_LOCATION)
                ->with(['items' => static fn ($query) => $query->orderBy('sort_order')->orderBy('id')])
                ->first();

            if (! $menu) {
                $view->with('visitorMenu', [
                    'name' => 'Menu principale',
                    'logo_url' => null,
                    'logo_alt' => config('app.name').' logo',
                    'show_search' => true,
                    'items' => $fallbackItems,
                    'ecommerceEnabled' => $ecommerceEnabled,
                    'cartItemCount' => $cartItemCount,
                ]);

                return;
            }

            /** @var array<int, \Illuminate\Support\Collection<int, MenuItem>> $childrenByParent */
            $childrenByParent = $menu->items->groupBy(static fn (MenuItem $item): int => $item->parent_id ?? 0)->all();

            $buildTree = function (int $parentId = 0) use (&$buildTree, $childrenByParent): array {
                $branch = $childrenByParent[$parentId] ?? collect();

                return $branch
                    ->map(static function (MenuItem $item) use (&$buildTree): array {
                        $resolvedUrl = trim((string) $item->url);
                        if ($resolvedUrl === '') {
                            $resolvedUrl = '#';
                        }

                        return [
                            'id' => $item->id,
                            'label' => $item->label,
                            'url' => $resolvedUrl,
                            'open_in_new_tab' => $item->open_in_new_tab,
                            'children' => $buildTree($item->id),
                        ];
                    })
                    ->values()
                    ->all();
            };

            $menuItems = $buildTree(0);
            if ($menuItems === []) {
                $menuItems = $fallbackItems;
            }

            $view->with('visitorMenu', [
                'name' => $menu->name,
                'logo_url' => $menu->logo_url,
                'logo_alt' => $menu->logo_alt ?: config('app.name').' logo',
                'show_search' => $menu->show_search,
                'items' => $menuItems,
                'ecommerceEnabled' => $ecommerceEnabled,
                'cartItemCount' => $cartItemCount,
            ]);
        });
    }
}
