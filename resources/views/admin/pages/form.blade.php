@extends('layouts.admin')

@section('title', $page->exists ? 'Modifica pagina' : 'Nuova pagina')

@section('content')
    <style>
        .wysiwyg-editor ul {
            list-style: disc;
            margin-left: 1.25rem;
            padding-left: 0.25rem;
        }

        .wysiwyg-editor ol {
            list-style: decimal;
            margin-left: 1.25rem;
            padding-left: 0.25rem;
        }

        .wysiwyg-editor li {
            margin: 0.125rem 0;
        }

        .wysiwyg-editor p + p,
        .wysiwyg-editor ul + p,
        .wysiwyg-editor ol + p,
        .wysiwyg-editor p + ul,
        .wysiwyg-editor p + ol {
            margin-top: 0.5rem;
        }
    </style>

    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Editor</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $page->exists ? 'Modifica pagina' : 'Nuova pagina' }}</h1>
                <p class="muted mt-2">Configura i campi base e componi il layout con blocchi flessibili.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($page->exists)
                    <button type="button" class="btn btn-secondary" data-open-revisions>
                        Revisioni ({{ $revisions->count() }})
                    </button>
                    <form
                        action="{{ route('admin.pages.destroy', $page) }}"
                        method="POST"
                        data-confirm-title="Sposta pagina nel cestino"
                        data-confirm-message="Vuoi spostare questa pagina nel cestino?"
                        data-confirm-label="Sposta nel cestino"
                        data-confirm-variant="danger"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Elimina</button>
                    </form>
                @endif
                <a class="btn btn-secondary" href="{{ route('admin.pages.index') }}">Torna alla lista</a>
            </div>
        </div>
    </section>

    @if ($page->exists)
        <div
            class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/45 p-4"
            data-revisions-modal
            aria-hidden="true"
        >
            <div class="w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Revisioni</h2>
                        <p class="muted mt-1 text-xs">Snapshot automatici ad ogni salvataggio. Puoi ripristinare una versione precedente.</p>
                    </div>
                    <button type="button" class="btn btn-secondary" data-close-revisions>Chiudi</button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto p-4">
                    @if ($revisions->isEmpty())
                        <p class="muted text-sm">Nessuna revisione disponibile.</p>
                    @else
                        <div class="mb-3">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                {{ $revisions->count() }} recenti
                            </span>
                        </div>
                        <div class="space-y-2">
                            @foreach ($revisions as $revision)
                                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">
                                            {{ $revision->label ?: 'Snapshot automatico' }}
                                        </p>
                                        <p class="truncate text-xs text-slate-500">
                                            #{{ $revision->id }} · {{ $revision->created_at?->format('d/m/Y H:i') }} · {{ $revision->user?->name ?: 'Sistema' }}
                                        </p>
                                    </div>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.pages.revisions.restore', [$page, $revision]) }}"
                                        onsubmit="return confirm('Ripristinare questa revisione? Le modifiche correnti verranno sovrascritte.')"
                                    >
                                        @csrf
                                        <button type="submit" class="btn btn-secondary">Ripristina</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="border-t border-slate-200 px-4 py-3">
                    <div class="flex justify-end">
                        <button type="button" class="btn btn-secondary" data-close-revisions>Chiudi</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
            const revisionsModal = document.querySelector('[data-revisions-modal]');
            const openRevisionsButton = document.querySelector('[data-open-revisions]');
            const closeRevisionsButtons = [...document.querySelectorAll('[data-close-revisions]')];

            if (revisionsModal && openRevisionsButton) {
                const openRevisionsModal = () => {
                    revisionsModal.classList.remove('hidden');
                    revisionsModal.classList.add('flex');
                    revisionsModal.setAttribute('aria-hidden', 'false');
                };

                const closeRevisionsModal = () => {
                    revisionsModal.classList.add('hidden');
                    revisionsModal.classList.remove('flex');
                    revisionsModal.setAttribute('aria-hidden', 'true');
                };

                const onRevisionsEscape = (event) => {
                    if (event.key === 'Escape' && revisionsModal.getAttribute('aria-hidden') === 'false') {
                        closeRevisionsModal();
                    }
                };

                openRevisionsButton.addEventListener('click', openRevisionsModal);
                closeRevisionsButtons.forEach((button) => {
                    button.addEventListener('click', closeRevisionsModal);
                });

                revisionsModal.addEventListener('click', (event) => {
                    if (event.target === revisionsModal) {
                        closeRevisionsModal();
                    }
                });

                document.addEventListener('keydown', onRevisionsEscape);
            }
        })();
    </script>

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
            const csrfToken = form.querySelector('input[name="_token"]')?.value || '';
            const mediaUploadEndpoint = @json(route('admin.media.upload'));
            const mediaLibraryEndpoint = @json(route('admin.media.library'));
            const defaultType = Object.keys(definitions)[0] || null;
            const collapseOnInitialLoad = @json($page->exists);
            let draggingCard = null;
            let blockCounter = 0;

            async function requestDeleteConfirmation(options = {}) {
                if (typeof window.adminConfirm === 'function') {
                    return window.adminConfirm({
                        title: options.title || 'Conferma eliminazione',
                        message: options.message || 'Questa azione non puo essere annullata.',
                        confirmLabel: options.confirmLabel || 'Elimina',
                        variant: options.variant || 'danger',
                    });
                }

                return window.confirm(options.message || 'Confermi questa eliminazione?');
            }

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
                    collapseButton.textContent = '▸';
                    collapseButton.setAttribute('title', 'Espandi blocco');
                    collapseButton.setAttribute('aria-label', 'Espandi blocco');
                    card.dataset.collapsed = '1';
                    return;
                }

                fieldsWrap.classList.remove('hidden');
                collapseButton.textContent = '▾';
                collapseButton.setAttribute('title', 'Comprimi blocco');
                collapseButton.setAttribute('aria-label', 'Comprimi blocco');
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
                } else if (typeof value === 'object') {
                    input.value = '';
                } else {
                    input.value = String(value);
                }

                return input;
            }

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function normalizeEditorHtml(value) {
                const raw = Array.isArray(value) ? value.join('\n') : String(value ?? '');
                const trimmed = raw.trim();

                if (trimmed === '') {
                    return '';
                }

                if (/<\s*[a-z][^>]*>/i.test(trimmed)) {
                    return trimmed;
                }

                return trimmed
                    .split(/\r\n|\r|\n/)
                    .map((line) => `<p>${line === '' ? '<br>' : escapeHtml(line)}</p>`)
                    .join('');
            }

            function makeWysiwygField(field, inputName, value, options = {}) {
                const wrapper = document.createElement('div');
                wrapper.className = options.fullWidth === false ? 'field' : 'field md:col-span-2';

                const label = document.createElement('label');
                label.className = 'field-label';
                label.textContent = field.label || 'Contenuto';
                wrapper.appendChild(label);

                const toolbar = document.createElement('div');
                toolbar.className = 'mb-2 flex flex-wrap items-center gap-1 rounded-xl border border-slate-200 bg-white p-1';
                wrapper.appendChild(toolbar);

                const editor = document.createElement('div');
                editor.className = 'wysiwyg-editor min-h-28 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-slate-400 focus:outline-none';
                editor.contentEditable = 'true';
                editor.innerHTML = normalizeEditorHtml(value);
                wrapper.appendChild(editor);

                const hiddenInput = document.createElement('textarea');
                hiddenInput.className = 'hidden';
                hiddenInput.name = inputName;
                wrapper.appendChild(hiddenInput);

                const syncValue = () => {
                    hiddenInput.value = editor.innerHTML.trim();
                };

                const applyCommand = (command, commandValue = null) => {
                    editor.focus();
                    if (commandValue === null) {
                        document.execCommand(command, false);
                    } else {
                        document.execCommand(command, false, commandValue);
                    }
                    syncValue();
                };

                const makeToolbarButton = (labelText, onClick, title = '') => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'inline-flex h-8 min-w-8 items-center justify-center rounded-md border border-slate-200 px-2 text-xs font-semibold text-slate-700 hover:bg-slate-100';
                    button.textContent = labelText;
                    if (title !== '') {
                        button.title = title;
                    }

                    button.addEventListener('mousedown', (event) => {
                        event.preventDefault();
                    });

                    button.addEventListener('click', onClick);

                    return button;
                };

                toolbar.appendChild(makeToolbarButton('B', () => applyCommand('bold'), 'Grassetto'));
                toolbar.appendChild(makeToolbarButton('I', () => applyCommand('italic'), 'Corsivo'));
                toolbar.appendChild(makeToolbarButton('•', () => applyCommand('insertUnorderedList'), 'Lista puntata'));
                toolbar.appendChild(makeToolbarButton('1.', () => applyCommand('insertOrderedList'), 'Lista numerata'));
                toolbar.appendChild(makeToolbarButton('Link', () => {
                    const rawLink = window.prompt('Inserisci URL', 'https://');
                    if (!rawLink) {
                        return;
                    }

                    const link = rawLink.trim();
                    if (link === '') {
                        return;
                    }

                    applyCommand('createLink', link);
                }, 'Inserisci link'));
                toolbar.appendChild(makeToolbarButton('Tx', () => applyCommand('removeFormat'), 'Rimuovi formato'));

                editor.addEventListener('input', syncValue);
                editor.addEventListener('blur', syncValue);
                syncValue();

                const hint = document.createElement('small');
                hint.className = 'muted';
                hint.textContent = 'Supporta grassetto, corsivo, liste e link.';
                wrapper.appendChild(hint);

                return wrapper;
            }

            function openMediaPicker(context) {
                const overlay = document.createElement('div');
                overlay.className = 'fixed inset-0 z-[90] flex items-center justify-center bg-black/45 p-4';
                overlay.innerHTML = `
                    <div class="w-full max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                            <h3 class="text-base font-semibold text-slate-900">Seleziona media</h3>
                            <button type="button" class="btn btn-secondary" data-close-modal>Chiudi</button>
                        </div>
                        <div class="border-b border-slate-200 px-4 py-2">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="btn btn-primary" data-tab="library">Libreria</button>
                                <button type="button" class="btn btn-secondary" data-tab="upload">Carica nuovo</button>
                            </div>
                        </div>
                        <div class="max-h-[70vh] overflow-y-auto p-4">
                            <section data-panel="library" class="space-y-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <input type="text" class="field-input" placeholder="Cerca immagini..." data-library-search>
                                    <button type="button" class="btn btn-secondary" data-library-refresh>Aggiorna</button>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-library-grid></div>
                            </section>

                            <section data-panel="upload" class="hidden space-y-3">
                                <p class="text-sm text-slate-600">Carica una nuova immagine nel Media Manager e selezionala subito.</p>
                                <input type="file" accept="image/*" class="field-input" data-upload-file>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="btn btn-primary" data-upload-submit>Carica immagine</button>
                                    <span class="text-xs text-slate-500" data-upload-status></span>
                                </div>
                            </section>
                        </div>
                    </div>
                `;

                document.body.appendChild(overlay);

                const closeButton = overlay.querySelector('[data-close-modal]');
                const tabButtons = [...overlay.querySelectorAll('[data-tab]')];
                const libraryPanel = overlay.querySelector('[data-panel="library"]');
                const uploadPanel = overlay.querySelector('[data-panel="upload"]');
                const librarySearch = overlay.querySelector('[data-library-search]');
                const libraryRefresh = overlay.querySelector('[data-library-refresh]');
                const libraryGrid = overlay.querySelector('[data-library-grid]');
                const uploadFile = overlay.querySelector('[data-upload-file]');
                const uploadSubmit = overlay.querySelector('[data-upload-submit]');
                const uploadStatus = overlay.querySelector('[data-upload-status]');

                if (!closeButton || !libraryPanel || !uploadPanel || !librarySearch || !libraryRefresh || !libraryGrid || !uploadFile || !uploadSubmit || !uploadStatus) {
                    overlay.remove();
                    return;
                }

                const closeModal = () => {
                    document.removeEventListener('keydown', onEscape);
                    overlay.remove();
                };

                const onEscape = (event) => {
                    if (event.key === 'Escape') {
                        closeModal();
                    }
                };

                document.addEventListener('keydown', onEscape);

                overlay.addEventListener('click', (event) => {
                    if (event.target === overlay) {
                        closeModal();
                    }
                });

                closeButton.addEventListener('click', closeModal);

                function switchTab(tab) {
                    const libraryActive = tab === 'library';
                    libraryPanel.classList.toggle('hidden', !libraryActive);
                    uploadPanel.classList.toggle('hidden', libraryActive);

                    tabButtons.forEach((button) => {
                        const isActive = button.getAttribute('data-tab') === tab;
                        button.className = `btn ${isActive ? 'btn-primary' : 'btn-secondary'}`;
                    });
                }

                tabButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        switchTab(button.getAttribute('data-tab') || 'library');
                    });
                });

                function selectMedia(url) {
                    context.input.value = url;
                    context.input.dispatchEvent(new Event('input', { bubbles: true }));
                    context.updatePreview();
                    closeModal();
                }

                async function loadLibrary(search = '') {
                    libraryGrid.innerHTML = '<p class="text-sm text-slate-500">Caricamento libreria...</p>';
                    try {
                        const params = new URLSearchParams();
                        if (search.trim() !== '') {
                            params.set('search', search.trim());
                        }

                        const response = await fetch(`${mediaLibraryEndpoint}?${params.toString()}`, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        const payload = await response.json();
                        const mediaItems = Array.isArray(payload?.data) ? payload.data : [];

                        if (!response.ok) {
                            throw new Error(payload?.message || 'Errore nel caricamento libreria');
                        }

                        if (mediaItems.length === 0) {
                            libraryGrid.innerHTML = '<p class="text-sm text-slate-500">Nessuna immagine trovata.</p>';
                            return;
                        }

                        libraryGrid.innerHTML = '';
                        mediaItems.forEach((item) => {
                            const card = document.createElement('button');
                            card.type = 'button';
                            card.className = 'overflow-hidden rounded-xl border border-slate-200 bg-white text-left hover:border-slate-400';
                            card.innerHTML = `
                                <div class="h-32 w-full bg-slate-100">
                                    <img src="${item.url}" alt="${item.name}" class="h-full w-full object-cover" loading="lazy">
                                </div>
                                <div class="p-2">
                                    <p class="truncate text-xs font-medium text-slate-800">${item.name}</p>
                                    <p class="text-[11px] text-slate-500">${item.human_size ?? ''}</p>
                                </div>
                            `;
                            card.addEventListener('click', () => selectMedia(item.url));
                            libraryGrid.appendChild(card);
                        });
                    } catch (error) {
                        const message = error instanceof Error ? error.message : 'Errore nel caricamento libreria';
                        libraryGrid.innerHTML = `<p class="text-sm text-rose-600">${message}</p>`;
                    }
                }

                let searchDebounce = null;
                librarySearch.addEventListener('input', () => {
                    if (searchDebounce) {
                        clearTimeout(searchDebounce);
                    }
                    searchDebounce = setTimeout(() => {
                        loadLibrary(librarySearch.value);
                    }, 250);
                });

                libraryRefresh.addEventListener('click', () => {
                    loadLibrary(librarySearch.value);
                });

                uploadSubmit.addEventListener('click', async () => {
                    const file = uploadFile.files?.[0];
                    if (!file) {
                        uploadStatus.textContent = 'Seleziona prima un file immagine.';
                        return;
                    }

                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    formData.append('file', file);

                    uploadSubmit.disabled = true;
                    uploadStatus.textContent = 'Upload in corso...';

                    try {
                        const response = await fetch(mediaUploadEndpoint, {
                            method: 'POST',
                            body: formData,
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        const payload = await response.json();
                        if (!response.ok || !payload.url) {
                            throw new Error(payload.message || 'Upload non riuscito');
                        }

                        uploadStatus.textContent = 'Caricato con successo.';
                        selectMedia(payload.url);
                    } catch (error) {
                        const message = error instanceof Error ? error.message : 'Errore durante upload immagine';
                        uploadStatus.textContent = message;
                    } finally {
                        uploadSubmit.disabled = false;
                    }
                });

                switchTab('library');
                loadLibrary('');
            }

            function makeMediaImageField(field, inputName, value) {
                const wrapper = document.createElement('div');
                wrapper.className = 'field md:col-span-2';

                const label = document.createElement('label');
                label.className = 'field-label';
                label.textContent = field.label || 'Immagine';
                wrapper.appendChild(label);

                const input = document.createElement('input');
                input.type = 'text';
                input.name = inputName;
                input.className = 'field-input';
                input.placeholder = 'Incolla URL immagine oppure carica un file';
                input.value = typeof value === 'string' ? value : '';
                wrapper.appendChild(input);

                const controls = document.createElement('div');
                controls.className = 'flex flex-wrap items-center gap-2';
                wrapper.appendChild(controls);

                const uploadButton = makeButton('Carica');
                controls.appendChild(uploadButton);

                const clearButton = makeButton('Pulisci');
                controls.appendChild(clearButton);

                const helper = document.createElement('small');
                helper.className = 'muted';
                helper.textContent = 'Apri modale: carica nuovo o seleziona dalla libreria';
                controls.appendChild(helper);

                const previewWrap = document.createElement('div');
                previewWrap.className = 'mt-2 overflow-hidden rounded-xl border border-slate-200 bg-slate-100';
                wrapper.appendChild(previewWrap);

                const previewImage = document.createElement('img');
                previewImage.className = 'h-40 w-full object-cover';
                previewImage.alt = 'Anteprima immagine';
                previewWrap.appendChild(previewImage);

                function updatePreview() {
                    const url = String(input.value || '').trim();
                    if (url === '') {
                        previewWrap.classList.add('hidden');
                        previewImage.removeAttribute('src');
                        return;
                    }

                    previewImage.src = url;
                    previewWrap.classList.remove('hidden');
                }

                uploadButton.addEventListener('click', () => {
                    openMediaPicker({ input, updatePreview });
                });

                clearButton.addEventListener('click', () => {
                    input.value = '';
                    updatePreview();
                });

                input.addEventListener('input', updatePreview);

                updatePreview();

                return wrapper;
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

                            const rawValue = itemData[subField.name] ?? subField.default ?? '';
                            const subInputName = `${baseInputName}[${itemIndex}][${subField.name}]`;

                            if (subField.type === 'media_image') {
                                grid.appendChild(makeMediaImageField(subField, subInputName, rawValue));
                                return;
                            }

                            if (subField.type === 'textarea' || subField.type === 'wysiwyg') {
                                grid.appendChild(makeWysiwygField(subField, subInputName, rawValue));
                                return;
                            }

                            const subFieldWrapper = document.createElement('div');
                            subFieldWrapper.className = 'field';
                            grid.appendChild(subFieldWrapper);

                            const subLabel = document.createElement('label');
                            subLabel.className = 'field-label';
                            subLabel.textContent = subField.label || subField.name || 'Campo';
                            subFieldWrapper.appendChild(subLabel);

                            const subInput = createInputElement(subField, subInputName, rawValue);
                            subFieldWrapper.appendChild(subInput);

                            if (subField.type === 'list') {
                                const hint = document.createElement('small');
                                hint.className = 'muted';
                                hint.textContent = 'Inserisci un elemento per riga.';
                                subFieldWrapper.appendChild(hint);
                            }
                        });

                        removeItemButton.addEventListener('click', async () => {
                            const confirmed = await requestDeleteConfirmation({
                                title: 'Rimuovi card',
                                message: 'Vuoi rimuovere questa card dal blocco?',
                                confirmLabel: 'Rimuovi',
                            });

                            if (!confirmed) {
                                return;
                            }

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

                if (field.type === 'media_image') {
                    return makeMediaImageField(field, baseInputName, value);
                }

                if (field.type === 'textarea' || field.type === 'wysiwyg') {
                    return makeWysiwygField(field, baseInputName, value);
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

            function createBlock(type, data = {}, sortOrder = 0, collapsedInitially = false) {
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
                const collapse = document.createElement('button');
                collapse.type = 'button';
                collapse.className = 'btn btn-secondary w-10 px-0 text-base leading-none';
                collapse.textContent = '▾';
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
                            rawValue = field.default ?? (field.type === 'repeater' ? [] : '');
                        }

                        if (field.type === 'repeater') {
                            if (
                                !Array.isArray(rawValue)
                                && (!rawValue || typeof rawValue !== 'object')
                            ) {
                                rawValue = [];
                            }
                        } else if (field.type === 'list') {
                            if (Array.isArray(rawValue)) {
                                rawValue = rawValue.join('\n');
                            } else {
                                rawValue = String(rawValue);
                            }
                        } else {
                            rawValue = (rawValue && typeof rawValue === 'object')
                                ? ''
                                : String(rawValue);
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

                remove.addEventListener('click', async () => {
                    const confirmed = await requestDeleteConfirmation({
                        title: 'Rimuovi blocco',
                        message: 'Vuoi rimuovere questo blocco dalla pagina?',
                        confirmLabel: 'Rimuovi',
                    });

                    if (!confirmed) {
                        return;
                    }

                    card.remove();
                    updateSortOrders();
                });

                renderFields();
                blocksContainer.appendChild(card);
                enableDragAndDrop(card, handle);
                setCollapsed(card, collapsedInitially);
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
                createBlock(addTypeSelect.value, {}, blocksContainer.children.length, false);
            });

            if (Array.isArray(initialBlocks) && initialBlocks.length > 0) {
                initialBlocks.forEach((block, index) => {
                    createBlock(block.type, block.data || {}, block.sort_order ?? index, collapseOnInitialLoad);
                });
            } else if (defaultType) {
                createBlock(defaultType, {}, 0, false);
            }

            form.addEventListener('submit', updateSortOrders);
        })();
    </script>
@endsection
