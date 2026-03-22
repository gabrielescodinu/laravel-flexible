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
                    'type' => 'feature_list',
                    'sort_order' => 0,
                    'data' => [
                        'title' => 'Features',
                        'items' => "Uno\n\nDue\n",
                    ],
                ],
            ],
        ])->assertRedirect();

        $page = Page::query()->where('slug', 'homepage')->firstOrFail();
        $page->load('blocks', 'revisions');

        $this->assertSame('Homepage', $page->title);
        $this->assertCount(2, $page->blocks);
        $this->assertSame('feature_list', $page->blocks[0]->type);
        $this->assertSame(['Uno', 'Due'], $page->blocks[0]->data['items'] ?? []);

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
            'type' => 'rich_text',
            'data' => ['content' => 'Prima versione'],
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
                'type' => 'rich_text',
                'data' => ['content' => 'Prima versione'],
                'sort_order' => 0,
            ]],
        ]);

        $this->put(route('admin.pages.update', $page), [
            'title' => 'Pagina aggiornata',
            'slug' => 'pagina-aggiornata',
            'status' => 'published',
            'blocks' => [
                [
                    'type' => 'cta',
                    'sort_order' => 0,
                    'data' => [
                        'title' => 'Pronto?',
                        'text' => 'Testo CTA',
                        'button_label' => 'Scopri',
                        'button_url' => '/cta',
                    ],
                ],
            ],
        ])->assertRedirect(route('admin.pages.edit', $page));

        $page->refresh()->load('blocks', 'revisions');

        $this->assertSame('Pagina aggiornata', $page->title);
        $this->assertSame('pagina-aggiornata', $page->slug);
        $this->assertSame('published', $page->status);
        $this->assertCount(1, $page->blocks);
        $this->assertSame('cta', $page->blocks[0]->type);
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
}
