@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <article class="space-y-4">
        <header class="panel overflow-hidden">
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('articles.index') }}" class="hover:text-slate-600">Blog</a>
                <span>•</span>
                <span>{{ $post->published_at?->format('d/m/Y H:i') ?: '-' }}</span>
            </div>

            <h1 class="mt-3 text-3xl font-bold text-slate-900">{{ $post->title }}</h1>

            @if ($post->categories->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach ($post->categories as $category)
                        <a href="{{ route('articles.category', $category->slug) }}" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($post->excerpt)
                <p class="mt-4 text-slate-600">{{ $post->excerpt }}</p>
            @endif
        </header>

        @if ($post->featured_image)
            <section class="panel p-0 overflow-hidden">
                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="max-h-[520px] w-full object-cover">
            </section>
        @endif

        <section class="content-block prose prose-slate max-w-none">
            {!! nl2br(e((string) $post->content)) !!}
        </section>

        @if ($relatedPosts->isNotEmpty())
            <section class="panel">
                <h2 class="text-xl font-semibold text-slate-900">Articoli correlati</h2>
                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    @foreach ($relatedPosts as $relatedPost)
                        <a href="{{ route('articles.show', $relatedPost->slug) }}" class="rounded-xl border border-slate-200 bg-white p-3 hover:border-slate-300">
                            <p class="text-sm font-semibold text-slate-900">{{ $relatedPost->title }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $relatedPost->published_at?->format('d/m/Y') ?: '-' }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection
