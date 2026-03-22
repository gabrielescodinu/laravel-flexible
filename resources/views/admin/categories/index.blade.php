@extends('layouts.admin')

@section('title', 'Categorie')

@section('content')
    <section class="panel space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Blog taxonomy</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Categorie articoli</h1>
                <p class="muted mt-2">Crea e modifica categorie da assegnare ai post, come WordPress.</p>
            </div>
            <a class="btn btn-secondary" href="{{ route('admin.articles.index') }}">Torna agli articoli</a>
        </div>

        <form action="{{ route('admin.categories.store') }}" method="POST" class="grid gap-3 md:grid-cols-[1fr_1fr_1fr_auto]">
            @csrf
            <input class="field-input" type="text" name="name" placeholder="Nome categoria" required>
            <input class="field-input" type="text" name="slug" placeholder="Slug (opzionale)">
            <input class="field-input" type="text" name="description" placeholder="Descrizione (opzionale)">
            <button class="btn btn-primary" type="submit">Aggiungi</button>
        </form>
    </section>

    <section class="panel mt-4 space-y-3">
        @if ($categories->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <p class="text-sm text-slate-500">Nessuna categoria disponibile.</p>
            </div>
        @else
            @foreach ($categories as $category)
                <article class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $category->name }}</p>
                            <p class="text-xs text-slate-500">{{ $category->posts_count }} articoli assegnati</p>
                        </div>

                        <form
                            action="{{ route('admin.categories.destroy', $category) }}"
                            method="POST"
                            data-confirm-title="Elimina categoria"
                            data-confirm-message="Vuoi eliminare questa categoria? Questa azione non puo essere annullata."
                            data-confirm-label="Elimina"
                            data-confirm-variant="danger"
                        >
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit">Elimina</button>
                        </form>
                    </div>

                    <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="grid gap-3 md:grid-cols-[1fr_1fr_1fr_auto]">
                        @csrf
                        @method('PUT')
                        <input class="field-input" type="text" name="name" value="{{ $category->name }}" required>
                        <input class="field-input" type="text" name="slug" value="{{ $category->slug }}">
                        <input class="field-input" type="text" name="description" value="{{ $category->description }}">
                        <button class="btn btn-secondary" type="submit">Salva</button>
                    </form>
                </article>
            @endforeach
        @endif
    </section>
@endsection
