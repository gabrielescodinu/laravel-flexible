@php
    $statsHeading = trim((string) ($data['stats_heading'] ?? ''));
    $statsDescription = trim((string) ($data['stats_description'] ?? ''));

    $statsItemsRaw = $data['stats_items'] ?? [];
    $statsItems = [];

    if (is_array($statsItemsRaw)) {
        foreach ($statsItemsRaw as $item) {
            if (! is_array($item)) {
                continue;
            }

            $statData = trim((string) ($item['stat_data'] ?? ''));
            $statTitle = trim((string) ($item['stat_title'] ?? ''));

            if ($statData === '' && $statTitle === '') {
                continue;
            }

            $statsItems[] = [
                'stat_data' => $statData,
                'stat_title' => $statTitle,
            ];
        }
    }
@endphp

<section class="bg-ui-surface bg-slate-50 py-20">
    <div class="mx-auto max-w-screen-xl px-4 md:px-8">
        <div class="mx-auto max-w-3xl text-center">
            @if ($statsHeading !== '')
                <h3 class="text-primary text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    {{ $statsHeading }}
                </h3>
            @endif

            @if ($statsDescription !== '')
                <div class="editor-styles-wrapper text-ui-body mt-4 text-xl text-slate-600">
                    {!! nl2br(e($statsDescription)) !!}
                </div>
            @endif
        </div>

        @if ($statsItems !== [])
            <div class="mt-16">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ($statsItems as $item)
                        <article class="bg-ui-soft rounded-lg bg-white p-8 text-center shadow-sm ring-1 ring-slate-200/70">
                            @if ($item['stat_data'] !== '')
                                <h4 class="text-primary text-5xl font-bold tracking-tight text-slate-900">
                                    {{ $item['stat_data'] }}
                                </h4>
                            @endif

                            @if ($item['stat_title'] !== '')
                                <p class="text-ui-body mt-2 text-lg text-slate-600">
                                    {{ $item['stat_title'] }}
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
