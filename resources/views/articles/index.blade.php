@extends('layouts.app')

@section('title', $selectedCategory ? 'Blog - '.$selectedCategory->name : 'Blog')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Magazine</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">
                    {{ $selectedCategory ? 'Categoria: '.$selectedCategory->name : 'Ultimi articoli' }}
                </h1>
                <p class="muted mt-2">Notizie, approfondimenti e contenuti editoriali.</p>
            </div>

            @if ($selectedCategory)
                <a href="{{ route('articles.index') }}" class="btn btn-secondary">Vedi tutti</a>
            @endif
        </div>

        @if ($categories->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-2">
                <a
                    href="{{ route('articles.index') }}"
                    class="btn {{ $selectedCategory ? 'btn-secondary' : 'btn-primary' }}"
                >
                    Tutte
                </a>
                @foreach ($categories as $category)
                    <a
                        href="{{ route('articles.category', $category->slug) }}"
                        class="btn {{ $selectedCategory?->id === $category->id ? 'btn-primary' : 'btn-secondary' }}"
                    >
                        {{ $category->name }} ({{ $category->published_posts_count ?? 0 }})
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($posts as $post)
            <article class="panel p-0 overflow-hidden">
                @if ($post->featured_image)
                    <a href="{{ $post->public_url }}" class="block h-44 bg-slate-100">
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="h-full w-full object-cover">
                    </a>
                @endif

                <div class="space-y-3 p-5">
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($post->categories as $category)
                            <a href="{{ route('articles.category', $category->slug) }}" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>

                    <h2 class="text-xl font-semibold text-slate-900">
                        <a href="{{ $post->public_url }}" class="hover:text-slate-600">{{ $post->title }}</a>
                    </h2>

                    <p class="muted line-clamp-3">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->content), 130) }}</p>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">{{ $post->published_at?->format('d/m/Y') ?: '-' }}</span>
                        <a href="{{ $post->public_url }}" class="btn btn-secondary">Leggi</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="panel md:col-span-2 xl:col-span-3">
                <p class="muted">Nessun articolo pubblicato.</p>
            </div>
        @endforelse
    </section>

    @if (method_exists($posts, 'links'))
        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
