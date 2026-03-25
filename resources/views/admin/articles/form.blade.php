@extends('layouts.admin')

@section('title', $post->exists ? 'Modifica articolo' : 'Nuovo articolo')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Blog editor</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $post->exists ? 'Modifica articolo' : 'Nuovo articolo' }}</h1>
                <p class="muted mt-2">Titolo, contenuto, immagine e categorie in stile WordPress.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="btn btn-secondary" data-open-seo-modal>SEO</button>
                <a class="btn btn-secondary" href="{{ route('admin.categories.index') }}">Gestisci categorie</a>
                <a class="btn btn-secondary" href="{{ route('admin.articles.index') }}">Torna alla lista</a>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ $formAction }}" class="mt-4 space-y-4">
        @csrf
        @if ($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <section class="panel space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="field">
                    <label class="field-label" for="title">Titolo</label>
                    <input class="field-input" id="title" name="title" type="text" value="{{ old('title', $post->title) }}" required>
                </div>

                <div class="field">
                    <label class="field-label" for="slug">Slug</label>
                    <input class="field-input" id="slug" name="slug" type="text" value="{{ old('slug', $post->slug) }}" placeholder="titolo-articolo" required>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="field">
                    <label class="field-label" for="status">Stato</label>
                    @php($currentStatus = old('status', $post->status ?: 'draft'))
                    <select class="field-input" id="status" name="status" required>
                        <option value="draft" @selected($currentStatus === 'draft')>draft</option>
                        <option value="published" @selected($currentStatus === 'published')>published</option>
                    </select>
                </div>

                <div class="field">
                    <label class="field-label" for="published_at">Data pubblicazione</label>
                    <input
                        class="field-input"
                        id="published_at"
                        name="published_at"
                        type="datetime-local"
                        value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
                    >
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="excerpt">Estratto</label>
                <textarea class="field-input min-h-24" id="excerpt" name="excerpt" placeholder="Breve riassunto dell'articolo...">{{ old('excerpt', $post->excerpt) }}</textarea>
            </div>

            <div class="field">
                <label class="field-label" for="content">Contenuto</label>
                <textarea class="field-input min-h-64" id="content" name="content" placeholder="Scrivi qui il contenuto completo...">{{ old('content', $post->content) }}</textarea>
            </div>

            <div class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">SEO</h2>
                    <p class="muted mt-1 text-sm">Apri la modale per configurare title, description e robots.</p>
                </div>
                <button type="button" class="btn btn-secondary" data-open-seo-modal>Apri SEO</button>
            </div>
        </section>

        <div
            class="fixed inset-0 z-[85] hidden items-center justify-center bg-black/45 p-4"
            data-seo-modal
            aria-hidden="true"
        >
            <div class="w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">SEO</h2>
                        <p class="muted mt-1 text-xs">Campi opzionali per snippet e indicizzazione.</p>
                    </div>
                    <button type="button" class="btn btn-secondary" data-close-seo-modal>Chiudi</button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto p-4 space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="field">
                            <label class="field-label" for="seo_title">SEO title</label>
                            <input class="field-input" id="seo_title" name="seo_title" type="text" maxlength="255" value="{{ old('seo_title', $post->seo_title) }}" placeholder="Titolo SEO personalizzato">
                        </div>

                        <div class="field">
                            <label class="field-label" for="seo_robots">Robots</label>
                            @php($currentSeoRobots = old('seo_robots', $post->seo_robots ?: 'index,follow'))
                            <select class="field-input" id="seo_robots" name="seo_robots">
                                <option value="index,follow" @selected($currentSeoRobots === 'index,follow')>index,follow</option>
                                <option value="index,nofollow" @selected($currentSeoRobots === 'index,nofollow')>index,nofollow</option>
                                <option value="noindex,follow" @selected($currentSeoRobots === 'noindex,follow')>noindex,follow</option>
                                <option value="noindex,nofollow" @selected($currentSeoRobots === 'noindex,nofollow')>noindex,nofollow</option>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label class="field-label" for="seo_description">Meta description</label>
                        <textarea class="field-input min-h-24" id="seo_description" name="seo_description" maxlength="500" placeholder="Breve descrizione (consigliato 140-160 caratteri)">{{ old('seo_description', $post->seo_description) }}</textarea>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="field">
                            <label class="field-label" for="seo_keywords">Meta keywords</label>
                            <input class="field-input" id="seo_keywords" name="seo_keywords" type="text" maxlength="500" value="{{ old('seo_keywords', $post->seo_keywords) }}" placeholder="es. notizie, sviluppo, laravel">
                        </div>

                        <div class="field">
                            <label class="field-label" for="seo_canonical_url">Canonical URL</label>
                            <input class="field-input" id="seo_canonical_url" name="seo_canonical_url" type="text" maxlength="2048" value="{{ old('seo_canonical_url', $post->seo_canonical_url) }}" placeholder="https://example.com/blog/categoria/articolo">
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 px-4 py-3">
                    <div class="flex justify-end">
                        <button type="button" class="btn btn-secondary" data-close-seo-modal>Chiudi</button>
                    </div>
                </div>
            </div>
        </div>

        <section class="panel space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">Media e categorie</h2>

            <div class="field">
                <label class="field-label" for="featured_image">Immagine in evidenza</label>
                <input
                    class="field-input"
                    id="featured_image"
                    name="featured_image"
                    type="text"
                    value="{{ old('featured_image', $post->featured_image) }}"
                    placeholder="/storage/media/... oppure https://..."
                >
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <button type="button" class="btn btn-secondary" data-open-featured-picker>Media Manager</button>
                    <button type="button" class="btn btn-secondary" data-clear-featured-image>Pulisci</button>
                    <small class="muted">Carica nuovo o seleziona dalla libreria media.</small>
                </div>
            </div>

            <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-slate-100" data-featured-preview-wrap>
                <img class="h-52 w-full object-cover" src="" alt="Anteprima immagine in evidenza" data-featured-preview>
            </div>

            @php($resolvedCategoryIds = collect(old('category_ids', $selectedCategoryIds ?? []))->map(static fn ($id) => (int) $id)->all())

            <div class="field">
                <label class="field-label">Categorie</label>
                @if ($categories->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Nessuna categoria disponibile.</p>
                        <a class="btn btn-secondary mt-3" href="{{ route('admin.categories.index') }}">Crea categoria</a>
                    </div>
                @else
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($categories as $category)
                            <label class="flex items-start gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                <input
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300"
                                    type="checkbox"
                                    name="category_ids[]"
                                    value="{{ $category->id }}"
                                    @checked(in_array((int) $category->id, $resolvedCategoryIds, true))
                                >
                                <span>
                                    <span class="font-medium text-slate-800">{{ $category->name }}</span>
                                    <span class="block text-xs text-slate-400">{{ $category->slug }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
            @if ($post->exists && $post->status === 'published')
                <a class="btn btn-secondary" href="{{ $post->public_url }}" target="_blank" rel="noopener">Apri articolo</a>
            @endif
        </div>
    </form>

    <script>
        (() => {
            const seoModal = document.querySelector('[data-seo-modal]');
            const openSeoButtons = [...document.querySelectorAll('[data-open-seo-modal]')];
            const closeSeoButtons = [...document.querySelectorAll('[data-close-seo-modal]')];

            if (!seoModal || openSeoButtons.length === 0) {
                return;
            }

            const openSeoModal = () => {
                seoModal.classList.remove('hidden');
                seoModal.classList.add('flex');
                seoModal.setAttribute('aria-hidden', 'false');
            };

            const closeSeoModal = () => {
                seoModal.classList.add('hidden');
                seoModal.classList.remove('flex');
                seoModal.setAttribute('aria-hidden', 'true');
            };

            openSeoButtons.forEach((button) => {
                button.addEventListener('click', openSeoModal);
            });

            closeSeoButtons.forEach((button) => {
                button.addEventListener('click', closeSeoModal);
            });

            seoModal.addEventListener('click', (event) => {
                if (event.target === seoModal) {
                    closeSeoModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && seoModal.getAttribute('aria-hidden') === 'false') {
                    closeSeoModal();
                }
            });
        })();
    </script>

    <script>
        (() => {
            const form = document.querySelector('form');
            const imageInput = document.getElementById('featured_image');
            const openPickerButton = document.querySelector('[data-open-featured-picker]');
            const clearButton = document.querySelector('[data-clear-featured-image]');
            const previewWrap = document.querySelector('[data-featured-preview-wrap]');
            const previewImage = document.querySelector('[data-featured-preview]');
            const csrfToken = form?.querySelector('input[name="_token"]')?.value || '';
            const mediaUploadEndpoint = @json(route('admin.media.upload'));
            const mediaLibraryEndpoint = @json(route('admin.media.library'));

            if (!imageInput || !previewWrap || !previewImage) {
                return;
            }

            function updatePreview() {
                const value = String(imageInput.value || '').trim();
                if (value === '') {
                    previewWrap.classList.add('hidden');
                    previewImage.removeAttribute('src');
                    return;
                }

                previewImage.src = value;
                previewWrap.classList.remove('hidden');
            }

            function openMediaPicker() {
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
                    imageInput.value = url;
                    imageInput.dispatchEvent(new Event('input', { bubbles: true }));
                    updatePreview();
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

            imageInput.addEventListener('input', updatePreview);
            openPickerButton?.addEventListener('click', openMediaPicker);
            clearButton?.addEventListener('click', () => {
                imageInput.value = '';
                updatePreview();
            });
            updatePreview();
        })();
    </script>
@endsection
