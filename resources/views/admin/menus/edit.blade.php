@extends('layouts.admin')

@section('title', 'Menu visitatori')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Navigazione</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Menu visitatori</h1>
                <p class="muted mt-2 max-w-2xl">
                    Configura il menu pubblico del sito (desktop + mobile). Il menu admin resta separato e visibile solo agli utenti autenticati.
                </p>
            </div>

            <a href="{{ route('pages.home') }}" class="btn btn-secondary" target="_blank" rel="noopener">Apri sito</a>
        </div>
    </section>

    <form id="visitor-menu-form" class="mt-4 space-y-4" method="POST" action="{{ route('admin.menus.update') }}">
        @csrf
        @method('PUT')

        <section class="panel space-y-4">
            <h2 class="text-xl font-semibold text-slate-900">Impostazioni</h2>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="field">
                    <label class="field-label" for="name">Nome menu</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        class="field-input"
                        value="{{ old('name', $menu->name) }}"
                        required
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="logo_alt">Logo alt</label>
                    <input
                        id="logo_alt"
                        name="logo_alt"
                        type="text"
                        class="field-input"
                        placeholder="Logo {{ config('app.name') }}"
                        value="{{ old('logo_alt', $menu->logo_alt) }}"
                    >
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="logo_url">Logo URL</label>
                <input
                    id="logo_url"
                    name="logo_url"
                    type="url"
                    class="field-input"
                    placeholder="https://... o /storage/..."
                    value="{{ old('logo_url', $menu->logo_url) }}"
                >
                <p class="text-xs text-slate-500">Puoi copiare l'URL dalla libreria media o usare un path relativo.</p>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input
                    type="checkbox"
                    name="show_search"
                    value="1"
                    @checked(old('show_search', $menu->show_search))
                >
                Mostra pulsante ricerca nel menu
            </label>
        </section>

        <section class="panel space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Voci menu</h2>
                    <p class="muted mt-1">Crea voci principali e sotto-voci impostando il campo "Sottomenu di".</p>
                </div>
                <button type="button" class="btn btn-primary" data-add-item>Aggiungi voce</button>
            </div>

            <div class="space-y-3" data-menu-items></div>
        </section>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">Salva menu visitatori</button>
            <a class="btn btn-secondary" href="{{ route('admin.pages.index') }}">Torna alle pagine</a>
        </div>
    </form>

    <script>
        (() => {
            const container = document.querySelector('[data-menu-items]');
            const addButton = document.querySelector('[data-add-item]');
            const initial = @json(old('items', $menuItems));

            if (!container || !addButton) {
                return;
            }

            const toArray = (value) => {
                if (Array.isArray(value)) {
                    return value;
                }

                if (value && typeof value === 'object') {
                    return Object.values(value);
                }

                return [];
            };

            const rowKey = () => `row-${Date.now()}-${Math.random().toString(16).slice(2, 8)}`;

            const normalize = (raw = {}, index = 0) => ({
                row_key: String(raw.row_key || rowKey()),
                label: String(raw.label || ''),
                url: String(raw.url || ''),
                parent_key: String(raw.parent_key || ''),
                open_in_new_tab: ['1', 'true', 'on', true, 1].includes(raw.open_in_new_tab),
                sort_order: Number.isFinite(Number(raw.sort_order)) ? Number(raw.sort_order) : index,
            });

            let state = toArray(initial).map((item, index) => normalize(item, index));

            const ensureValidParents = () => {
                const keys = new Set(state.map((item) => item.row_key));
                state = state.map((item) => {
                    if (!item.parent_key || !keys.has(item.parent_key) || item.parent_key === item.row_key) {
                        return {
                            ...item,
                            parent_key: '',
                        };
                    }

                    return item;
                });
            };

            const confirmRemove = async () => {
                if (typeof window.adminConfirm === 'function') {
                    return window.adminConfirm({
                        title: 'Rimuovi voce menu',
                        message: 'Vuoi rimuovere questa voce dal menu?',
                        confirmLabel: 'Rimuovi',
                        variant: 'danger',
                    });
                }

                return window.confirm('Vuoi rimuovere questa voce dal menu?');
            };

            const render = () => {
                ensureValidParents();
                container.innerHTML = '';

                if (state.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500';
                    empty.textContent = 'Nessuna voce configurata. Aggiungi la prima voce menu.';
                    container.appendChild(empty);
                    return;
                }

                state.forEach((item, index) => {
                    const card = document.createElement('div');
                    card.className = 'builder-card space-y-3';

                    const hiddenSort = document.createElement('input');
                    hiddenSort.type = 'hidden';
                    hiddenSort.name = `items[${index}][sort_order]`;
                    hiddenSort.value = String(index);
                    card.appendChild(hiddenSort);

                    const hiddenKey = document.createElement('input');
                    hiddenKey.type = 'hidden';
                    hiddenKey.name = `items[${index}][row_key]`;
                    hiddenKey.value = item.row_key;
                    card.appendChild(hiddenKey);

                    const topRow = document.createElement('div');
                    topRow.className = 'flex flex-wrap items-center justify-between gap-2';

                    const title = document.createElement('p');
                    title.className = 'text-sm font-semibold text-slate-700';
                    title.textContent = `Voce ${index + 1}`;
                    topRow.appendChild(title);

                    const actions = document.createElement('div');
                    actions.className = 'flex flex-wrap gap-1.5';

                    const up = document.createElement('button');
                    up.type = 'button';
                    up.className = 'btn btn-secondary';
                    up.textContent = 'Su';
                    up.disabled = index === 0;
                    up.addEventListener('click', () => {
                        if (index === 0) {
                            return;
                        }

                        const previous = state[index - 1];
                        state[index - 1] = state[index];
                        state[index] = previous;
                        render();
                    });

                    const down = document.createElement('button');
                    down.type = 'button';
                    down.className = 'btn btn-secondary';
                    down.textContent = 'Giu';
                    down.disabled = index === state.length - 1;
                    down.addEventListener('click', () => {
                        if (index === state.length - 1) {
                            return;
                        }

                        const next = state[index + 1];
                        state[index + 1] = state[index];
                        state[index] = next;
                        render();
                    });

                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'btn btn-danger';
                    remove.textContent = 'Rimuovi';
                    remove.addEventListener('click', async () => {
                        const confirmed = await confirmRemove();
                        if (!confirmed) {
                            return;
                        }

                        const removedKey = item.row_key;
                        state = state.filter((entry) => entry.row_key !== removedKey)
                            .map((entry) => entry.parent_key === removedKey
                                ? { ...entry, parent_key: '' }
                                : entry);

                        render();
                    });

                    actions.append(up, down, remove);
                    topRow.appendChild(actions);
                    card.appendChild(topRow);

                    const grid = document.createElement('div');
                    grid.className = 'grid gap-3 md:grid-cols-2';

                    const labelWrap = document.createElement('div');
                    labelWrap.className = 'field';

                    const labelField = document.createElement('input');
                    labelField.type = 'text';
                    labelField.className = 'field-input';
                    labelField.name = `items[${index}][label]`;
                    labelField.placeholder = 'Etichetta';
                    labelField.value = item.label;
                    labelField.required = true;
                    labelField.addEventListener('input', (event) => {
                        item.label = event.target.value;
                    });

                    const labelTitle = document.createElement('label');
                    labelTitle.className = 'field-label';
                    labelTitle.textContent = 'Etichetta';

                    labelWrap.append(labelTitle, labelField);

                    const urlWrap = document.createElement('div');
                    urlWrap.className = 'field';

                    const urlField = document.createElement('input');
                    urlField.type = 'text';
                    urlField.className = 'field-input';
                    urlField.name = `items[${index}][url]`;
                    urlField.placeholder = '/contatti oppure https://example.com';
                    urlField.value = item.url;
                    urlField.required = true;
                    urlField.addEventListener('input', (event) => {
                        item.url = event.target.value;
                    });

                    const urlTitle = document.createElement('label');
                    urlTitle.className = 'field-label';
                    urlTitle.textContent = 'URL';

                    urlWrap.append(urlTitle, urlField);

                    const parentWrap = document.createElement('div');
                    parentWrap.className = 'field';

                    const parentTitle = document.createElement('label');
                    parentTitle.className = 'field-label';
                    parentTitle.textContent = 'Sottomenu di';

                    const parentSelect = document.createElement('select');
                    parentSelect.className = 'field-input';
                    parentSelect.name = `items[${index}][parent_key]`;

                    const noneOption = document.createElement('option');
                    noneOption.value = '';
                    noneOption.textContent = 'Nessuna voce padre';
                    parentSelect.appendChild(noneOption);

                    state.forEach((candidate, candidateIndex) => {
                        if (candidate.row_key === item.row_key) {
                            return;
                        }

                        const option = document.createElement('option');
                        option.value = candidate.row_key;
                        option.textContent = candidate.label.trim() !== ''
                            ? candidate.label
                            : `Voce ${candidateIndex + 1}`;
                        parentSelect.appendChild(option);
                    });

                    parentSelect.value = item.parent_key;
                    parentSelect.addEventListener('change', (event) => {
                        item.parent_key = event.target.value;
                    });

                    parentWrap.append(parentTitle, parentSelect);

                    const checkboxWrap = document.createElement('label');
                    checkboxWrap.className = 'field flex items-center gap-2 pt-7 text-sm text-slate-700';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = `items[${index}][open_in_new_tab]`;
                    checkbox.value = '1';
                    checkbox.checked = item.open_in_new_tab;
                    checkbox.addEventListener('change', (event) => {
                        item.open_in_new_tab = event.target.checked;
                    });

                    checkboxWrap.append(checkbox, document.createTextNode('Apri in una nuova scheda'));

                    grid.append(labelWrap, urlWrap, parentWrap, checkboxWrap);
                    card.appendChild(grid);
                    container.appendChild(card);
                });
            };

            addButton.addEventListener('click', () => {
                state.push(normalize({
                    row_key: rowKey(),
                    label: '',
                    url: '',
                    parent_key: '',
                    open_in_new_tab: false,
                }, state.length));
                render();
            });

            render();
        })();
    </script>
@endsection
