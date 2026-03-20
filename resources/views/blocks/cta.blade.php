<section class="content-block border-slate-900 bg-slate-900 text-white">
    <h3 class="text-2xl font-semibold">{{ $data['title'] ?? '' }}</h3>

    @if (!empty($data['text']))
        <p class="mt-3 max-w-2xl text-slate-300">{{ $data['text'] }}</p>
    @endif

    @if (!empty($data['button_label']) && !empty($data['button_url']))
        <a class="btn btn-secondary mt-6 bg-white text-slate-900 hover:bg-slate-100" href="{{ $data['button_url'] }}">{{ $data['button_label'] }}</a>
    @endif
</section>
