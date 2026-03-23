@extends('layouts.app')

@section('title', $product->title)

@section('content')
    <article class="space-y-4">
        <section class="panel overflow-hidden">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="overflow-hidden rounded-2xl bg-slate-100">
                    @if ($product->featured_image)
                        <img src="{{ $product->featured_image }}" alt="{{ $product->title }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex min-h-80 items-center justify-center text-slate-400">Nessuna immagine</div>
                    @endif
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Prodotto</p>
                        <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ $product->title }}</h1>
                    </div>

                    <p class="text-2xl font-extrabold text-slate-900">€ {{ number_format((float) $product->price, 2, ',', '.') }}</p>

                    <div class="prose prose-slate max-w-none">
                        {!! nl2br(e((string) $product->description)) !!}
                    </div>

                    @if ($ecommerceEnabled)
                        <form method="POST" action="{{ route('cart.add', ['product' => $product->id]) }}" class="flex flex-wrap items-center gap-2">
                            @csrf
                            <input
                                class="field-input max-w-28"
                                type="number"
                                name="quantity"
                                value="1"
                                min="1"
                                max="99"
                            >
                            <button class="btn btn-primary" type="submit">Aggiungi al carrello</button>
                            <a class="btn btn-secondary" href="{{ route('cart.index') }}">Vai al carrello</a>
                        </form>
                    @else
                        <p class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                            Ecommerce disattivato: acquisto momentaneamente non disponibile.
                        </p>
                    @endif
                </div>
            </div>
        </section>

        @if ($relatedProducts->isNotEmpty())
            <section class="panel">
                <h2 class="text-xl font-semibold text-slate-900">Prodotti correlati</h2>
                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($relatedProducts as $relatedProduct)
                        <a href="{{ $relatedProduct->public_url }}" class="rounded-xl border border-slate-200 bg-white p-3 transition hover:border-slate-300">
                            <p class="text-sm font-semibold text-slate-900">{{ $relatedProduct->title }}</p>
                            <p class="mt-1 text-sm text-slate-500">€ {{ number_format((float) $relatedProduct->price, 2, ',', '.') }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection
