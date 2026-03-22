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
        </section>

        <section class="panel space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">Media e categorie</h2>

            <div class="field">
                <label class="field-label" for="featured_image">Immagine in evidenza (URL)</label>
                <input
                    class="field-input"
                    id="featured_image"
                    name="featured_image"
                    type="text"
                    value="{{ old('featured_image', $post->featured_image) }}"
                    placeholder="/storage/media/... oppure https://..."
                >
                <small class="muted">Puoi copiare l'URL dal Media Manager.</small>
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
                <a class="btn btn-secondary" href="{{ route('articles.show', $post->slug) }}" target="_blank" rel="noopener">Apri articolo</a>
            @endif
        </div>
    </form>

    <script>
        (() => {
            const imageInput = document.getElementById('featured_image');
            const previewWrap = document.querySelector('[data-featured-preview-wrap]');
            const previewImage = document.querySelector('[data-featured-preview]');

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

            imageInput.addEventListener('input', updatePreview);
            updatePreview();
        })();
    </script>
@endsection
