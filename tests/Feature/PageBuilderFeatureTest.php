<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBuilderFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_page_with_flexible_blocks_and_revision(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('admin.pages.store'), [
            'title' => 'Homepage',
            'slug' => 'homepage',
            'status' => 'draft',
            'blocks' => [
                [
                    'type' => 'hero',
                    'sort_order' => 2,
                    'data' => [
                        'title' => 'Titolo Hero',
                        'description' => 'Descrizione Hero',
                        'button_primary_text' => 'Vai',
                        'button_primary_url' => '/go',
                    ],
                ],
                [
                    'type' => 'stats_grid',
                    'sort_order' => 0,
                    'data' => [
                        'stats_heading' => 'KPI',
                        'stats_description' => 'Numeri principali',
                        'stats_items' => [
                            [
                                'stat_data' => '10+',
                                'stat_title' => 'Uno',
                            ],
                            [
                                'stat_data' => '20+',
                                'stat_title' => 'Due',
                            ],
                        ],
                    ],
                ],
            ],
        ])->assertRedirect();

        $page = Page::query()->where('slug', 'homepage')->firstOrFail();
        $page->load('blocks', 'revisions');

        $this->assertSame('Homepage', $page->title);
        $this->assertCount(2, $page->blocks);
        $this->assertSame('stats_grid', $page->blocks[0]->type);
        $this->assertSame('Uno', $page->blocks[0]->data['stats_items'][0]['stat_title'] ?? null);

        $this->assertCount(1, $page->revisions);
        $this->assertSame('Creazione pagina', $page->revisions[0]->label);
    }

    public function test_admin_can_update_page_and_create_new_revision_snapshot(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = Page::query()->create([
            'title' => 'Vecchia pagina',
            'slug' => 'vecchia-pagina',
            'status' => 'draft',
        ]);
        $page->blocks()->create([
            'type' => 'hero',
            'data' => ['title' => 'Prima versione', 'description' => 'Contenuto iniziale'],
            'sort_order' => 0,
        ]);
        $page->revisions()->create([
            'user_id' => $user->id,
            'label' => 'Creazione pagina',
            'page_data' => [
                'title' => $page->title,
                'slug' => $page->slug,
                'status' => $page->status,
            ],
            'blocks' => [[
                'type' => 'hero',
                'data' => ['title' => 'Prima versione', 'description' => 'Contenuto iniziale'],
                'sort_order' => 0,
            ]],
        ]);

        $this->put(route('admin.pages.update', $page), [
            'title' => 'Pagina aggiornata',
            'slug' => 'pagina-aggiornata',
            'status' => 'published',
            'blocks' => [
                [
                    'type' => 'features',
                    'sort_order' => 0,
                    'data' => [
                        'features_heading_top' => 'Sezione',
                        'features_heading_main' => 'Funzionalita',
                        'features_description' => 'Dettaglio',
                        'features' => [
                            [
                                'feature_icon' => '',
                                'feature_icon_alt' => 'Icona',
                                'feature_title' => 'Feature 1',
                                'feature_desc' => 'Descrizione 1',
                            ],
                        ],
                    ],
                ],
            ],
        ])->assertRedirect(route('admin.pages.edit', $page));

        $page->refresh()->load('blocks', 'revisions');

        $this->assertSame('Pagina aggiornata', $page->title);
        $this->assertSame('pagina-aggiornata', $page->slug);
        $this->assertSame('published', $page->status);
        $this->assertCount(1, $page->blocks);
        $this->assertSame('features', $page->blocks[0]->type);
        $this->assertCount(2, $page->revisions);
        $this->assertSame('Salvataggio manuale', $page->revisions[0]->label);
    }

    public function test_restore_returns_404_if_revision_belongs_to_different_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pageA = Page::query()->create([
            'title' => 'Pagina A',
            'slug' => 'pagina-a',
            'status' => 'draft',
        ]);
        $pageB = Page::query()->create([
            'title' => 'Pagina B',
            'slug' => 'pagina-b',
            'status' => 'draft',
        ]);

        $foreignRevision = $pageB->revisions()->create([
            'user_id' => $user->id,
            'label' => 'Snapshot B',
            'page_data' => [
                'title' => 'Pagina B',
                'slug' => 'pagina-b',
                'status' => 'draft',
            ],
            'blocks' => [],
        ]);

        $this->post(route('admin.pages.revisions.restore', [$pageA, $foreignRevision]))
            ->assertNotFound();
    }

    public function test_page_can_be_moved_to_trash_restored_and_force_deleted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = Page::query()->create([
            'title' => 'Pagina cestino',
            'slug' => 'pagina-cestino',
            'status' => 'draft',
        ]);
        $page->blocks()->create([
            'type' => 'hero',
            'data' => ['title' => 'Titolo'],
            'sort_order' => 0,
        ]);

        $this->delete(route('admin.pages.destroy', $page))
            ->assertRedirect(route('admin.pages.index'));

        $this->assertSoftDeleted('pages', [
            'id' => $page->id,
        ]);

        $this->post(route('admin.pages.restore', $page->id))
            ->assertRedirect(route('admin.pages.trash'));

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'deleted_at' => null,
        ]);

        $this->delete(route('admin.pages.destroy', $page))
            ->assertRedirect(route('admin.pages.index'));

        $this->delete(route('admin.pages.force-delete', $page->id))
            ->assertRedirect(route('admin.pages.trash'));

        $this->assertDatabaseMissing('pages', [
            'id' => $page->id,
        ]);
    }
}
