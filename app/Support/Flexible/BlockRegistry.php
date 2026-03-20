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
                ['name' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text', 'default' => 'Nuovo'],
                ['name' => 'title', 'label' => 'Titolo', 'type' => 'text', 'default' => 'Titolo principale'],
                ['name' => 'subtitle', 'label' => 'Sottotitolo', 'type' => 'textarea', 'default' => 'Scrivi un sottotitolo chiaro e diretto.'],
                ['name' => 'cta_text', 'label' => 'Testo CTA', 'type' => 'text', 'default' => 'Scopri di piu'],
                ['name' => 'cta_url', 'label' => 'URL CTA', 'type' => 'text', 'default' => '#'],
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

            $formatted[$name] = is_scalar($value) ? (string) $value : '';
        }

        return $formatted;
    }
}
