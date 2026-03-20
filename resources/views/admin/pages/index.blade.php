@extends('layouts.admin')

@section('title', 'Admin Builder')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Flexible content</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Page Builder</h1>
                <p class="muted mt-2 max-w-2xl">Componi pagine con sezioni modulari, riordinabili e riusabili.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('admin.pages.create') }}">Nuova pagina</a>
        </div>
    </section>

    <section class="panel">
        @if ($pages->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <p class="text-sm text-slate-500">Nessuna pagina disponibile.</p>
                <a class="btn btn-primary mt-4" href="{{ route('admin.pages.create') }}">Crea la prima pagina</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="pb-3 pr-4">Titolo</th>
                            <th class="pb-3 pr-4">Slug</th>
                            <th class="pb-3 pr-4">Stato</th>
                            <th class="pb-3 pr-4">Blocchi</th>
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
                                <td class="py-4 pr-4">
                                    <span class="status-badge {{ $page->status === 'published' ? 'status-published' : 'status-draft' }}">
                                        {{ $page->status }}
                                    </span>
                                </td>
                                <td class="py-4 pr-4 text-slate-600">{{ $page->blocks_count }}</td>
                                <td class="py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a class="btn btn-secondary" href="{{ route('admin.pages.edit', $page) }}">Modifica</a>
                                        @if ($page->status === 'published')
                                            <a class="btn btn-secondary" href="{{ $page->slug === 'home' ? route('pages.home') : route('pages.show', $page->slug) }}">Apri</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
