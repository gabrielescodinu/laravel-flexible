@php
    $cards = $data['cards'] ?? [];
    if (! is_array($cards)) {
        $cards = [];
    }
@endphp

<section class="content-block border-0 bg-[#d8d8db] p-6 md:p-10">
    <header class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
            {{ $data['section_subtitle'] ?? 'Featured' }}
        </p>
        <h3 class="mt-2 text-3xl font-semibold text-slate-900">
            {{ $data['section_title'] ?? 'Top Destinations' }}
        </h3>
    </header>

    <div class="grid gap-8 lg:grid-cols-2">
        @foreach ($cards as $index => $card)
            @php
                if (! is_array($card)) {
                    $card = [];
                }

                $showImageFirst = $index % 2 === 1;
                $imageUrl = $card['image_url'] ?? '';
                $tagOne = $card['tag_1'] ?? '';
                $tagTwo = $card['tag_2'] ?? '';
                $rating = $card['rating'] ?? '';
            @endphp

            <article class="overflow-hidden rounded-[28px] bg-white shadow-[0_16px_40px_rgba(15,23,42,0.12)]">
                @if ($showImageFirst)
                    <div class="relative m-3 mb-0 h-72 overflow-hidden rounded-[22px]">
                        @if ($imageUrl !== '')
                            <img src="{{ $imageUrl }}" alt="{{ $card['title'] ?? 'Destination image' }}" class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full bg-slate-300"></div>
                        @endif

                        <div class="absolute inset-x-3 top-3 flex items-center justify-between text-xs text-white">
                            <div class="flex items-center gap-2">
                                @if ($tagOne !== '')
                                    <span class="rounded-full bg-white/25 px-3 py-1 backdrop-blur">{{ $tagOne }}</span>
                                @endif
                                @if ($tagTwo !== '')
                                    <span class="rounded-full bg-white/25 px-3 py-1 backdrop-blur">{{ $tagTwo }}</span>
                                @endif
                            </div>
                            @if ($rating !== '')
                                <span class="inline-flex items-center gap-1 rounded-full bg-black/20 px-2 py-1 backdrop-blur">
                                    ★ {{ $rating }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h4 class="text-4xl font-medium tracking-tight text-slate-900 md:text-[2.05rem]">
                            {{ $card['title'] ?? '' }}
                        </h4>
                        @if (! empty($card['badge']))
                            <span class="rounded-full border border-slate-300 px-3 py-1 text-sm text-slate-700">
                                {{ $card['badge'] }}
                            </span>
                        @endif
                    </div>

                    @if (! empty($card['meta']))
                        <p class="mt-2 text-sm text-slate-500">{{ $card['meta'] }}</p>
                    @endif

                    @if (! empty($card['description']))
                        <p class="mt-4 max-w-sm text-base text-slate-500">{{ $card['description'] }}</p>
                    @endif

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-3xl font-semibold text-slate-900 md:text-4xl">{{ $card['price'] ?? '' }}</p>
                        @if (! empty($card['cta_text']) && ! empty($card['cta_url']))
                            <a href="{{ $card['cta_url'] }}" class="inline-flex items-center gap-2 rounded-full bg-black px-4 py-2 text-base font-medium text-white">
                                {{ $card['cta_text'] }}
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-black">↗</span>
                            </a>
                        @endif
                    </div>
                </div>

                @if (! $showImageFirst)
                    <div class="relative h-72 overflow-hidden rounded-b-[22px]">
                        @if ($imageUrl !== '')
                            <img src="{{ $imageUrl }}" alt="{{ $card['title'] ?? 'Destination image' }}" class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full bg-slate-300"></div>
                        @endif

                        <div class="absolute inset-x-5 bottom-4 flex items-center justify-between text-sm text-white">
                            <div class="flex items-center gap-2">
                                @if ($tagOne !== '')
                                    <span class="rounded-full bg-white/25 px-3 py-1 backdrop-blur">{{ $tagOne }}</span>
                                @endif
                                @if ($tagTwo !== '')
                                    <span class="rounded-full bg-white/25 px-3 py-1 backdrop-blur">{{ $tagTwo }}</span>
                                @endif
                            </div>
                            @if ($rating !== '')
                                <span class="inline-flex items-center gap-1 rounded-full bg-black/20 px-2 py-1 backdrop-blur">
                                    ★ {{ $rating }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </article>
        @endforeach
    </div>
</section>
