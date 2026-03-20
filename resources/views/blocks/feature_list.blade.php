<section class="content-block">
    <h3 class="text-2xl font-semibold text-slate-900">{{ $data['title'] ?? 'Features' }}</h3>

    @php
        $items = $data['items'] ?? [];
        if (is_string($items)) {
            $items = preg_split('/\r\n|\r|\n/', $items) ?: [];
        }
    @endphp

    @if (!empty($items))
        <ul class="mt-6 grid gap-3 md:grid-cols-2">
            @foreach ($items as $item)
                <li class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    {{ $item }}
                </li>
            @endforeach
        </ul>
    @endif
</section>
