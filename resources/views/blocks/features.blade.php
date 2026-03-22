@php
    $featuresHeadingTop = trim((string) ($data['features_heading_top'] ?? ''));
    $featuresHeadingMain = trim((string) ($data['features_heading_main'] ?? ''));
    $featuresDescription = trim((string) ($data['features_description'] ?? ''));

    $featuresRaw = $data['features'] ?? [];
    $features = [];

    if (is_array($featuresRaw)) {
        foreach ($featuresRaw as $featureItem) {
            if (! is_array($featureItem)) {
                continue;
            }

            $featureIcon = trim((string) ($featureItem['feature_icon'] ?? ''));
            $featureIconAlt = trim((string) ($featureItem['feature_icon_alt'] ?? ''));
            $featureTitle = trim((string) ($featureItem['feature_title'] ?? ''));
            $featureDesc = trim((string) ($featureItem['feature_desc'] ?? ''));

            if ($featureIcon === '' && $featureTitle === '' && $featureDesc === '') {
                continue;
            }

            $features[] = [
                'feature_icon' => $featureIcon,
                'feature_icon_alt' => $featureIconAlt !== '' ? $featureIconAlt : 'Feature icon',
                'feature_title' => $featureTitle,
                'feature_desc' => $featureDesc,
            ];
        }
    }
@endphp

<section class="bg-ui-soft bg-slate-100 py-20">
    <div class="mx-auto max-w-screen-xl px-4 md:px-8">
        <div class="mx-auto max-w-3xl space-y-3 text-center">
            @if ($featuresHeadingTop !== '')
                <h3 class="text-ui-ink text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">
                    {{ $featuresHeadingTop }}
                </h3>
            @endif

            @if ($featuresHeadingMain !== '')
                <h2 class="text-primary text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    {{ $featuresHeadingMain }}
                </h2>
            @endif

            @if ($featuresDescription !== '')
                <div class="editor-styles-wrapper text-secondary text-xl text-slate-600">
                    {!! nl2br(e($featuresDescription)) !!}
                </div>
            @endif
        </div>

        @if ($features !== [])
            <div class="mt-16">
                <ul class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($features as $item)
                        <li class="bg-ui-surface space-y-4 rounded-lg border border-ui-border border-slate-200 bg-white p-6 shadow-sm">
                            @if ($item['feature_icon'] !== '')
                                <div class="bg-ui-ink text-ui-contrast flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-lg bg-slate-900">
                                    <img
                                        loading="lazy"
                                        src="{{ $item['feature_icon'] }}"
                                        alt="{{ $item['feature_icon_alt'] }}"
                                        class="h-8 w-8 object-contain"
                                    >
                                </div>
                            @endif

                            <div class="space-y-2">
                                @if ($item['feature_title'] !== '')
                                    <h4 class="text-primary text-lg font-semibold text-slate-900">
                                        {{ $item['feature_title'] }}
                                    </h4>
                                @endif

                                @if ($item['feature_desc'] !== '')
                                    <div class="editor-styles-wrapper-small text-ui-ink text-slate-600">
                                        {!! nl2br(e($item['feature_desc'])) !!}
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</section>
