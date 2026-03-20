@extends('layouts.app')

@section('title', $page->title)

@section('content')
    <article class="space-y-4">
        <header class="panel overflow-hidden">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Pagina</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $page->title }}</h1>
            <p class="muted mt-2">Slug: <code class="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-700">{{ $page->slug }}</code></p>
        </header>

        @forelse ($page->blocks as $block)
            @include($registry->viewFor($block->type), ['data' => $block->data ?? []])
        @empty
            <section class="panel">
                <p class="muted">Nessun blocco configurato per questa pagina.</p>
            </section>
        @endforelse
    </article>
@endsection
