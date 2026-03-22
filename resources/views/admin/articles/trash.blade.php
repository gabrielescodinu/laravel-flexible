@extends('layouts.admin')

@section('title', 'Cestino articoli')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Blog</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Cestino articoli</h1>
                <p class="muted mt-2">Gli articoli eliminati restano nel cestino finché non li ripristini o li cancelli definitivamente.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a class="btn btn-secondary" href="{{ route('admin.categories.index') }}">Categorie</a>
                <a class="btn btn-secondary" href="{{ route('admin.articles.index') }}">Torna agli articoli</a>
            </div>
        </div>
    </section>

    <section class="panel mt-4">
        @if ($posts->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <p class="text-sm text-slate-500">Il cestino degli articoli è vuoto.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="pb-3 pr-4">Titolo</th>
                            <th class="pb-3 pr-4">Slug</th>
                            <th class="pb-3 pr-4">Categorie</th>
                            <th class="pb-3 pr-4">Eliminato il</th>
                            <th class="pb-3">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($posts as $post)
                            <tr>
                                <td class="py-4 pr-4 font-medium text-slate-900">{{ $post->title }}</td>
                                <td class="py-4 pr-4">
                                    <code class="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-700">{{ $post->slug }}</code>
                                </td>
                                <td class="py-4 pr-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($post->categories as $category)
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $category->name }}</span>
                                        @empty
                                            <span class="text-xs text-slate-400">Nessuna</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-4 pr-4 text-slate-600">{{ $post->deleted_at?->format('d/m/Y H:i') ?: '-' }}</td>
                                <td class="py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <form
                                            action="{{ route('admin.articles.restore', $post->id) }}"
                                            method="POST"
                                            data-confirm-title="Ripristina articolo"
                                            data-confirm-message="Vuoi ripristinare questo articolo dal cestino?"
                                            data-confirm-label="Ripristina"
                                            data-confirm-variant="secondary"
                                        >
                                            @csrf
                                            <button class="btn btn-secondary" type="submit">Ripristina</button>
                                        </form>

                                        <form
                                            action="{{ route('admin.articles.force-delete', $post->id) }}"
                                            method="POST"
                                            data-confirm-title="Elimina definitivamente"
                                            data-confirm-message="Questo articolo sarà rimosso per sempre. Continuare?"
                                            data-confirm-label="Elimina definitivamente"
                                            data-confirm-variant="danger"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger" type="submit">Elimina definitivamente</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $posts->links() }}
            </div>
        @endif
    </section>
@endsection
