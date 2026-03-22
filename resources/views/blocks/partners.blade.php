@php
    $partnersTitle = trim((string) ($data['partners_title'] ?? ''));
    $partnersContent = trim((string) ($data['partners_content'] ?? ''));

    $logosRaw = $data['logos'] ?? [];
    $logos = [];

    if (is_array($logosRaw)) {
        foreach ($logosRaw as $logo) {
            if (! is_array($logo)) {
                continue;
            }

            $logoLink = trim((string) ($logo['link'] ?? ''));
            $logoUrl = trim((string) ($logo['image'] ?? ''));
            $logoAlt = trim((string) ($logo['image_alt'] ?? ''));

            if ($logoLink === '' || $logoUrl === '') {
                continue;
            }

            $logos[] = [
                'link' => $logoLink,
                'url' => $logoUrl,
                'alt' => $logoAlt !== '' ? $logoAlt : 'Partner logo',
            ];
        }
    }
@endphp

<section class="bg-ui-surface p-8 py-40 lg:px-40 4xl:px-96">
    <div>
        @if ($partnersTitle !== '')
            <h3 class="text-template-blue text-4xl font-bold uppercase text-slate-900">
                {{ $partnersTitle }}
            </h3>
        @endif

        @if ($partnersContent !== '')
            <div class="editor-styles-wrapper mt-16 text-lg text-slate-600 [&_a]:underline [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6">
                {!! $partnersContent !!}
            </div>
        @endif

        @if ($logos !== [])
            <div class="mt-24 grid grid-cols-1 items-center gap-16 lg:grid-cols-3">
                @foreach ($logos as $logo)
                    <a href="{{ $logo['link'] }}">
                        <img
                            loading="lazy"
                            class="mx-auto h-16 w-auto object-contain"
                            src="{{ $logo['url'] }}"
                            alt="{{ $logo['alt'] }}"
                        >
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
