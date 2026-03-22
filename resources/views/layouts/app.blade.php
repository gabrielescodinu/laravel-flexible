<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @include('layouts.partials.assets')
</head>
<body>
    @php
        $menu = $visitorMenu ?? [
            'name' => 'Menu principale',
            'logo_url' => null,
            'logo_alt' => config('app.name').' logo',
            'show_search' => true,
            'items' => [],
        ];

        $menuItems = is_array($menu['items'] ?? null) ? $menu['items'] : [];
        $showSearch = (bool) ($menu['show_search'] ?? true);
        $logoUrl = $menu['logo_url'] ?? null;
        $logoAlt = $menu['logo_alt'] ?? (config('app.name').' logo');
    @endphp

    @auth
        @php
            $editLink = null;
            $editLabel = null;

            if (isset($page) && $page instanceof \App\Models\Page) {
                $editLink = route('admin.pages.edit', $page);
                $editLabel = 'Modifica pagina';
            } elseif (isset($post) && $post instanceof \App\Models\Post) {
                $editLink = route('admin.articles.edit', $post);
                $editLabel = 'Modifica articolo';
            }
        @endphp

        <div class="border-b border-slate-700 bg-slate-900 text-slate-200">
            <div class="mx-auto flex w-full max-w-screen-xl flex-wrap items-center justify-between gap-2 px-4 py-2 text-xs md:px-8">
                <div class="flex flex-wrap items-center gap-1.5">
                    <a href="{{ route('admin.pages.index') }}" class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 font-medium hover:bg-slate-800">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h14M10 3l7 7-7 7"></path>
                        </svg>
                        Dashboard admin
                    </a>

                    @if ($editLink && $editLabel)
                        <a href="{{ $editLink }}" class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 font-medium text-emerald-200 hover:bg-slate-800">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.7 3.3 2 2a1 1 0 0 1 0 1.4l-8.9 8.9-3.8.9.9-3.8 8.9-8.9a1 1 0 0 1 1.4 0Z"></path>
                            </svg>
                            {{ $editLabel }}
                        </a>
                    @endif
                </div>

                <div class="relative" data-user-dropdown>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 font-medium hover:bg-slate-800"
                        data-user-dropdown-toggle
                        aria-expanded="false"
                    >
                        <span>{{ auth()->user()?->name }}</span>
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4"></path>
                        </svg>
                    </button>

                    <div
                        class="absolute right-0 z-30 mt-2 hidden min-w-44 overflow-hidden rounded-xl border border-slate-700 bg-slate-900 shadow-xl"
                        data-user-dropdown-menu
                    >
                        <a href="{{ route('profile.edit') }}" class="block px-3 py-2.5 text-sm text-slate-200 transition hover:bg-slate-800">
                            Modifica profilo
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-3 py-2.5 text-left text-sm text-rose-200 transition hover:bg-slate-800">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    <header>
        <div class="relative bg-[#050b16]">
            <div class="mx-auto flex w-full max-w-screen-xl items-center justify-between px-4 py-4 md:px-8">
                <div class="z-40">
                    <a href="{{ route('pages.home') }}" class="inline-flex items-center" aria-label="{{ config('app.name') }} home">
                        @if (filled($logoUrl))
                            <img loading="lazy" class="h-8 w-auto lg:h-12" src="{{ $logoUrl }}" alt="{{ $logoAlt }}">
                        @else
                            <span class="text-lg font-extrabold uppercase tracking-[0.2em] text-white">{{ config('app.name') }}</span>
                        @endif
                    </a>
                </div>

                <nav id="desktop-menu" class="hidden items-center uppercase lg:flex" aria-label="Menu principale">
                    @include('layouts.partials.visitor-desktop-menu', [
                        'items' => $menuItems,
                        'level' => 0,
                    ])

                    @if ($showSearch)
                        <button type="button" class="ml-4 inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/20 text-white transition hover:bg-white/10" data-open-search>
                            <span class="sr-only">Apri ricerca</span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="9" r="6"></circle>
                                <path stroke-linecap="round" d="m14 14 4 4"></path>
                            </svg>
                        </button>
                    @endif
                </nav>

                <button id="mobile-menu-toggle" class="z-40 inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/20 text-white lg:hidden" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Apri menu</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <nav id="mobile-menu" class="fixed inset-0 z-30 translate-x-full overflow-y-auto bg-[#050b16] px-8 py-10 transition-transform duration-300 lg:hidden" aria-label="Menu mobile">
                <div class="mx-auto flex min-h-full w-full max-w-screen-sm flex-col justify-center">
                    @if ($showSearch)
                        <button type="button" class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/20 text-white" data-open-search>
                            <span class="sr-only">Apri ricerca</span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="9" r="6"></circle>
                                <path stroke-linecap="round" d="m14 14 4 4"></path>
                            </svg>
                        </button>
                    @endif

                    <div class="text-white">
                        @include('layouts.partials.visitor-mobile-menu', [
                            'items' => $menuItems,
                            'isChild' => false,
                        ])
                    </div>
                </div>
            </nav>
        </div>

        @if ($showSearch)
            <div id="search-panel" class="fixed inset-0 z-40 flex translate-y-full flex-col justify-center bg-[#050b16]/95 px-4 transition-transform duration-300" aria-hidden="true">
                <div class="mx-auto w-full max-w-2xl">
                    <div class="mb-5 flex justify-end">
                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/25 text-white" data-close-search>
                            <span class="sr-only">Chiudi ricerca</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15"></path>
                            </svg>
                        </button>
                    </div>

                    <form role="search" method="get" class="search-form" action="{{ route('articles.index') }}">
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-300">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="9" cy="9" r="6"></circle>
                                    <path stroke-linecap="round" d="m14 14 4 4"></path>
                                </svg>
                            </span>
                            <input
                                id="search-bar"
                                type="search"
                                name="q"
                                placeholder="Cerca nel blog"
                                class="w-full border-b border-white/40 bg-transparent py-3 pl-12 pr-4 text-lg text-white outline-none placeholder:text-slate-400"
                                value="{{ request('q', '') }}"
                            >
                        </div>
                    </form>

                    <div id="search-results" class="mt-4 rounded-xl bg-white/5"></div>
                </div>
            </div>
        @endif
    </header>

    <main class="shell space-y-4 pb-16 pt-8">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <p class="font-semibold">Controlla i campi:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        (() => {
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileToggle = document.getElementById('mobile-menu-toggle');
            const searchPanel = document.getElementById('search-panel');
            const searchOpenButtons = [...document.querySelectorAll('[data-open-search]')];
            const searchCloseButtons = [...document.querySelectorAll('[data-close-search]')];
            const userDropdown = document.querySelector('[data-user-dropdown]');
            const userDropdownToggle = userDropdown?.querySelector('[data-user-dropdown-toggle]');
            const userDropdownMenu = userDropdown?.querySelector('[data-user-dropdown-menu]');

            const closeMobileMenu = () => {
                if (!mobileMenu || !mobileToggle) {
                    return;
                }

                mobileMenu.classList.add('translate-x-full');
                mobileToggle.setAttribute('aria-expanded', 'false');
            };

            const openMobileMenu = () => {
                if (!mobileMenu || !mobileToggle) {
                    return;
                }

                mobileMenu.classList.remove('translate-x-full');
                mobileToggle.setAttribute('aria-expanded', 'true');
            };

            if (mobileMenu && mobileToggle) {
                mobileToggle.addEventListener('click', () => {
                    const isOpen = mobileToggle.getAttribute('aria-expanded') === 'true';

                    if (isOpen) {
                        closeMobileMenu();
                        return;
                    }

                    openMobileMenu();
                });
            }

            const openSearch = () => {
                if (!searchPanel) {
                    return;
                }

                searchPanel.classList.remove('translate-y-full');
                searchPanel.setAttribute('aria-hidden', 'false');
            };

            const closeSearch = () => {
                if (!searchPanel) {
                    return;
                }

                searchPanel.classList.add('translate-y-full');
                searchPanel.setAttribute('aria-hidden', 'true');
            };

            searchOpenButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    closeMobileMenu();
                    openSearch();
                });
            });

            searchCloseButtons.forEach((button) => {
                button.addEventListener('click', closeSearch);
            });

            const closeUserDropdown = () => {
                if (!userDropdownToggle || !userDropdownMenu) {
                    return;
                }

                userDropdownMenu.classList.add('hidden');
                userDropdownToggle.setAttribute('aria-expanded', 'false');
            };

            const openUserDropdown = () => {
                if (!userDropdownToggle || !userDropdownMenu) {
                    return;
                }

                userDropdownMenu.classList.remove('hidden');
                userDropdownToggle.setAttribute('aria-expanded', 'true');
            };

            if (userDropdownToggle && userDropdownMenu) {
                userDropdownToggle.addEventListener('click', () => {
                    const expanded = userDropdownToggle.getAttribute('aria-expanded') === 'true';

                    if (expanded) {
                        closeUserDropdown();
                        return;
                    }

                    openUserDropdown();
                });

                document.addEventListener('click', (event) => {
                    if (!userDropdown.contains(event.target)) {
                        closeUserDropdown();
                    }
                });
            }

            if (searchPanel) {
                searchPanel.addEventListener('click', (event) => {
                    if (event.target === searchPanel) {
                        closeSearch();
                    }
                });
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeMobileMenu();
                    closeSearch();
                    closeUserDropdown();
                }
            });

            document.querySelectorAll('[data-mobile-submenu-toggle]').forEach((button) => {
                const wrapper = button.closest('div');
                const panel = wrapper?.nextElementSibling;
                const icon = button.querySelector('[data-mobile-submenu-icon]');

                if (!panel || !icon || !panel.hasAttribute('data-mobile-submenu-panel')) {
                    return;
                }

                button.addEventListener('click', () => {
                    const expanded = button.getAttribute('aria-expanded') === 'true';

                    if (expanded) {
                        panel.style.maxHeight = '0px';
                        panel.classList.remove('pb-2');
                        button.setAttribute('aria-expanded', 'false');
                        icon.classList.remove('rotate-180');
                        return;
                    }

                    panel.style.maxHeight = `${panel.scrollHeight}px`;
                    panel.classList.add('pb-2');
                    button.setAttribute('aria-expanded', 'true');
                    icon.classList.add('rotate-180');
                });
            });
        })();
    </script>
</body>
</html>
