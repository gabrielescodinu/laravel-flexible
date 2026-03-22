<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_restores_an_older_revision_of_page_and_blocks(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('admin.pages.store'), [
            'title' => 'Versione A',
            'slug' => 'pagina-test',
            'status' => 'draft',
            'blocks' => [
                [
                    'type' => 'stats_grid',
                    'sort_order' => 0,
                    'data' => [
                        'stats_heading' => 'Statistiche A',
                        'stats_description' => 'Descrizione A',
                        'stats_items' => [
                            [
                                'stat_data' => '100',
                                'stat_title' => 'Card A',
                            ],
                        ],
                    ],
                ],
            ],
        ])->assertRedirect();

        $page = Page::query()->where('slug', 'pagina-test')->firstOrFail();

        $this->put(route('admin.pages.update', $page), [
            'title' => 'Versione B',
            'slug' => 'pagina-test',
            'status' => 'published',
            'blocks' => [
                [
                    'type' => 'stats_grid',
                    'sort_order' => 0,
                    'data' => [
                        'stats_heading' => 'Statistiche B',
                        'stats_description' => 'Descrizione B',
                        'stats_items' => [
                            [
                                'stat_data' => '200',
                                'stat_title' => 'Card B',
                            ],
                        ],
                    ],
                ],
            ],
        ])->assertRedirect();

        $page->refresh();
        $initialRevision = $page->revisions()->oldest('id')->firstOrFail();

        $this->post(route('admin.pages.revisions.restore', [$page, $initialRevision]))
            ->assertRedirect(route('admin.pages.edit', $page));

        $page->refresh();
        $restoredBlock = $page->blocks()->firstOrFail();
        $statsItems = $restoredBlock->data['stats_items'] ?? [];

        $this->assertSame('Versione A', $page->title);
        $this->assertSame('draft', $page->status);
        $this->assertSame('Card A', $statsItems[0]['stat_title'] ?? null);
        $this->assertCount(3, $page->revisions);
    }
}
