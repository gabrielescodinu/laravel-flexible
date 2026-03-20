@extends('layouts.admin')

@section('title', $page->exists ? 'Modifica pagina' : 'Nuova pagina')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Editor</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $page->exists ? 'Modifica pagina' : 'Nuova pagina' }}</h1>
                <p class="muted mt-2">Configura i campi base e componi il layout con blocchi flessibili.</p>
            </div>
            <a class="btn btn-secondary" href="{{ route('admin.pages.index') }}">Torna alla lista</a>
        </div>
    </section>

    <form id="builder-form" class="space-y-4" method="POST" action="{{ $formAction }}">
        @csrf
        @if ($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <section class="panel space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="field">
                    <label class="field-label" for="title">Titolo</label>
                    <input class="field-input" id="title" name="title" type="text" value="{{ old('title', $page->title) }}" required>
                </div>
                <div class="field">
                    <label class="field-label" for="slug">Slug</label>
                    <input class="field-input" id="slug" name="slug" type="text" value="{{ old('slug', $page->slug) }}" placeholder="home" required>
                </div>
            </div>

            <div class="field max-w-52">
                <label class="field-label" for="status">Stato</label>
                <select class="field-input" id="status" name="status" required>
                    @php($currentStatus = old('status', $page->status ?: 'draft'))
                    <option value="draft" @selected($currentStatus === 'draft')>draft</option>
                    <option value="published" @selected($currentStatus === 'published')>published</option>
                </select>
            </div>
        </section>

        <section class="panel space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Flexible Blocks</h2>
                    <p class="muted mt-1">Aggiungi, rimuovi e riordina i blocchi per costruire la pagina.</p>
                </div>
                <div class="flex flex-wrap items-end gap-2">
                    <div class="field min-w-48">
                        <label class="field-label" for="block-type">Tipo blocco</label>
                        <select class="field-input" id="block-type" data-add-type>
                            @foreach ($blockDefinitions as $type => $definition)
                                <option value="{{ $type }}">{{ $definition['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" data-add-block>Aggiungi blocco</button>
                </div>
            </div>

            <div class="space-y-3" data-blocks></div>
        </section>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
            @if ($page->exists && $page->status === 'published')
                <a class="btn btn-secondary" href="{{ $page->slug === 'home' ? route('pages.home') : route('pages.show', $page->slug) }}">Vedi pagina</a>
            @endif
        </div>
    </form>

    <script>
        (() => {
            const definitions = @json($blockDefinitions);
            const oldBlocks = @json(old('blocks'));
            const fallbackBlocks = @json($builderBlocks);

            function normalizeBlocks(value) {
                if (Array.isArray(value)) {
                    return value;
                }

                if (value && typeof value === 'object') {
                    return Object.values(value);
                }

                return [];
            }

            const initialBlocks = normalizeBlocks(oldBlocks).length > 0
                ? normalizeBlocks(oldBlocks)
                : normalizeBlocks(fallbackBlocks);

            const blocksContainer = document.querySelector('[data-blocks]');
            const addTypeSelect = document.querySelector('[data-add-type]');
            const addBlockButton = document.querySelector('[data-add-block]');
            const form = document.getElementById('builder-form');
            const defaultType = Object.keys(definitions)[0] || null;
            let draggingCard = null;
            let blockCounter = 0;

            function makeButton(label, variant = 'secondary') {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `btn ${variant === 'danger' ? 'btn-danger' : 'btn-secondary'}`;
                button.textContent = label;
                return button;
            }

            function setCollapsed(card, collapsed) {
                const fieldsWrap = card.querySelector('[data-fields-wrap]');
                const collapseButton = card.querySelector('[data-collapse-btn]');

                if (!fieldsWrap || !collapseButton) {
                    return;
                }

                if (collapsed) {
                    fieldsWrap.classList.add('hidden');
                    collapseButton.textContent = 'Espandi';
                    card.dataset.collapsed = '1';
                    return;
                }

                fieldsWrap.classList.remove('hidden');
                collapseButton.textContent = 'Comprimi';
                card.dataset.collapsed = '0';
            }

            function normalizeRepeaterItems(value) {
                if (Array.isArray(value)) {
                    return value.filter((item) => item && typeof item === 'object');
                }

                if (value && typeof value === 'object') {
                    return Object.values(value).filter((item) => item && typeof item === 'object');
                }

                return [];
            }

            function createInputElement(field, inputName, value) {
                const isLongText = field.type === 'textarea' || field.type === 'list';
                const input = document.createElement(isLongText ? 'textarea' : 'input');
                input.className = 'field-input';
                input.name = inputName;

                if (!isLongText) {
                    input.type = 'text';
                }

                if (Array.isArray(value)) {
                    input.value = value.join('\n');
                } else if (value === null || value === undefined) {
                    input.value = '';
                } else {
                    input.value = String(value);
                }

                return input;
            }

            function makeField(field, blockIndex, value) {
                const baseInputName = `blocks[${blockIndex}][data][${field.name}]`;

                if (field.type === 'repeater') {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'field md:col-span-2';

                    const label = document.createElement('label');
                    label.className = 'field-label';
                    label.textContent = field.label;
                    wrapper.appendChild(label);

                    const itemsContainer = document.createElement('div');
                    itemsContainer.className = 'space-y-3';
                    wrapper.appendChild(itemsContainer);

                    const addItemButton = makeButton('Aggiungi card');
                    wrapper.appendChild(addItemButton);

                    const subFields = Array.isArray(field.fields) ? field.fields : [];
                    const providedItems = normalizeRepeaterItems(value);
                    const defaultItems = normalizeRepeaterItems(field.default ?? []);
                    const initialItems = providedItems.length > 0 ? providedItems : defaultItems;
                    let repeaterIndex = 0;

                    function addRepeaterItem(itemData = {}) {
                        const itemIndex = repeaterIndex++;
                        const itemCard = document.createElement('div');
                        itemCard.className = 'rounded-xl border border-slate-200 bg-white p-4 space-y-3';
                        itemsContainer.appendChild(itemCard);

                        const itemHeader = document.createElement('div');
                        itemHeader.className = 'flex items-center justify-between gap-2';
                        itemCard.appendChild(itemHeader);

                        const itemTitle = document.createElement('p');
                        itemTitle.className = 'text-sm font-semibold text-slate-700';
                        itemTitle.textContent = `Card ${itemsContainer.children.length}`;
                        itemHeader.appendChild(itemTitle);

                        const removeItemButton = makeButton('Rimuovi', 'danger');
                        itemHeader.appendChild(removeItemButton);

                        const grid = document.createElement('div');
                        grid.className = 'grid gap-3 md:grid-cols-2';
                        itemCard.appendChild(grid);

                        subFields.forEach((subField) => {
                            if (!subField || typeof subField !== 'object') {
                                return;
                            }

                            const subFieldWrapper = document.createElement('div');
                            subFieldWrapper.className = 'field';
                            grid.appendChild(subFieldWrapper);

                            const subLabel = document.createElement('label');
                            subLabel.className = 'field-label';
                            subLabel.textContent = subField.label || subField.name || 'Campo';
                            subFieldWrapper.appendChild(subLabel);

                            const rawValue = itemData[subField.name] ?? subField.default ?? '';
                            const subInputName = `${baseInputName}[${itemIndex}][${subField.name}]`;
                            const subInput = createInputElement(subField, subInputName, rawValue);
                            subFieldWrapper.appendChild(subInput);

                            if (subField.type === 'list') {
                                const hint = document.createElement('small');
                                hint.className = 'muted';
                                hint.textContent = 'Inserisci un elemento per riga.';
                                subFieldWrapper.appendChild(hint);
                            }
                        });

                        removeItemButton.addEventListener('click', () => {
                            itemCard.remove();
                            [...itemsContainer.children].forEach((child, idx) => {
                                const titleNode = child.querySelector('p');
                                if (titleNode) {
                                    titleNode.textContent = `Card ${idx + 1}`;
                                }
                            });
                        });
                    }

                    if (initialItems.length > 0) {
                        initialItems.forEach((item) => addRepeaterItem(item));
                    } else {
                        addRepeaterItem({});
                    }

                    addItemButton.addEventListener('click', () => addRepeaterItem({}));

                    return wrapper;
                }

                const wrapper = document.createElement('div');
                wrapper.className = 'field';

                const label = document.createElement('label');
                label.className = 'field-label';
                label.textContent = field.label;
                wrapper.appendChild(label);

                const input = createInputElement(field, baseInputName, value);
                wrapper.appendChild(input);

                if (field.type === 'list') {
                    const hint = document.createElement('small');
                    hint.className = 'muted';
                    hint.textContent = 'Inserisci un elemento per riga.';
                    wrapper.appendChild(hint);
                }

                return wrapper;
            }

            function updateSortOrders() {
                const cards = blocksContainer.querySelectorAll('[data-builder-block]');
                cards.forEach((card, index) => {
                    const sortInput = card.querySelector('[data-sort-input]');
                    if (sortInput) {
                        sortInput.value = String(index);
                    }
                });
            }

            function getDragAfterElement(container, clientY) {
                const cards = [...container.querySelectorAll('[data-builder-block]:not(.is-dragging)')];

                return cards.reduce((closest, card) => {
                    const box = card.getBoundingClientRect();
                    const offset = clientY - box.top - box.height / 2;

                    if (offset < 0 && offset > closest.offset) {
                        return { offset, element: card };
                    }

                    return closest;
                }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
            }

            function enableDragAndDrop(card, handle) {
                handle.draggable = true;

                handle.addEventListener('dragstart', (event) => {
                    draggingCard = card;
                    card.classList.add('is-dragging', 'opacity-60', 'ring-2', 'ring-slate-300');

                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', card.dataset.builderBlock || 'block');
                    }
                });

                handle.addEventListener('dragend', () => {
                    card.classList.remove('is-dragging', 'opacity-60', 'ring-2', 'ring-slate-300');
                    draggingCard = null;
                    updateSortOrders();
                });
            }

            function createBlock(type, data = {}, sortOrder = 0) {
                if (!defaultType) {
                    return;
                }

                const blockIndex = blockCounter++;
                const blockType = definitions[type] ? type : defaultType;
                const blockData = (data && typeof data === 'object') ? data : {};

                const card = document.createElement('section');
                card.className = 'builder-card space-y-4';
                card.dataset.builderBlock = '1';
                card.dataset.collapsed = '0';

                const sortInput = document.createElement('input');
                sortInput.type = 'hidden';
                sortInput.name = `blocks[${blockIndex}][sort_order]`;
                sortInput.value = String(sortOrder);
                sortInput.dataset.sortInput = '1';
                card.appendChild(sortInput);

                const header = document.createElement('div');
                header.className = 'flex flex-wrap items-end justify-between gap-3';
                card.appendChild(header);

                const left = document.createElement('div');
                left.className = 'flex flex-wrap items-end gap-2';

                const handle = document.createElement('button');
                handle.type = 'button';
                handle.className = 'btn btn-secondary cursor-grab active:cursor-grabbing';
                handle.textContent = '↕';
                handle.title = 'Trascina per riordinare';
                handle.dataset.dragHandle = '1';
                left.appendChild(handle);

                const blockInfo = document.createElement('div');
                blockInfo.className = 'field min-w-48';

                const blockLabel = document.createElement('label');
                blockLabel.className = 'field-label';
                blockLabel.textContent = 'Tipo blocco';
                blockInfo.appendChild(blockLabel);

                const select = document.createElement('select');
                select.className = 'field-input';
                select.name = `blocks[${blockIndex}][type]`;

                Object.entries(definitions).forEach(([blockKey, definition]) => {
                    const option = document.createElement('option');
                    option.value = blockKey;
                    option.textContent = definition.label;
                    select.appendChild(option);
                });

                select.value = blockType;
                blockInfo.appendChild(select);
                left.appendChild(blockInfo);
                header.appendChild(left);

                const actions = document.createElement('div');
                actions.className = 'flex flex-wrap gap-2';
                const collapse = makeButton('Comprimi');
                collapse.dataset.collapseBtn = '1';
                const remove = makeButton('Rimuovi', 'danger');
                actions.appendChild(collapse);
                actions.appendChild(remove);
                header.appendChild(actions);

                const fieldsWrap = document.createElement('div');
                fieldsWrap.className = 'grid gap-4 md:grid-cols-2';
                fieldsWrap.dataset.fieldsWrap = '1';
                card.appendChild(fieldsWrap);

                function renderFields() {
                    fieldsWrap.innerHTML = '';
                    const currentDefinition = definitions[select.value];

                    if (!currentDefinition || !Array.isArray(currentDefinition.fields)) {
                        return;
                    }

                    currentDefinition.fields.forEach((field) => {
                        let rawValue = blockData[field.name];

                        if (rawValue === undefined || rawValue === null) {
                            rawValue = field.default ?? '';
                        }

                        if (Array.isArray(rawValue)) {
                            rawValue = rawValue.join('\n');
                        } else {
                            rawValue = String(rawValue);
                        }

                        fieldsWrap.appendChild(makeField(field, blockIndex, rawValue));
                    });
                }

                select.addEventListener('change', () => {
                    Object.keys(blockData).forEach((key) => delete blockData[key]);
                    renderFields();
                });

                collapse.addEventListener('click', () => {
                    const isCollapsed = card.dataset.collapsed === '1';
                    setCollapsed(card, !isCollapsed);
                });

                remove.addEventListener('click', () => {
                    card.remove();
                    updateSortOrders();
                });

                renderFields();
                blocksContainer.appendChild(card);
                enableDragAndDrop(card, handle);
                setCollapsed(card, false);
                updateSortOrders();
            }

            blocksContainer.addEventListener('dragover', (event) => {
                if (!draggingCard) {
                    return;
                }

                event.preventDefault();
                const afterElement = getDragAfterElement(blocksContainer, event.clientY);

                if (!afterElement) {
                    blocksContainer.appendChild(draggingCard);
                } else {
                    blocksContainer.insertBefore(draggingCard, afterElement);
                }
            });

            blocksContainer.addEventListener('drop', (event) => {
                event.preventDefault();
                updateSortOrders();
            });

            addBlockButton.addEventListener('click', () => {
                createBlock(addTypeSelect.value, {}, blocksContainer.children.length);
            });

            if (Array.isArray(initialBlocks) && initialBlocks.length > 0) {
                initialBlocks.forEach((block, index) => {
                    createBlock(block.type, block.data || {}, block.sort_order ?? index);
                });
            } else if (defaultType) {
                createBlock(defaultType, {}, 0);
            }

            form.addEventListener('submit', updateSortOrders);
        })();
    </script>
@endsection
