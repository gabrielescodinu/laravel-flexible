<section class="content-block relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-cyan-900 text-white">
    <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>

    @if (!empty($data['eyebrow']))
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-200">{{ $data['eyebrow'] }}</p>
    @endif

    <h2 class="mt-3 text-3xl font-bold leading-tight sm:text-4xl">{{ $data['title'] ?? '' }}</h2>

    @if (!empty($data['subtitle']))
        <p class="mt-4 max-w-2xl text-base text-slate-200 sm:text-lg">{{ $data['subtitle'] }}</p>
    @endif

    @if (!empty($data['cta_text']) && !empty($data['cta_url']))
        <a class="btn btn-secondary mt-7 bg-white text-slate-900 hover:bg-slate-100" href="{{ $data['cta_url'] }}">{{ $data['cta_text'] }}</a>
    @endif
</section>
