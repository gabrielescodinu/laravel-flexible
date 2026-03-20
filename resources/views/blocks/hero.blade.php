@php
    $layoutDirection = $data['layout_direction'] ?? 'lg:flex-row';
    $allowedDirections = ['lg:flex-row', 'lg:flex-row-reverse'];
    if (! in_array($layoutDirection, $allowedDirections, true)) {
        $layoutDirection = 'lg:flex-row';
    }

    $title = $data['title'] ?? '';
    $description = $data['description'] ?? ($data['subtitle'] ?? '');

    $buttonPrimaryText = $data['button_primary_text'] ?? ($data['cta_text'] ?? '');
    $buttonPrimaryUrl = $data['button_primary_url'] ?? ($data['cta_url'] ?? '');
    $buttonSecondaryText = $data['button_secondary_text'] ?? '';
    $buttonSecondaryUrl = $data['button_secondary_url'] ?? '';

    $imageUrl = $data['image_url'] ?? '';
    $imageAlt = $data['image_alt'] ?? 'Hero image';
@endphp

<section class="bg-ui-surface py-16 text-primary lg:py-24">
    <div class="mx-auto max-w-screen-xl space-y-12 px-4 py-8 lg:flex lg:gap-16 lg:py-0 {{ $layoutDirection }}">
        <div class="flex flex-1 flex-col justify-center">
            @if ($title !== '')
                <h1 class="mb-4 max-w-2xl text-4xl font-bold leading-none tracking-tight sm:text-5xl">
                    {{ $title }}
                </h1>
            @endif

            @if ($description !== '')
                <div class="editor-styles-wrapper mb-8 max-w-2xl text-lg text-ui-body">
                    {!! nl2br(e($description)) !!}
                </div>
            @endif

            <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap">
                @if ($buttonPrimaryText !== '' && $buttonPrimaryUrl !== '')
                    <a href="{{ $buttonPrimaryUrl }}" class="btn btn-primary">
                        {{ $buttonPrimaryText }}
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                @endif

                @if ($buttonSecondaryText !== '' && $buttonSecondaryUrl !== '')
                    <a href="{{ $buttonSecondaryUrl }}" class="btn btn-secondary">
                        {{ $buttonSecondaryText }}
                    </a>
                @endif
            </div>
        </div>

        @if ($imageUrl !== '')
            <div class="flex flex-1 items-center justify-center">
                <img loading="lazy" src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="h-auto w-full rounded-lg shadow-xl">
            </div>
        @endif
    </div>
</section>
