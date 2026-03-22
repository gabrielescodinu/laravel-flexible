@php
    $level = $level ?? 0;
    $isRoot = $level === 0;
    $currentPath = trim(request()->path(), '/');
@endphp

@if (! empty($items))
    <ul class="{{ $isRoot ? 'menu-principale flex items-center gap-1' : 'sub-menu absolute left-0 top-full z-20 mt-3 hidden min-w-56 space-y-1 rounded-2xl border border-white/15 bg-[#0b1220] p-2 text-left shadow-2xl group-hover:block group-focus-within:block' }}">
        @foreach ($items as $item)
            @php
                $itemUrl = (string) ($item['url'] ?? '#');
                $host = parse_url($itemUrl, PHP_URL_HOST);
                $path = trim((string) parse_url($itemUrl, PHP_URL_PATH), '/');
                $hasChildren = ! empty($item['children']);
                $isActive = $host === null && (
                    ($path === '' && $currentPath === '')
                    || ($path !== '' && ($currentPath === $path || str_starts_with($currentPath, $path.'/')))
                );
            @endphp

            <li class="{{ $isRoot ? 'group relative' : 'group relative' }}">
                <a
                    href="{{ $itemUrl }}"
                    @if (! empty($item['open_in_new_tab'])) target="_blank" rel="noopener" @endif
                    class="{{ $isRoot
                        ? 'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold uppercase tracking-[0.08em] transition '.($isActive ? 'bg-white/20 text-white' : 'text-slate-200 hover:bg-white/10 hover:text-white')
                        : 'block rounded-xl px-3 py-2 text-sm text-slate-200 transition hover:bg-white/10 hover:text-white'
                    }}"
                >
                    <span>{{ $item['label'] }}</span>
                    @if ($hasChildren && $isRoot)
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4" />
                        </svg>
                    @endif
                </a>

                @if ($hasChildren)
                    @include('layouts.partials.visitor-desktop-menu', [
                        'items' => $item['children'],
                        'level' => $level + 1,
                    ])
                @endif
            </li>
        @endforeach
    </ul>
@endif
