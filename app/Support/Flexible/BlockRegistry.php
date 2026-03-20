<?php

namespace App\Support\Flexible;

use Illuminate\Support\Arr;

class BlockRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $blocks = [
        'hero' => [
            'label' => 'Hero',
            'view' => 'blocks.hero',
            'fields' => [
                ['name' => 'layout_direction', 'label' => 'Direzione layout (lg:flex-row o lg:flex-row-reverse)', 'type' => 'text', 'default' => 'lg:flex-row'],
                ['name' => 'title', 'label' => 'Titolo', 'type' => 'text', 'default' => 'Titolo principale hero'],
                ['name' => 'description', 'label' => 'Descrizione', 'type' => 'textarea', 'default' => 'Descrizione di supporto della hero.'],
                ['name' => 'button_primary_text', 'label' => 'Testo bottone primario', 'type' => 'text', 'default' => 'Scopri di piu'],
                ['name' => 'button_primary_url', 'label' => 'URL bottone primario', 'type' => 'text', 'default' => '#'],
                ['name' => 'button_secondary_text', 'label' => 'Testo bottone secondario', 'type' => 'text', 'default' => 'Contattaci'],
                ['name' => 'button_secondary_url', 'label' => 'URL bottone secondario', 'type' => 'text', 'default' => '#'],
                ['name' => 'image_url', 'label' => 'URL immagine', 'type' => 'text', 'default' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1600&q=80'],
                ['name' => 'image_alt', 'label' => 'Alt immagine', 'type' => 'text', 'default' => 'Hero image'],
            ],
        ],
        'rich_text' => [
            'label' => 'Rich Text',
            'view' => 'blocks.rich_text',
            'fields' => [
                ['name' => 'content', 'label' => 'Contenuto', 'type' => 'textarea', 'default' => 'Inserisci qui il testo del blocco.'],
            ],
        ],
        'feature_list' => [
            'label' => 'Feature List',
            'view' => 'blocks.feature_list',
            'fields' => [
                ['name' => 'title', 'label' => 'Titolo', 'type' => 'text', 'default' => 'Perche scegliere questo servizio'],
                ['name' => 'items', 'label' => 'Elementi (uno per riga)', 'type' => 'list', 'default' => ['Veloce', 'Modulare', 'Scalabile']],
            ],
        ],
        'cta' => [
            'label' => 'Call To Action',
            'view' => 'blocks.cta',
            'fields' => [
                ['name' => 'title', 'label' => 'Titolo', 'type' => 'text', 'default' => 'Pronto a partire?'],
                ['name' => 'text', 'label' => 'Testo', 'type' => 'textarea', 'default' => 'Contattaci e costruiamo la pagina insieme.'],
                ['name' => 'button_label', 'label' => 'Testo bottone', 'type' => 'text', 'default' => 'Contattaci'],
                ['name' => 'button_url', 'label' => 'URL bottone', 'type' => 'text', 'default' => '#'],
            ],
        ],
        'travel_cards_grid' => [
            'label' => 'Travel Cards Grid',
            'view' => 'blocks.travel_cards_grid',
            'fields' => [
                ['name' => 'section_title', 'label' => 'Titolo sezione', 'type' => 'text', 'default' => 'Top Destinations'],
                ['name' => 'section_subtitle', 'label' => 'Sottotitolo sezione', 'type' => 'text', 'default' => 'Scopri le mete piu richieste del momento.'],
                [
                    'name' => 'cards',
                    'label' => 'Card',
                    'type' => 'repeater',
                    'fields' => [
                        ['name' => 'title', 'label' => 'Titolo', 'type' => 'text', 'default' => 'Banff, Canada'],
                        ['name' => 'meta', 'label' => 'Meta info', 'type' => 'text', 'default' => 'June 22 - 26 - Local host'],
                        ['name' => 'description', 'label' => 'Descrizione', 'type' => 'textarea', 'default' => 'Turquoise lakes, alpine forests and snowy peaks in wild Canadian beauty.'],
                        ['name' => 'price', 'label' => 'Prezzo', 'type' => 'text', 'default' => '$172 / night'],
                        ['name' => 'badge', 'label' => 'Badge', 'type' => 'text', 'default' => 'Top rated'],
                        ['name' => 'tag_1', 'label' => 'Tag 1', 'type' => 'text', 'default' => 'Adventure'],
                        ['name' => 'tag_2', 'label' => 'Tag 2', 'type' => 'text', 'default' => 'Ancient Monuments'],
                        ['name' => 'rating', 'label' => 'Rating', 'type' => 'text', 'default' => '5.0'],
                        ['name' => 'image_url', 'label' => 'URL immagine', 'type' => 'text', 'default' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1200&q=80'],
                        ['name' => 'cta_text', 'label' => 'Testo CTA', 'type' => 'text', 'default' => 'Book Now'],
                        ['name' => 'cta_url', 'label' => 'URL CTA', 'type' => 'text', 'default' => '#'],
                    ],
                    'default' => [
                        [
                            'title' => 'Banff, Canada',
                            'meta' => 'June 22 - 26 - Local host',
                            'description' => 'Turquoise lakes, alpine forests and snowy peaks in wild Canadian beauty.',
                            'price' => '$172 / night',
                            'badge' => 'Top rated',
                            'tag_1' => 'Adventure',
                            'tag_2' => 'Ancient Monuments',
                            'rating' => '5.0',
                            'image_url' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1200&q=80',
                            'cta_text' => 'Book Now',
                            'cta_url' => '#',
                        ],
                        [
                            'title' => 'Petra, Jordan',
                            'meta' => 'May 1 - 6 - Business host',
                            'description' => 'A lost city carved in rose-colored stone, hidden in majestic desert canyons.',
                            'price' => '$139 / night',
                            'badge' => 'Top rated',
                            'tag_1' => 'Adventure',
                            'tag_2' => 'Ancient Monuments',
                            'rating' => '4.8',
                            'image_url' => 'https://images.unsplash.com/photo-1700344928565-2df11e2d7294?auto=format&fit=crop&w=1200&q=80',
                            'cta_text' => 'Book Now',
                            'cta_url' => '#',
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->blocks;
    }

    /**
     * @return list<string>
     */
    public function types(): array
    {
        return array_keys($this->blocks);
    }

    public function has(string $type): bool
    {
        return isset($this->blocks[$type]);
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(string $type): array
    {
        return $this->blocks[$type] ?? [];
    }

    public function viewFor(string $type): string
    {
        return $this->definition($type)['view'] ?? 'blocks.unknown';
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>
     */
    public function sanitize(string $type, ?array $payload): array
    {
        if (! $this->has($type)) {
            return [];
        }

        $payload = $payload ?? [];
        $sanitized = [];

        foreach ($this->definition($type)['fields'] as $field) {
            $name = (string) $field['name'];
            $fieldType = (string) ($field['type'] ?? 'text');
            $default = $field['default'] ?? null;
            $value = Arr::get($payload, $name, $default);

            if ($fieldType === 'list') {
                $items = is_array($value)
                    ? $value
                    : (preg_split('/\r\n|\r|\n/', (string) $value) ?: []);
                $sanitized[$name] = array_values(array_filter(array_map(
                    static fn (mixed $item): string => trim((string) $item),
                    $items
                ), static fn (string $item): bool => $item !== ''));

                continue;
            }

            if ($fieldType === 'repeater') {
                $items = is_array($value) ? array_values($value) : [];
                $defaultItems = is_array($default) ? $default : [];
                $repeaterFields = is_array($field['fields'] ?? null) ? $field['fields'] : [];

                if ($items === [] && $defaultItems !== []) {
                    $items = $defaultItems;
                }

                $normalizedItems = [];

                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $normalizedItem = [];

                    foreach ($repeaterFields as $repeaterField) {
                        if (! is_array($repeaterField)) {
                            continue;
                        }

                        $repeaterName = (string) ($repeaterField['name'] ?? '');

                        if ($repeaterName === '') {
                            continue;
                        }

                        $repeaterType = (string) ($repeaterField['type'] ?? 'text');
                        $repeaterDefault = $repeaterField['default'] ?? null;
                        $repeaterValue = Arr::get($item, $repeaterName, $repeaterDefault);

                        if ($repeaterType === 'list') {
                            $listItems = is_array($repeaterValue)
                                ? $repeaterValue
                                : (preg_split('/\r\n|\r|\n/', (string) $repeaterValue) ?: []);

                            $normalizedItem[$repeaterName] = array_values(array_filter(array_map(
                                static fn (mixed $listItem): string => trim((string) $listItem),
                                $listItems
                            ), static fn (string $listItem): bool => $listItem !== ''));

                            continue;
                        }

                        $normalizedItem[$repeaterName] = trim((string) $repeaterValue);
                    }

                    $hasContent = collect($normalizedItem)->contains(
                        static fn (mixed $entry): bool => is_array($entry) ? $entry !== [] : $entry !== ''
                    );

                    if ($hasContent) {
                        $normalizedItems[] = $normalizedItem;
                    }
                }

                $sanitized[$name] = $normalizedItems;

                continue;
            }

            $sanitized[$name] = trim((string) $value);
        }

        return $sanitized;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>
     */
    public function payloadForForm(string $type, ?array $payload): array
    {
        $payload = $payload ?? [];

        if (! $this->has($type)) {
            return $payload;
        }

        $formatted = [];

        foreach ($this->definition($type)['fields'] as $field) {
            $name = (string) $field['name'];
            $fieldType = (string) ($field['type'] ?? 'text');
            $default = $field['default'] ?? null;
            $value = Arr::get($payload, $name, $default);

            if ($fieldType === 'list') {
                $list = is_array($value)
                    ? $value
                    : (preg_split('/\r\n|\r|\n/', (string) $value) ?: []);
                $formatted[$name] = implode("\n", array_map(static fn (mixed $item): string => trim((string) $item), $list));

                continue;
            }

            if ($fieldType === 'repeater') {
                $items = is_array($value) ? array_values($value) : [];
                $defaultItems = is_array($default) ? $default : [];
                $repeaterFields = is_array($field['fields'] ?? null) ? $field['fields'] : [];

                if ($items === [] && $defaultItems !== []) {
                    $items = $defaultItems;
                }

                $formattedItems = [];

                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $formattedItem = [];

                    foreach ($repeaterFields as $repeaterField) {
                        if (! is_array($repeaterField)) {
                            continue;
                        }

                        $repeaterName = (string) ($repeaterField['name'] ?? '');

                        if ($repeaterName === '') {
                            continue;
                        }

                        $repeaterType = (string) ($repeaterField['type'] ?? 'text');
                        $repeaterDefault = $repeaterField['default'] ?? null;
                        $repeaterValue = Arr::get($item, $repeaterName, $repeaterDefault);

                        if ($repeaterType === 'list') {
                            $list = is_array($repeaterValue)
                                ? $repeaterValue
                                : (preg_split('/\r\n|\r|\n/', (string) $repeaterValue) ?: []);
                            $formattedItem[$repeaterName] = implode("\n", array_map(
                                static fn (mixed $listItem): string => trim((string) $listItem),
                                $list
                            ));

                            continue;
                        }

                        $formattedItem[$repeaterName] = is_scalar($repeaterValue) ? (string) $repeaterValue : '';
                    }

                    $formattedItems[] = $formattedItem;
                }

                $formatted[$name] = $formattedItems;

                continue;
            }

            $formatted[$name] = is_scalar($value) ? (string) $value : '';
        }

        return $formatted;
    }
}
