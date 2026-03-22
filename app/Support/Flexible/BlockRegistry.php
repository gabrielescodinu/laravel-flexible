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
                ['name' => 'image_url', 'label' => 'Immagine', 'type' => 'media_image', 'default' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1600&q=80'],
                ['name' => 'image_alt', 'label' => 'Alt immagine', 'type' => 'text', 'default' => 'Hero image'],
            ],
        ],
        'stats_grid' => [
            'label' => 'Stats',
            'view' => 'blocks.stats_grid',
            'fields' => [
                ['name' => 'stats_heading', 'label' => 'Titolo sezione', 'type' => 'text', 'default' => 'Numeri che raccontano la crescita'],
                ['name' => 'stats_description', 'label' => 'Descrizione sezione', 'type' => 'textarea', 'default' => 'Metriche chiave e risultati concreti in un colpo d occhio.'],
                [
                    'name' => 'stats_items',
                    'label' => 'Statistiche',
                    'type' => 'repeater',
                    'fields' => [
                        ['name' => 'stat_data', 'label' => 'Valore', 'type' => 'text', 'default' => '12K+'],
                        ['name' => 'stat_title', 'label' => 'Titolo', 'type' => 'text', 'default' => 'Utenti attivi'],
                    ],
                    'default' => [
                        ['stat_data' => '12K+', 'stat_title' => 'Utenti attivi'],
                        ['stat_data' => '98%', 'stat_title' => 'Soddisfazione clienti'],
                        ['stat_data' => '240+', 'stat_title' => 'Progetti completati'],
                        ['stat_data' => '24/7', 'stat_title' => 'Supporto disponibile'],
                    ],
                ],
            ],
        ],
        'partners' => [
            'label' => 'Partners',
            'view' => 'blocks.partners',
            'fields' => [
                ['name' => 'partners_title', 'label' => 'Titolo', 'type' => 'text', 'default' => 'Partners'],
                ['name' => 'partners_content', 'label' => 'Contenuto', 'type' => 'textarea', 'default' => 'Collaboriamo con aziende e team che condividono la nostra visione.'],
                [
                    'name' => 'logos',
                    'label' => 'Loghi',
                    'type' => 'repeater',
                    'fields' => [
                        ['name' => 'link', 'label' => 'Link', 'type' => 'text', 'default' => '#'],
                        ['name' => 'image', 'label' => 'Logo', 'type' => 'media_image', 'default' => 'https://dummyimage.com/220x72/e2e8f0/0f172a.png&text=Partner'],
                        ['name' => 'image_alt', 'label' => 'Alt logo', 'type' => 'text', 'default' => 'Partner logo'],
                    ],
                    'default' => [
                        ['link' => '#', 'image' => 'https://dummyimage.com/220x72/e2e8f0/0f172a.png&text=Partner+1', 'image_alt' => 'Partner 1'],
                        ['link' => '#', 'image' => 'https://dummyimage.com/220x72/e2e8f0/0f172a.png&text=Partner+2', 'image_alt' => 'Partner 2'],
                        ['link' => '#', 'image' => 'https://dummyimage.com/220x72/e2e8f0/0f172a.png&text=Partner+3', 'image_alt' => 'Partner 3'],
                    ],
                ],
            ],
        ],
        'features' => [
            'label' => 'Features',
            'view' => 'blocks.features',
            'fields' => [
                ['name' => 'features_heading_top', 'label' => 'Heading top', 'type' => 'text', 'default' => 'Core features'],
                ['name' => 'features_heading_main', 'label' => 'Heading principale', 'type' => 'text', 'default' => 'Progettato per crescere con te'],
                ['name' => 'features_description', 'label' => 'Descrizione', 'type' => 'textarea', 'default' => 'Strumenti modulari per gestire contenuti, media e publishing in modo veloce.'],
                [
                    'name' => 'features',
                    'label' => 'Elenco feature',
                    'type' => 'repeater',
                    'fields' => [
                        ['name' => 'feature_icon', 'label' => 'Icona', 'type' => 'media_image', 'default' => ''],
                        ['name' => 'feature_icon_alt', 'label' => 'Alt icona', 'type' => 'text', 'default' => 'Feature icon'],
                        ['name' => 'feature_title', 'label' => 'Titolo', 'type' => 'text', 'default' => 'Titolo feature'],
                        ['name' => 'feature_desc', 'label' => 'Descrizione', 'type' => 'textarea', 'default' => 'Descrizione breve della feature.'],
                    ],
                    'default' => [
                        ['feature_icon' => '', 'feature_icon_alt' => 'Feature 1', 'feature_title' => 'Visual builder', 'feature_desc' => 'Componi pagine a blocchi in pochi minuti.'],
                        ['feature_icon' => '', 'feature_icon_alt' => 'Feature 2', 'feature_title' => 'Media library', 'feature_desc' => 'Gestisci immagini e documenti da un unico pannello.'],
                        ['feature_icon' => '', 'feature_icon_alt' => 'Feature 3', 'feature_title' => 'Revisioni', 'feature_desc' => 'Ripristina versioni precedenti in sicurezza.'],
                        ['feature_icon' => '', 'feature_icon_alt' => 'Feature 4', 'feature_title' => 'SEO friendly', 'feature_desc' => 'URL pulite e struttura contenuti modulare.'],
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
