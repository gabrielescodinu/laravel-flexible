@extends('layouts.admin')

@section('title', 'Media Manager')

@section('content')
    <section class="panel space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Libreria media</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Media Manager</h1>
                <p class="muted mt-2">
                    Carica immagini e documenti in stile media library.
                </p>
            </div>
        </div>

        <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <label class="block cursor-pointer rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center hover:border-slate-400">
                <span class="text-sm font-semibold text-slate-700">Trascina qui i file oppure clicca per selezionarli</span>
                <span class="mt-1 block text-xs text-slate-500">Supporto: immagini, PDF, Office, CSV, TXT, ZIP (max 50MB per file)</span>
                <input class="hidden" type="file" name="files[]" multiple required>
            </label>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary">Carica file</button>
                <p class="text-xs text-slate-500">
                    Se gli URL non aprono i file, esegui <code>php artisan storage:link</code>.
                </p>
            </div>
        </form>
    </section>

    <section class="panel space-y-4 mt-4">
        <form method="GET" action="{{ route('admin.media.index') }}" class="grid gap-3 md:grid-cols-[1fr_auto_auto_auto]">
            <div class="field">
                <label class="field-label" for="search">Cerca file</label>
                <input id="search" class="field-input" type="text" name="search" value="{{ $search }}" placeholder="Nome file, titolo, mime...">
            </div>

            <button class="btn {{ $type === 'all' ? 'btn-primary' : 'btn-secondary' }}" type="submit" name="type" value="all">
                Tutti ({{ $stats['all'] }})
            </button>
            <button class="btn {{ $type === 'image' ? 'btn-primary' : 'btn-secondary' }}" type="submit" name="type" value="image">
                Immagini ({{ $stats['images'] }})
            </button>
            <button class="btn {{ $type === 'document' ? 'btn-primary' : 'btn-secondary' }}" type="submit" name="type" value="document">
                Documenti ({{ $stats['documents'] }})
            </button>
        </form>

        @if ($media->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <p class="text-sm text-slate-500">Nessun file trovato.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($media as $item)
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div class="relative h-40 bg-slate-100">
                            @if ($item->isImage())
                                <img src="{{ $item->url }}" alt="{{ $item->alt_text ?: $item->original_name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <span class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600">
                                        {{ strtoupper($item->extension ?: 'FILE') }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-3 p-4">
                            <div>
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $item->original_name }}</p>
                                <p class="text-xs text-slate-500">{{ $item->mime_type }} • {{ $item->human_size }}</p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <a href="{{ $item->url }}" target="_blank" rel="noopener" class="btn btn-secondary">Apri</a>
                                <button type="button" class="btn btn-secondary" data-copy-url="{{ $item->url }}">Copia URL</button>
                                <form action="{{ route('admin.media.destroy', $item) }}" method="POST" onsubmit="return confirm('Eliminare questo file?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Elimina</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div>
                {{ $media->links() }}
            </div>
        @endif
    </section>

    <script>
        (() => {
            document.querySelectorAll('[data-copy-url]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const url = button.getAttribute('data-copy-url') || '';
                    if (!url) return;

                    try {
                        await navigator.clipboard.writeText(url);
                        button.textContent = 'Copiato';
                        setTimeout(() => {
                            button.textContent = 'Copia URL';
                        }, 1200);
                    } catch {
                        window.prompt('Copia questo URL:', url);
                    }
                });
            });
        })();
    </script>
@endsection
