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
                    'type' => 'travel_cards_grid',
                    'sort_order' => 0,
                    'data' => [
                        'section_title' => 'Destinazioni A',
                        'section_subtitle' => 'Sottotitolo A',
                        'cards' => [
                            [
                                'title' => 'Card A',
                                'meta' => 'Meta A',
                                'description' => 'Descrizione A',
                                'price' => '$100 / night',
                                'badge' => 'Top rated',
                                'tag_1' => 'Adventure',
                                'tag_2' => 'Nature',
                                'rating' => '4.9',
                                'image_url' => 'https://example.com/a.jpg',
                                'cta_text' => 'Book',
                                'cta_url' => '#',
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
                    'type' => 'travel_cards_grid',
                    'sort_order' => 0,
                    'data' => [
                        'section_title' => 'Destinazioni B',
                        'section_subtitle' => 'Sottotitolo B',
                        'cards' => [
                            [
                                'title' => 'Card B',
                                'meta' => 'Meta B',
                                'description' => 'Descrizione B',
                                'price' => '$200 / night',
                                'badge' => 'Top rated',
                                'tag_1' => 'Adventure',
                                'tag_2' => 'Culture',
                                'rating' => '4.7',
                                'image_url' => 'https://example.com/b.jpg',
                                'cta_text' => 'Book',
                                'cta_url' => '#',
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
        $cards = $restoredBlock->data['cards'] ?? [];

        $this->assertSame('Versione A', $page->title);
        $this->assertSame('draft', $page->status);
        $this->assertSame('Card A', $cards[0]['title'] ?? null);
        $this->assertCount(3, $page->revisions);
    }
}
