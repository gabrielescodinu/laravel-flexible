<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin').' | '.config('app.name')</title>
    @include('layouts.partials.assets')
</head>
<body class="h-screen w-screen overflow-hidden bg-slate-100 text-slate-800">
    @php
        $totalPages = 0;
        $draftPages = 0;
        $publishedPages = 0;
        $totalPosts = 0;
        $draftPosts = 0;
        $publishedPosts = 0;
        $totalCategories = 0;
        $totalMedia = 0;
        $imageMedia = 0;
        $documentMedia = 0;

        if (\Illuminate\Support\Facades\Schema::hasTable('pages')) {
            $totalPages = \App\Models\Page::query()->count();
            $draftPages = \App\Models\Page::query()->where('status', 'draft')->count();
            $publishedPages = \App\Models\Page::query()->where('status', 'published')->count();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('media')) {
            $totalMedia = \App\Models\Media::query()->count();
            $imageMedia = \App\Models\Media::query()->where('mime_type', 'like', 'image/%')->count();
            $documentMedia = \App\Models\Media::query()->where('mime_type', 'not like', 'image/%')->count();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('posts')) {
            $totalPosts = \App\Models\Post::query()->count();
            $draftPosts = \App\Models\Post::query()->where('status', 'draft')->count();
            $publishedPosts = \App\Models\Post::query()->where('status', 'published')->count();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
            $totalCategories = \App\Models\Category::query()->count();
        }
    @endphp

    <main class="h-full w-full">
        <div class="grid h-full w-full grid-rows-[64px_190px_minmax(0,1fr)] md:grid-rows-1 md:grid-cols-[72px_300px_minmax(0,1fr)]">
            <aside class="flex items-center justify-between border-b border-zinc-800 bg-zinc-950 px-4 text-white md:hidden">
                <a
                    href="{{ route('admin.pages.index') }}"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white/10"
                    aria-label="Admin Home"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current">
                        <path d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm7 2h9v4h-9v-4Z"/>
                    </svg>
                </a>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.pages.index') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/15">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3.5" y="5.5" width="17" height="13" rx="2.5"></rect>
                            <path d="M7 10h10M7 14h6"></path>
                        </svg>
                    </a>
                    <a href="{{ route('admin.pages.create') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>
                    </a>
                    <a href="{{ route('admin.articles.index') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M6 5.5h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2Z"></path>
                            <path d="M8 9.5h8M8 12.5h8M8 15.5h5"></path>
                        </svg>
                    </a>
                    <a href="{{ route('admin.media.index') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3.5" y="5.5" width="17" height="13" rx="2.5"></rect>
                            <path d="M8 9.5h8M8 12h8M8 14.5h5"></path>
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M15 16.5 19.5 12 15 7.5"></path>
                                <path d="M19 12H9"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </aside>

            <aside class="border-b border-slate-200 bg-slate-100 px-4 py-4 md:hidden">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Contenuti</p>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    <a
                        href="{{ route('admin.pages.index') }}"
                        class="flex items-center justify-between rounded-lg px-3 py-2 {{ request()->routeIs('admin.pages.index') ? 'bg-slate-200 font-semibold text-slate-900' : 'bg-white text-slate-700' }}"
                    >
                        <span>Pagine</span>
                        <span class="text-slate-400">{{ $totalPages }}</span>
                    </a>
                    <a
                        href="{{ route('admin.pages.create') }}"
                        class="flex items-center justify-between rounded-lg px-3 py-2 {{ request()->routeIs('admin.pages.create') ? 'bg-slate-200 font-semibold text-slate-900' : 'bg-white text-slate-700' }}"
                    >
                        <span>Nuova</span>
                        <span class="text-slate-400">+</span>
                    </a>
                    <a
                        href="{{ route('admin.articles.index') }}"
                        class="flex items-center justify-between rounded-lg px-3 py-2 {{ request()->routeIs('admin.articles.*') ? 'bg-slate-200 font-semibold text-slate-900' : 'bg-white text-slate-700' }}"
                    >
                        <span>Articoli</span>
                        <span class="text-slate-400">{{ $totalPosts }}</span>
                    </a>
                    <a
                        href="{{ route('admin.categories.index') }}"
                        class="flex items-center justify-between rounded-lg px-3 py-2 {{ request()->routeIs('admin.categories.*') ? 'bg-slate-200 font-semibold text-slate-900' : 'bg-white text-slate-700' }}"
                    >
                        <span>Categorie</span>
                        <span class="text-slate-400">{{ $totalCategories }}</span>
                    </a>
                    <a
                        href="{{ route('admin.media.index') }}"
                        class="flex items-center justify-between rounded-lg px-3 py-2 {{ request()->routeIs('admin.media.*') ? 'bg-slate-200 font-semibold text-slate-900' : 'bg-white text-slate-700' }}"
                    >
                        <span>Media</span>
                        <span class="text-slate-400">{{ $totalMedia }}</span>
                    </a>
                </div>
            </aside>

            <aside class="hidden h-full flex-col items-center border-r border-zinc-800 bg-zinc-950 py-4 text-white md:flex">
                <a
                    href="{{ route('admin.pages.index') }}"
                    class="mb-6 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white/10"
                    aria-label="Admin Home"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current">
                        <path d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm7 2h9v4h-9v-4Z"/>
                    </svg>
                </a>

                <nav class="flex flex-1 flex-col items-center gap-2">
                    <a
                        href="{{ route('admin.pages.index') }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg transition {{ request()->routeIs('admin.pages.index') ? 'bg-white/20' : 'hover:bg-white/10' }}"
                        aria-label="Tutte le pagine"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3.5" y="5.5" width="17" height="13" rx="2.5"></rect>
                            <path d="M7 10h10M7 14h6"></path>
                        </svg>
                    </a>

                    <a
                        href="{{ route('admin.pages.create') }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg transition {{ request()->routeIs('admin.pages.create') ? 'bg-white/20' : 'hover:bg-white/10' }}"
                        aria-label="Nuova pagina"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>
                    </a>

                    <a
                        href="{{ route('admin.articles.index') }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg transition {{ request()->routeIs('admin.articles.*') ? 'bg-white/20' : 'hover:bg-white/10' }}"
                        aria-label="Articoli"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M6 5.5h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2Z"></path>
                            <path d="M8 9.5h8M8 12.5h8M8 15.5h5"></path>
                        </svg>
                    </a>

                    <a
                        href="{{ route('admin.media.index') }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg transition {{ request()->routeIs('admin.media.*') ? 'bg-white/20' : 'hover:bg-white/10' }}"
                        aria-label="Media manager"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3.5" y="5.5" width="17" height="13" rx="2.5"></rect>
                            <path d="M8 9.5h8M8 12h8M8 14.5h5"></path>
                        </svg>
                    </a>

                    <a
                        href="{{ route('pages.home') }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg transition hover:bg-white/10"
                        aria-label="Vai al sito"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M3 11.5 12 4l9 7.5"></path>
                            <path d="M5.5 10.5V20h13V10.5"></path>
                        </svg>
                    </a>
                </nav>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg transition hover:bg-white/10"
                        aria-label="Logout"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M15 16.5 19.5 12 15 7.5"></path>
                            <path d="M19 12H9"></path>
                            <path d="M10 4.5H6A2.5 2.5 0 0 0 3.5 7v10A2.5 2.5 0 0 0 6 19.5h4"></path>
                        </svg>
                    </button>
                </form>
            </aside>

            <aside class="hidden h-full overflow-y-auto border-r border-slate-200 bg-slate-100 px-4 py-6 md:block">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-400">Contenuti</p>
                <h2 class="mt-2 text-[1.65rem] font-semibold leading-none text-slate-900">Dashboard</h2>

                <p class="mt-6 text-sm font-semibold text-slate-400">Pagine</p>

                <div class="mt-3 space-y-1.5">
                    <a
                        href="{{ route('admin.pages.index') }}"
                        class="flex items-center justify-between rounded-xl px-3 py-2.5 text-[14px] font-medium text-slate-800 transition {{ request()->routeIs('admin.pages.index') ? 'bg-slate-200/85' : 'hover:bg-slate-200/60' }}"
                    >
                        <span class="inline-flex items-center gap-2">
                            <span class="inline-block h-2 w-2 rounded-full bg-slate-400"></span>
                            Tutte
                        </span>
                        <span class="text-slate-400">{{ $totalPages }}</span>
                    </a>

                    <a
                        href="{{ route('admin.pages.create') }}"
                        class="flex items-center justify-between rounded-xl px-3 py-2.5 text-[14px] font-medium text-slate-800 transition {{ request()->routeIs('admin.pages.create') ? 'bg-slate-200/85' : 'hover:bg-slate-200/60' }}"
                    >
                        <span class="inline-flex items-center gap-2">
                            <span class="inline-block h-2 w-2 rounded-full bg-slate-400"></span>
                            Nuova pagina
                        </span>
                        <span class="text-slate-400">+</span>
                    </a>
                </div>

                <div class="my-6 border-t border-slate-200"></div>

                <p class="text-sm font-semibold text-slate-400">Pages Status</p>
                <div class="mt-3 space-y-1.5">
                    <div class="flex items-center justify-between px-2.5 py-2 text-[14px] text-slate-800">
                        <span>Published</span>
                        <span class="text-slate-400">{{ $publishedPages }}</span>
                    </div>
                    <div class="flex items-center justify-between px-2.5 py-2 text-[14px] text-slate-800">
                        <span>Draft</span>
                        <span class="text-slate-400">{{ $draftPages }}</span>
                    </div>
                </div>

                <div class="my-6 border-t border-slate-200"></div>

                <p class="text-sm font-semibold text-slate-400">Articoli</p>
                <div class="mt-3 space-y-1.5">
                    <a
                        href="{{ route('admin.articles.index') }}"
                        class="flex items-center justify-between rounded-xl px-3 py-2.5 text-[14px] font-medium text-slate-800 transition {{ request()->routeIs('admin.articles.index', 'admin.articles.edit') ? 'bg-slate-200/85' : 'hover:bg-slate-200/60' }}"
                    >
                        <span>Tutti gli articoli</span>
                        <span class="text-slate-400">{{ $totalPosts }}</span>
                    </a>
                    <a
                        href="{{ route('admin.articles.create') }}"
                        class="flex items-center justify-between rounded-xl px-3 py-2.5 text-[14px] font-medium text-slate-800 transition {{ request()->routeIs('admin.articles.create') ? 'bg-slate-200/85' : 'hover:bg-slate-200/60' }}"
                    >
                        <span>Nuovo articolo</span>
                        <span class="text-slate-400">+</span>
                    </a>
                    <a
                        href="{{ route('admin.categories.index') }}"
                        class="flex items-center justify-between rounded-xl px-3 py-2.5 text-[14px] font-medium text-slate-800 transition {{ request()->routeIs('admin.categories.*') ? 'bg-slate-200/85' : 'hover:bg-slate-200/60' }}"
                    >
                        <span>Categorie</span>
                        <span class="text-slate-400">{{ $totalCategories }}</span>
                    </a>
                </div>
                <div class="mt-3 space-y-1.5">
                    <div class="flex items-center justify-between px-2.5 py-2 text-[14px] text-slate-800">
                        <span>Published</span>
                        <span class="text-slate-400">{{ $publishedPosts }}</span>
                    </div>
                    <div class="flex items-center justify-between px-2.5 py-2 text-[14px] text-slate-800">
                        <span>Draft</span>
                        <span class="text-slate-400">{{ $draftPosts }}</span>
                    </div>
                </div>

                <div class="my-6 border-t border-slate-200"></div>

                <p class="text-sm font-semibold text-slate-400">Media</p>
                <div class="mt-3 space-y-1.5">
                    <a
                        href="{{ route('admin.media.index') }}"
                        class="flex items-center justify-between rounded-xl px-3 py-2.5 text-[14px] font-medium text-slate-800 transition {{ request()->routeIs('admin.media.*') ? 'bg-slate-200/85' : 'hover:bg-slate-200/60' }}"
                    >
                        <span>Library</span>
                        <span class="text-slate-400">{{ $totalMedia }}</span>
                    </a>
                    <div class="flex items-center justify-between px-2.5 py-2 text-[14px] text-slate-800">
                        <span>Images</span>
                        <span class="text-slate-400">{{ $imageMedia }}</span>
                    </div>
                    <div class="flex items-center justify-between px-2.5 py-2 text-[14px] text-slate-800">
                        <span>Documents</span>
                        <span class="text-slate-400">{{ $documentMedia }}</span>
                    </div>
                </div>

                <div class="my-6 border-t border-slate-200"></div>

                <p class="text-sm font-semibold text-slate-400">Admin</p>
                <div class="mt-3 space-y-1.5 text-[16px]">
                    <div class="px-2.5 py-2 text-slate-700">{{ auth()->user()?->name }}</div>
                    <div class="px-2.5 py-2 text-sm text-slate-500">{{ auth()->user()?->email }}</div>
                </div>
            </aside>

            <section class="row-start-3 h-full overflow-y-auto bg-white md:row-start-1 md:col-start-3">
                @if (session('success'))
                    <div class="alert alert-success mx-4 mt-4 mb-0 md:mx-6 md:mt-6">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error mx-4 mt-4 mb-0 md:mx-6 md:mt-6">
                        <p class="font-semibold">Controlla i campi:</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="p-3 md:p-4">
                    @yield('content')
                </div>
            </section>
        </div>
    </main>
</body>
</html>
