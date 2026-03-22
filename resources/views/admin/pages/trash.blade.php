@extends('layouts.admin')

@section('title', 'Cestino pagine')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Flexible content</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Cestino pagine</h1>
                <p class="muted mt-2 max-w-2xl">Le pagine eliminate restano qui finché non le ripristini o le rimuovi definitivamente.</p>
            </div>
            <a class="btn btn-secondary" href="{{ route('admin.pages.index') }}">Torna alle pagine</a>
        </div>
    </section>

    <section class="panel mt-4">
        @if ($pages->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <p class="text-sm text-slate-500">Il cestino delle pagine è vuoto.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="pb-3 pr-4">Titolo</th>
                            <th class="pb-3 pr-4">Slug</th>
                            <th class="pb-3 pr-4">Blocchi</th>
                            <th class="pb-3 pr-4">Eliminata il</th>
                            <th class="pb-3">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($pages as $page)
                            <tr>
                                <td class="py-4 pr-4 font-medium text-slate-900">{{ $page->title }}</td>
                                <td class="py-4 pr-4">
                                    <code class="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-700">{{ $page->slug }}</code>
                                </td>
                                <td class="py-4 pr-4 text-slate-600">{{ $page->blocks_count }}</td>
                                <td class="py-4 pr-4 text-slate-600">{{ $page->deleted_at?->format('d/m/Y H:i') ?: '-' }}</td>
                                <td class="py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <form
                                            action="{{ route('admin.pages.restore', $page->id) }}"
                                            method="POST"
                                            data-confirm-title="Ripristina pagina"
                                            data-confirm-message="Vuoi ripristinare questa pagina dal cestino?"
                                            data-confirm-label="Ripristina"
                                            data-confirm-variant="secondary"
                                        >
                                            @csrf
                                            <button class="btn btn-secondary" type="submit">Ripristina</button>
                                        </form>

                                        <form
                                            action="{{ route('admin.pages.force-delete', $page->id) }}"
                                            method="POST"
                                            data-confirm-title="Elimina definitivamente"
                                            data-confirm-message="Questa pagina sarà rimossa per sempre. Continuare?"
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
                {{ $pages->links() }}
            </div>
        @endif
    </section>
@endsection
