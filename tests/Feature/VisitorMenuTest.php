<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_configure_visitor_menu_with_nested_items(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Header principale',
            'logo_url' => 'https://example.com/logo.svg',
            'logo_alt' => 'Logo demo',
            'show_search' => '1',
            'items' => [
                [
                    'row_key' => 'home',
                    'label' => 'Home',
                    'url' => '/',
                    'parent_key' => '',
                    'open_in_new_tab' => '0',
                    'sort_order' => 0,
                ],
                [
                    'row_key' => 'services',
                    'label' => 'Servizi',
                    'url' => '/servizi',
                    'parent_key' => '',
                    'open_in_new_tab' => '0',
                    'sort_order' => 1,
                ],
                [
                    'row_key' => 'consulting',
                    'label' => 'Consulenza',
                    'url' => '/servizi/consulenza',
                    'parent_key' => 'services',
                    'open_in_new_tab' => '1',
                    'sort_order' => 2,
                ],
            ],
        ];

        $this->actingAs($user)
            ->put(route('admin.menus.update'), $payload)
            ->assertRedirect(route('admin.menus.edit'));

        $this->assertDatabaseHas('menus', [
            'location' => Menu::PRIMARY_LOCATION,
            'name' => 'Header principale',
            'logo_url' => 'https://example.com/logo.svg',
            'logo_alt' => 'Logo demo',
            'show_search' => 1,
        ]);

        $menu = Menu::query()->where('location', Menu::PRIMARY_LOCATION)->firstOrFail();
        $services = MenuItem::query()
            ->where('menu_id', $menu->id)
            ->where('label', 'Servizi')
            ->firstOrFail();

        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $menu->id,
            'label' => 'Consulenza',
            'parent_id' => $services->id,
            'open_in_new_tab' => 1,
        ]);
    }

    public function test_public_layout_renders_visitor_menu_items(): void
    {
        $menu = Menu::query()->create([
            'name' => 'Menu pubblico',
            'location' => Menu::PRIMARY_LOCATION,
            'logo_url' => 'https://example.com/logo.svg',
            'logo_alt' => 'Logo pubblico',
            'show_search' => false,
        ]);

        $services = MenuItem::query()->create([
            'menu_id' => $menu->id,
            'label' => 'Servizi',
            'url' => '/servizi',
            'parent_id' => null,
            'sort_order' => 0,
            'open_in_new_tab' => false,
        ]);

        MenuItem::query()->create([
            'menu_id' => $menu->id,
            'label' => 'Contatti',
            'url' => '/contatti',
            'parent_id' => null,
            'sort_order' => 1,
            'open_in_new_tab' => false,
        ]);

        MenuItem::query()->create([
            'menu_id' => $menu->id,
            'label' => 'Consulenza',
            'url' => '/servizi/consulenza',
            'parent_id' => $services->id,
            'sort_order' => 2,
            'open_in_new_tab' => false,
        ]);

        $this->get(route('pages.home'))
            ->assertOk()
            ->assertSee('Servizi')
            ->assertSee('Contatti')
            ->assertSee('Consulenza')
            ->assertDontSee('id="search-panel"', false);
    }
}
