@foreach ($items as $item)
    @php
        $hasChildren = ! empty($item['children']);
        $itemUrl = (string) ($item['url'] ?? '#');
    @endphp

    <div class="border-b border-white/15 {{ ! empty($isChild) ? 'ml-4' : '' }}">
        @if ($hasChildren)
            <div class="flex items-center justify-between gap-2 py-4">
                <a
                    href="{{ $itemUrl }}"
                    @if (! empty($item['open_in_new_tab'])) target="_blank" rel="noopener" @endif
                    class="text-lg font-medium text-white"
                >
                    {{ $item['label'] }}
                </a>

                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/20 text-slate-200"
                    data-mobile-submenu-toggle
                    aria-expanded="false"
                    aria-label="Apri sottomenu"
                >
                    <svg class="h-4 w-4 transition-transform duration-300" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" data-mobile-submenu-icon>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4" />
                    </svg>
                </button>
            </div>

            <div class="mobile-submenu overflow-hidden max-h-0 pb-0 transition-all duration-300" data-mobile-submenu-panel>
                @include('layouts.partials.visitor-mobile-menu', [
                    'items' => $item['children'],
                    'isChild' => true,
                ])
            </div>
        @else
            <a
                href="{{ $itemUrl }}"
                @if (! empty($item['open_in_new_tab'])) target="_blank" rel="noopener" @endif
                class="block py-4 text-lg font-medium text-white"
            >
                {{ $item['label'] }}
            </a>
        @endif
    </div>
@endforeach
