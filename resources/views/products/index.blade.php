@extends('layouts.app')

@section('title', 'Prodotti')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Shop</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">Catalogo prodotti</h1>
                <p class="muted mt-2">Scopri tutti i prodotti disponibili.</p>
            </div>

            @if (! $ecommerceEnabled)
                <span class="status-badge status-draft">Ecommerce non attivo</span>
            @endif
        </div>
    </section>

    <section class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($products as $product)
            <article class="panel p-0 overflow-hidden">
                @if ($product->featured_image)
                    <a href="{{ $product->public_url }}" class="block h-48 bg-slate-100">
                        <img src="{{ $product->featured_image }}" alt="{{ $product->title }}" class="h-full w-full object-cover">
                    </a>
                @endif

                <div class="space-y-3 p-5">
                    <h2 class="text-xl font-semibold text-slate-900">
                        <a href="{{ $product->public_url }}" class="hover:text-slate-600">{{ $product->title }}</a>
                    </h2>

                    <p class="muted line-clamp-3">{{ \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 120) }}</p>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xl font-bold text-slate-900">€ {{ number_format((float) $product->price, 2, ',', '.') }}</span>
                        <a href="{{ $product->public_url }}" class="btn btn-secondary">Dettagli</a>
                    </div>

                    @if ($ecommerceEnabled)
                        <form method="POST" action="{{ route('cart.add', ['product' => $product->id]) }}">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button class="btn btn-primary w-full" type="submit">Aggiungi al carrello</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="panel md:col-span-2 xl:col-span-3">
                <p class="muted">Nessun prodotto pubblicato.</p>
            </div>
        @endforelse
    </section>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
