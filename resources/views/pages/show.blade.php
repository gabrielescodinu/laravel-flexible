@extends('layouts.app')

@section('title', $page->title)

@section('content')
    <article class="space-y-4">
        @forelse ($page->blocks as $block)
            @include($registry->viewFor($block->type), ['data' => $block->data ?? []])
        @empty
            <section class="panel">
                <p class="muted">Nessun blocco configurato per questa pagina.</p>
            </section>
        @endforelse
    </article>
@endsection
