# Laravel Flexible Blocks

Documentazione rapida per creare nuovi blocchi modulari nel page builder (stile ACF Flexible).

## Panoramica

Il sistema e basato su 3 pezzi:

1. **Definizione blocco** nel registry.
2. **Form dinamico admin** che legge la definizione e genera i campi.
3. **View frontend** che renderizza il blocco nella pagina pubblica.

File chiave:

- `app/Support/Flexible/BlockRegistry.php`
- `resources/views/admin/pages/form.blade.php`
- `resources/views/blocks/*.blade.php`

---

## Tipi campo supportati

Nel registry puoi usare:

- `text`
- `textarea`
- `list` (una riga = un elemento)
- `repeater` (lista di item con sotto-campi)

Il salvataggio e la normalizzazione dei dati sono gia gestiti da `sanitize()` e `payloadForForm()` in `BlockRegistry`.

---

## Come creare un nuovo blocco (step by step)

### 1) Registra il blocco nel `BlockRegistry`

Apri `app/Support/Flexible/BlockRegistry.php` e aggiungi una nuova entry nell'array `$blocks`.

Esempio minimale:

```php
'my_custom_block' => [
    'label' => 'My Custom Block',
    'view' => 'blocks.my_custom_block',
    'fields' => [
        ['name' => 'title', 'label' => 'Titolo', 'type' => 'text', 'default' => 'Titolo di esempio'],
        ['name' => 'description', 'label' => 'Descrizione', 'type' => 'textarea', 'default' => 'Testo descrittivo'],
    ],
],
```

Note:

- `label`: nome visibile nel selettore blocchi in admin.
- `view`: path Blade senza `.blade.php`.
- `fields`: schema campi usato dal builder.

---

### 2) Crea la view frontend del blocco

Crea il file:

- `resources/views/blocks/my_custom_block.blade.php`

Esempio:

```blade
<section class="content-block">
    <h3 class="text-2xl font-semibold text-slate-900">{{ $data['title'] ?? '' }}</h3>
    <p class="mt-3 text-slate-600">{{ $data['description'] ?? '' }}</p>
</section>
```

`$data` contiene i campi salvati del blocco.

---

### 3) Usa il blocco nel builder

Non devi aggiungere route/controller extra.

Appena il blocco e nel registry:

1. Apri admin (`/admin/pages/...`).
2. Seleziona il nuovo tipo blocco dal dropdown.
3. Compila campi e salva pagina.

---

## Come creare un blocco con repeater

Esempio struttura repeater nel registry:

```php
'fields' => [
    [
        'name' => 'cards',
        'label' => 'Card',
        'type' => 'repeater',
        'fields' => [
            ['name' => 'title', 'label' => 'Titolo', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Testo', 'type' => 'textarea'],
            ['name' => 'image_url', 'label' => 'Immagine', 'type' => 'text'],
        ],
        'default' => [
            ['title' => 'Card 1', 'text' => 'Descrizione', 'image_url' => 'https://...'],
        ],
    ],
],
```

Nel Blade:

```blade
@php($cards = is_array($data['cards'] ?? null) ? $data['cards'] : [])

<div class="grid gap-4 md:grid-cols-3">
    @foreach ($cards as $card)
        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <h4>{{ $card['title'] ?? '' }}</h4>
            <p>{{ $card['text'] ?? '' }}</p>
        </article>
    @endforeach
</div>
```

---

## Checklist veloce prima del commit

1. Hai aggiunto il blocco in `BlockRegistry`?
2. La `view` punta a un file Blade esistente?
3. I nomi campo (`name`) usati nel Blade sono corretti?
4. Hai salvato e verificato da `/admin/pages/...`?
5. Hai controllato rendering su mobile e desktop?

---

## Comandi utili

```bash
php artisan view:cache
php artisan test
```

Se vuoi, posso anche creare un **template generatore** (stub) per aggiungere nuovi blocchi in 30 secondi.
