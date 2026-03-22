<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_prefers_published_home_page(): void
    {
        $home = Page::query()->create([
            'title' => 'Home ufficiale',
            'slug' => 'home',
            'status' => 'published',
        ]);
        $home->blocks()->create([
            'type' => 'hero',
            'data' => [
                'title' => 'Hero Home',
                'description' => 'Contenuto home',
                'button_primary_text' => '',
                'button_primary_url' => '',
                'button_secondary_text' => '',
                'button_secondary_url' => '',
                'image_url' => '',
                'image_alt' => '',
            ],
            'sort_order' => 0,
        ]);

        Page::query()->create([
            'title' => 'Pagina secondaria',
            'slug' => 'secondaria',
            'status' => 'published',
        ]);

        $this->get(route('pages.home'))
            ->assertOk()
            ->assertSee('Home ufficiale')
            ->assertSee('Contenuto home');
    }

    public function test_show_route_only_displays_published_pages(): void
    {
        Page::query()->create([
            'title' => 'Pubblica',
            'slug' => 'pubblica',
            'status' => 'published',
        ]);
        Page::query()->create([
            'title' => 'Bozza',
            'slug' => 'bozza',
            'status' => 'draft',
        ]);

        $this->get(route('pages.show', 'pubblica'))
            ->assertOk()
            ->assertSee('Pubblica');

        $this->get(route('pages.show', 'bozza'))
            ->assertNotFound();
    }
}
