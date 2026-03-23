@extends('layouts.app')

@section('title', 'Carrello')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Ecommerce</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">Carrello</h1>
                <p class="muted mt-2">Controlla i prodotti selezionati prima del checkout.</p>
            </div>

            @if ($items !== [])
                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-secondary" type="submit">Svuota carrello</button>
                </form>
            @endif
        </div>
    </section>

    <section class="panel mt-4">
        @if ($items === [])
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <p class="text-sm text-slate-500">Il carrello e vuoto.</p>
                <a class="btn btn-primary mt-4" href="{{ route('products.index') }}">Vai ai prodotti</a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($items as $item)
                    @php($product = $item['product'])
                    <article class="rounded-xl border border-slate-200 bg-white p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $product->title }}</p>
                                <p class="text-sm text-slate-500">€ {{ number_format((float) $product->price, 2, ',', '.') }} cad.</p>
                            </div>

                            <form method="POST" action="{{ route('cart.update', ['product' => $product->id]) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input class="field-input max-w-24" type="number" name="quantity" min="1" max="99" value="{{ $item['quantity'] }}">
                                <button class="btn btn-secondary" type="submit">Aggiorna</button>
                            </form>

                            <p class="font-semibold text-slate-900">€ {{ number_format((float) $item['line_total'], 2, ',', '.') }}</p>

                            <form method="POST" action="{{ route('cart.remove', ['product' => $product->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Rimuovi</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 pt-4">
                <p class="text-lg font-semibold text-slate-900">Totale: € {{ number_format((float) $total, 2, ',', '.') }}</p>
                <button class="btn btn-primary" type="button" disabled>Checkout (demo)</button>
            </div>
        @endif
    </section>
@endsection
